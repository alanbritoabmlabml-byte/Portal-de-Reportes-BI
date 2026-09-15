<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccionBitacora;
use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Sesion;
use App\Models\User;
use App\Models\Visita;
use App\Support\LibroExcel;
use App\Support\Rastro;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Panel de trazabilidad: qué pasó en el portal, quién lo hizo y cuánto duró.
 *
 * Las agrupaciones se hacen en PHP sobre colecciones y no con funciones de
 * fecha de SQL: así el mismo código sirve en SQLite (local y demo) y en MySQL
 * (producción), y el volumen de un portal interno lo permite de sobra.
 */
class TrazabilidadController extends Controller
{
    public function index(Request $request): View
    {
        // Antes de contar, se cierran las sesiones que quedaron abiertas
        Rastro::vencerInactivas();

        $filtros = $this->filtros($request);

        $sesiones = $this->consultaSesiones($filtros)
            ->with('usuario')->latest('iniciada_at')->paginate(12, ['*'], 'sesiones')->withQueryString();

        $asientos = $this->consultaBitacora($filtros)
            ->with('usuario')->latest('created_at')->paginate(15, ['*'], 'bitacora')->withQueryString();

        return view('admin.trazabilidad.index', [
            'filtros' => $filtros,
            'sesiones' => $sesiones,
            'asientos' => $asientos,
            'metricas' => $this->metricas($filtros),
            'usuarios' => User::query()->orderBy('name')->get(['id', 'name']),
            'acciones' => AccionBitacora::opciones(),
        ]);
    }

    /** Recorrido completo de una sesión: página por página y acción por acción. */
    public function sesion(Sesion $sesion): View
    {
        $sesion->load(['usuario', 'visitas', 'asientos' => fn ($q) => $q->latest('created_at')]);

        return view('admin.trazabilidad.sesion', ['sesion' => $sesion]);
    }

    /** Descarga de la bitácora: .xlsx nativo o vista imprimible para PDF. */
    public function exportar(Request $request, string $formato): Response|View
    {
        $filtros = $this->filtros($request);

        $sesiones = $this->consultaSesiones($filtros)->with('usuario')->latest('iniciada_at')->get();
        $visitas = $this->consultaVisitas($filtros)->with('usuario')->latest('entrada_at')->limit(5000)->get();
        $asientos = $this->consultaBitacora($filtros)->with('usuario')->latest('created_at')->limit(5000)->get();

        Rastro::registrar(
            AccionBitacora::Exportar,
            'Exportó la bitácora de trazabilidad en '.mb_strtoupper($formato),
            null,
            null,
            ['desde' => $filtros['desde']->toDateString(), 'hasta' => $filtros['hasta']->toDateString()],
        );

        if ($formato === 'pdf') {
            return view('admin.trazabilidad.imprimible', [
                'filtros' => $filtros,
                'metricas' => $this->metricas($filtros),
                'sesiones' => $sesiones,
                'asientos' => $asientos,
            ]);
        }

        $libro = new LibroExcel;

        $libro->hoja('Sesiones',
            ['Usuario', 'Correo', 'Inicio', 'Fin', 'Duración', 'Duración (s)', 'Páginas', 'Acciones', 'IP', 'Navegador', 'Equipo', 'Estado'],
            $sesiones->map(fn (Sesion $s) => [
                $s->usuario?->name ?? '—',
                $s->usuario?->email ?? '—',
                $s->iniciada_at->format('d/m/Y H:i:s'),
                $s->cerrada_at?->format('d/m/Y H:i:s') ?? 'En curso',
                Sesion::formatear($s->duracion()),
                $s->duracion(),
                $s->visitas()->count(),
                $s->asientos()->count(),
                (string) $s->ip,
                (string) $s->navegador,
                (string) $s->plataforma,
                $s->abierta() ? 'Abierta' : ($s->motivo_cierre === 'salida' ? 'Cerró sesión' : 'Expirada'),
            ])->all(),
            [26, 32, 20, 20, 14, 13, 10, 10, 16, 12, 12, 14],
        );

        $libro->hoja('Páginas visitadas',
            ['Usuario', 'Página', 'Entrada', 'Salida', 'Permanencia', 'Permanencia (s)', 'URL'],
            $visitas->map(fn (Visita $v) => [
                $v->usuario?->name ?? '—',
                $v->titulo,
                $v->entrada_at->format('d/m/Y H:i:s'),
                $v->salida_at?->format('d/m/Y H:i:s') ?? '—',
                Sesion::formatear($v->segundos),
                $v->segundos,
                $v->url,
            ])->all(),
            [26, 38, 20, 20, 14, 16, 60],
        );

        $libro->hoja('Bitácora',
            ['Fecha', 'Usuario', 'Acción', 'Descripción', 'Entidad', 'ID', 'Pantalla', 'IP'],
            $asientos->map(fn (Bitacora $b) => [
                $b->created_at->format('d/m/Y H:i:s'),
                $b->usuario?->name ?? $b->usuario_nombre ?? '—',
                $b->accion->etiqueta(),
                $b->descripcion,
                (string) $b->entidad,
                $b->entidad_id ?? '',
                (string) $b->ruta,
                (string) $b->ip,
            ])->all(),
            [20, 26, 18, 60, 16, 8, 30, 16],
        );

        $nombre = 'trazabilidad-'.$filtros['desde']->format('Ymd').'-'.$filtros['hasta']->format('Ymd').'.xlsx';

        return response($libro->contenido(), 200, LibroExcel::cabeceras($nombre));
    }

