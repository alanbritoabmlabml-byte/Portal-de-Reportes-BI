<?php

namespace Database\Seeders;

use App\Enums\AccionBitacora;
use App\Models\Bitacora;
use App\Models\Sesion;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Actividad de muestra para que el panel de trazabilidad no se vea vacío en la
 * demo. No se ejecuta en producción: la traza real la escribe App\Support\Rastro.
 */
class TrazabilidadSeeder extends Seeder
{
    /** @var list<array{ruta:string,titulo:string,url:string,peso:int}> */
    private const PANTALLAS = [
        ['ruta' => 'menu', 'titulo' => 'Menú principal', 'url' => '/menu', 'peso' => 30],
        ['ruta' => 'bi.area', 'titulo' => 'Tableros · RRHH', 'url' => '/bi/rrhh', 'peso' => 16],
        ['ruta' => 'bi.area', 'titulo' => 'Tableros · Gerencia', 'url' => '/bi/gerencia', 'peso' => 12],
        ['ruta' => 'bi.area', 'titulo' => 'Tableros · Bolsas', 'url' => '/bi/bolsas', 'peso' => 12],
        ['ruta' => 'bi.area', 'titulo' => 'Tableros · Termoformado', 'url' => '/bi/termoformado', 'peso' => 9],
        ['ruta' => 'notificaciones.index', 'titulo' => 'Notificaciones', 'url' => '/notificaciones', 'peso' => 8],
        ['ruta' => 'admin.reportes.index', 'titulo' => 'Administrar reportes', 'url' => '/admin/reportes', 'peso' => 7],
        ['ruta' => 'admin.usuarios.index', 'titulo' => 'Administrar usuarios', 'url' => '/admin/usuarios', 'peso' => 6],
    ];

    public function run(): void
    {
        if (Sesion::query()->exists()) {
            return;
        }

        $usuarios = User::query()->where('activo', true)->get();

        if ($usuarios->isEmpty()) {
            return;
        }

        $bolsa = collect(self::PANTALLAS)
            ->flatMap(fn (array $p) => array_fill(0, $p['peso'], $p))
            ->values();

        for ($dia = 29; $dia >= 0; $dia--) {
            $fecha = now()->subDays($dia)->startOfDay();

            // Fin de semana: el portal casi no se usa
            $cuantas = $fecha->isWeekend() ? random_int(0, 2) : random_int(3, 9);

            for ($i = 0; $i < $cuantas; $i++) {
                $this->sesionDe($usuarios->random(), $fecha, $bolsa->all());
            }
        }

        // Un par de sesiones abiertas para que se vea el estado «en curso»
        foreach ($usuarios->random(min(2, $usuarios->count())) as $usuario) {
            $this->sesionDe($usuario, now(), $bolsa->all(), abierta: true);
        }
    }

    /**
     * @param  list<array{ruta:string,titulo:string,url:string,peso:int}>  $bolsa
     */
    private function sesionDe(User $usuario, Carbon $dia, array $bolsa, bool $abierta = false): void
    {
        $inicio = $abierta
            ? now()->subMinutes(random_int(3, 35))
            : $dia->copy()->setTime(random_int(7, 18), random_int(0, 59), random_int(0, 59));

        $paginas = random_int(2, 7);
        $cursor = $inicio->copy();

        $sesion = Sesion::query()->create([
            'user_id' => $usuario->id,
            'session_id' => (string) Str::uuid(),
            'ip' => '10.0.'.random_int(0, 4).'.'.random_int(2, 240),
            'navegador' => ['Chrome', 'Edge', 'Firefox', 'Safari'][random_int(0, 3)],
            'plataforma' => ['Windows', 'Windows', 'Android', 'macOS'][random_int(0, 3)],
            'agente' => 'Mozilla/5.0 (demo)',
            'iniciada_at' => $inicio,
            'ultima_at' => $inicio,
            'cerrada_at' => null,
            'motivo_cierre' => null,
            'segundos' => 0,
        ]);

        for ($p = 0; $p < $paginas; $p++) {
            $pantalla = $bolsa[array_rand($bolsa)];
            $duracion = random_int(25, 720);
            $entrada = $cursor->copy();
            $cursor = $cursor->copy()->addSeconds($duracion);

            Visita::query()->create([
                'sesion_id' => $sesion->id,
                'user_id' => $usuario->id,
                'ruta' => $pantalla['ruta'],
                'url' => config('app.url').$pantalla['url'],
                'titulo' => $pantalla['titulo'],
                'entrada_at' => $entrada,
                'salida_at' => $cursor->copy(),
                'segundos' => $duracion,
            ]);
        }

        Bitacora::query()->create([
            'sesion_id' => $sesion->id,
            'user_id' => $usuario->id,
            'usuario_nombre' => $usuario->name,
            'accion' => AccionBitacora::Entrar,
            'descripcion' => 'Inició sesión en el portal',
            'ruta' => 'login',
            'ip' => $sesion->ip,
            'created_at' => $inicio,
            'updated_at' => $inicio,
        ]);

        if ($usuario->esAdministrador() && random_int(1, 3) === 1) {
            $momento = $inicio->copy()->addSeconds(random_int(30, 300));

            Bitacora::query()->create([
                'sesion_id' => $sesion->id,
                'user_id' => $usuario->id,
                'usuario_nombre' => $usuario->name,
                'accion' => [AccionBitacora::Editar, AccionBitacora::Crear, AccionBitacora::Acceso][random_int(0, 2)],
                'entidad' => 'Reporte',
                'entidad_id' => random_int(1, 20),
                'descripcion' => 'Ajustó la configuración de un tablero de Power BI',
                'ruta' => 'admin.reportes.index',
                'ip' => $sesion->ip,
                'created_at' => $momento,
                'updated_at' => $momento,
            ]);
        }

        if ($abierta) {
            $sesion->forceFill([
                'ultima_at' => $cursor,
                'segundos' => (int) $inicio->diffInSeconds($cursor),
            ])->save();

            return;
        }

        $fin = $cursor->copy()->addSeconds(random_int(5, 60));

        $sesion->forceFill([
            'ultima_at' => $fin,
            'cerrada_at' => $fin,
            'motivo_cierre' => random_int(1, 4) === 1 ? 'inactividad' : 'salida',
            'segundos' => (int) $inicio->diffInSeconds($fin),
        ])->save();

        Bitacora::query()->create([
            'sesion_id' => $sesion->id,
            'user_id' => $usuario->id,
            'usuario_nombre' => $usuario->name,
            'accion' => AccionBitacora::Salir,
            'descripcion' => 'Cerró sesión',
            'ruta' => 'logout',
            'ip' => $sesion->ip,
            'created_at' => $fin,
            'updated_at' => $fin,
        ]);
    }
}
