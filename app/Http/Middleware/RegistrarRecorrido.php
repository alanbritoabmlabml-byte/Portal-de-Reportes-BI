<?php

namespace App\Http\Middleware;

use App\Support\Rastro;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Anota el paso del usuario por cada pantalla. Solo páginas: se ignoran las
 * peticiones de fondo (latido, fetch de favoritos) y todo lo que no sea HTML.
 */
class RegistrarRecorrido
{
    /** Rutas que no cuentan como «visita a una página». */
    private const IGNORADAS = [
        'trazabilidad.latido',
        'trazabilidad.evento',
        'favoritos.alternar',
        'notificaciones.leer',
        'notificaciones.leerTodas',
        'tema.guardar',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        if (! $this->registrable($request, $respuesta)) {
            return $respuesta;
        }

        try {
            Rastro::visitar($request, $this->titulo($request));
        } catch (\Throwable) {
            // La bitácora nunca debe tumbar una página
        }

        return $respuesta;
    }

    private function registrable(Request $request, Response $respuesta): bool
    {
        if (! $request->isMethod('GET') || ! $request->user()) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson() || $request->isMethod('HEAD')) {
            return false;
        }

        if ($respuesta->getStatusCode() >= 300) {
            return false;
        }

        $ruta = $request->route()?->getName();

        return $ruta !== null && ! in_array($ruta, self::IGNORADAS, true);
    }

    /**
     * Nombre legible de la pantalla. Para las páginas con parámetro (un área
     * de BI) se añade el nombre del elemento para que la bitácora se lea sola.
     */
    private function titulo(Request $request): string
    {
        $ruta = $request->route()?->getName() ?? '';

        $nombres = [
            'menu' => 'Menú principal',
            'notificaciones.index' => 'Notificaciones',
            'admin.reportes.index' => 'Administración · Reportes BI',
            'admin.usuarios.index' => 'Administración · Usuarios',
            'admin.tarjetas.index' => 'Administración · Tarjetas del menú',
            'admin.estructura.index' => 'Administración · Departamentos y áreas',
            'admin.trazabilidad.index' => 'Administración · Trazabilidad',
            'admin.trazabilidad.sesion' => 'Administración · Detalle de sesión',
            'login' => 'Acceso',
        ];

        if ($ruta === 'bi.area') {
            $area = $request->route('area');

            return 'Tableros · '.(is_object($area) ? $area->nombre : (string) $area);
        }

        if ($ruta === 'demo.reporte') {
            $reporte = $request->route('reporte');

            return 'Tablero de muestra · '.(is_object($reporte) ? $reporte->titulo : (string) $reporte);
        }

        return $nombres[$ruta] ?? $ruta;
    }
}