    /**
     * @return array{desde:Carbon,hasta:Carbon,usuario:?int,accion:?string}
     */
    private function filtros(Request $request): array
    {
        $desde = $request->date('desde') ?? now()->subDays(29)->startOfDay();
        $hasta = $request->date('hasta') ?? now()->endOfDay();

        return [
            'desde' => Carbon::parse($desde)->startOfDay(),
            'hasta' => Carbon::parse($hasta)->endOfDay(),
            'usuario' => $request->integer('usuario') ?: null,
            'accion' => AccionBitacora::tryFrom((string) $request->query('accion'))?->value,
        ];
    }

    /**
     * @param  array{desde:Carbon,hasta:Carbon,usuario:?int,accion:?string}  $filtros
     * @return Builder<Sesion>
     */
    private function consultaSesiones(array $filtros): Builder
    {
        return Sesion::query()
            ->whereBetween('iniciada_at', [$filtros['desde'], $filtros['hasta']])
            ->when($filtros['usuario'], fn ($q, $id) => $q->where('user_id', $id));
    }

    /**
     * @param  array{desde:Carbon,hasta:Carbon,usuario:?int,accion:?string}  $filtros
     * @return Builder<Visita>
     */
    private function consultaVisitas(array $filtros): Builder
    {
        return Visita::query()
            ->whereBetween('entrada_at', [$filtros['desde'], $filtros['hasta']])
            ->when($filtros['usuario'], fn ($q, $id) => $q->where('user_id', $id));
    }

    /**
     * @param  array{desde:Carbon,hasta:Carbon,usuario:?int,accion:?string}  $filtros
     * @return Builder<Bitacora>
     */
    private function consultaBitacora(array $filtros): Builder
    {
        return Bitacora::query()
            ->whereBetween('created_at', [$filtros['desde'], $filtros['hasta']])
            ->when($filtros['usuario'], fn ($q, $id) => $q->where('user_id', $id))
            ->when($filtros['accion'], fn ($q, $a) => $q->where('accion', $a));
    }

    /**
     * Números y series del tablero.
     *
     * @param  array{desde:Carbon,hasta:Carbon,usuario:?int,accion:?string}  $filtros
     * @return array<string, mixed>
     */
    private function metricas(array $filtros): array
    {
        $sesiones = $this->consultaSesiones($filtros)->with('usuario')->get();
        $visitas = $this->consultaVisitas($filtros)->with('usuario')->get();
        $asientos = $this->consultaBitacora($filtros)->get();

        $segundosSesion = $sesiones->map(fn (Sesion $s) => $s->duracion());

        // Serie diaria: se rellenan los días sin actividad para que la barra
        // vacía se vea y no se deforme la escala del gráfico.
        $dias = collect();
        $cursor = $filtros['desde']->copy()->startOfDay();
        $tope = min($filtros['hasta']->copy()->startOfDay(), now()->startOfDay());
        $porDia = $sesiones->groupBy(fn (Sesion $s) => $s->iniciada_at->toDateString());

        while ($cursor->lte($tope) && $dias->count() < 62) {
            $clave = $cursor->toDateString();
            $delDia = $porDia->get($clave, collect());

            $dias->push([
                'fecha' => $cursor->copy(),
                'sesiones' => $delDia->count(),
                'usuarios' => $delDia->pluck('user_id')->unique()->count(),
                'minutos' => (int) round($delDia->sum(fn (Sesion $s) => $s->duracion()) / 60),
            ]);

            $cursor->addDay();
        }

        $porHora = collect(range(0, 23))->map(fn (int $hora) => [
            'hora' => $hora,
            'visitas' => $visitas->filter(fn (Visita $v) => (int) $v->entrada_at->format('G') === $hora)->count(),
        ]);

        return [
            'sesiones' => $sesiones->count(),
            'usuarios' => $sesiones->pluck('user_id')->unique()->count(),
            'paginas' => $visitas->count(),
            'acciones' => $asientos->count(),
            'segundos_total' => (int) $segundosSesion->sum(),
            'promedio_sesion' => (int) ($segundosSesion->avg() ?? 0),
            'sesion_larga' => (int) ($segundosSesion->max() ?? 0),
            'abiertas' => $sesiones->filter(fn (Sesion $s) => $s->abierta())->count(),
            'dias' => $dias,
            'horas' => $porHora,
            'paginas_top' => $this->agrupar($visitas, fn (Visita $v) => $v->titulo ?: 'Sin nombre'),
            'usuarios_top' => $this->agrupar($visitas, fn (Visita $v) => $v->usuario?->name ?? 'Cuenta eliminada'),
            'acciones_top' => $asientos->groupBy(fn (Bitacora $b) => $b->accion->value)
                ->map(fn (Collection $g, string $clave) => [
                    'etiqueta' => AccionBitacora::from($clave)->etiqueta(),
                    'tono' => AccionBitacora::from($clave)->tono(),
                    'total' => $g->count(),
                ])
                ->sortByDesc('total')->values(),
        ];
    }

    /**
     * Agrupa visitas por una clave y devuelve las diez primeras por tiempo.
     *
     * @param  Collection<int, Visita>  $visitas
     * @return Collection<int, array{etiqueta:string,visitas:int,segundos:int}>
     */
    private function agrupar(Collection $visitas, callable $clave): Collection
    {
        return $visitas->groupBy($clave)
            ->map(fn (Collection $grupo, string $etiqueta) => [
                'etiqueta' => $etiqueta,
                'visitas' => $grupo->count(),
                'segundos' => (int) $grupo->sum('segundos'),
            ])
            ->sortByDesc('segundos')
            ->take(10)
            ->values();
    }
}
