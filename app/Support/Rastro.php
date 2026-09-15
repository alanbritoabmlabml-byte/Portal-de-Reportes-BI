<?php

namespace App\Support;

use App\Enums\AccionBitacora;
use App\Models\Bitacora;
use App\Models\Sesion;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Motor de trazabilidad. Todo lo que se registra pasa por aquí:
 * la sesión (con su duración), el paso por cada página (con su permanencia)
 * y los asientos de bitácora de lo que el usuario hace.
 *
 * Es deliberadamente tolerante a fallos: si algo sale mal registrando, la
 * aplicación sigue funcionando. Un portal no se cae por su bitácora.
 */
class Rastro
{
    /** Sesión resuelta en esta petición, para no ir dos veces a la base. */
    private static ?Sesion $sesion = null;

    private static bool $resuelta = false;

    /** Minutos sin actividad tras los cuales una sesión se considera vencida. */
    public const MINUTOS_INACTIVIDAD = 30;

    /** Abre (o recupera) la sesión de trazabilidad del usuario autenticado. */
    public static function sesion(?Request $peticion = null): ?Sesion
    {
        if (self::$resuelta) {
            return self::$sesion;
        }

        self::$resuelta = true;
        $usuario = Auth::user();

        if (! $usuario instanceof User) {
            return self::$sesion = null;
        }

        $peticion ??= request();
        $idSesion = $peticion->hasSession() ? $peticion->session()->getId() : null;

        $sesion = Sesion::query()
            ->where('user_id', $usuario->getKey())
            ->whereNull('cerrada_at')
            ->when($idSesion, fn ($q) => $q->where('session_id', $idSesion))
            ->latest('iniciada_at')
            ->first();

        if (! $sesion) {
            $agente = (string) $peticion->userAgent();

            $sesion = Sesion::query()->create([
                'user_id' => $usuario->getKey(),
                'session_id' => $idSesion,
                'ip' => $peticion->ip(),
                'navegador' => self::navegador($agente),
                'plataforma' => self::plataforma($agente),
                'agente' => mb_substr($agente, 0, 500),
                'iniciada_at' => now(),
                'ultima_at' => now(),
            ]);
        }

        return self::$sesion = $sesion;
    }

    /** Marca actividad: alarga la sesión y la visita abierta. */
    public static function latir(?Request $peticion = null): ?Sesion
    {
        $sesion = self::sesion($peticion);

        if (! $sesion) {
            return null;
        }

        $sesion->forceFill([
            'ultima_at' => now(),
            'segundos' => (int) $sesion->iniciada_at->diffInSeconds(now()),
        ])->save();

        $visita = self::visitaAbierta($sesion);

        if ($visita) {
            $visita->forceFill([
                'salida_at' => now(),
                'segundos' => (int) $visita->entrada_at->diffInSeconds(now()),
            ])->save();
        }

        return $sesion;
    }

    /** Cierra la sesión actual (salida voluntaria o vencimiento). */
    public static function cerrar(string $motivo = 'salida', ?Request $peticion = null): void
    {
        $sesion = self::sesion($peticion);

        if (! $sesion) {
            return;
        }

        self::cerrarVisitaAbierta($sesion);

        $sesion->forceFill([
            'cerrada_at' => now(),
            'ultima_at' => now(),
            'motivo_cierre' => $motivo,
            'segundos' => (int) $sesion->iniciada_at->diffInSeconds(now()),
        ])->save();

        self::$sesion = null;
        self::$resuelta = false;
    }

    /** Registra el paso por una página y cierra la anterior. */
    public static function visitar(Request $peticion, string $titulo): ?Visita
    {
        $sesion = self::sesion($peticion);

        if (! $sesion) {
            return null;
        }

        self::cerrarVisitaAbierta($sesion);

        $sesion->forceFill([
            'ultima_at' => now(),
            'segundos' => (int) $sesion->iniciada_at->diffInSeconds(now()),
        ])->save();

        return Visita::query()->create([
            'sesion_id' => $sesion->getKey(),
            'user_id' => $sesion->user_id,
            'ruta' => $peticion->route()?->getName(),
            'url' => mb_substr($peticion->fullUrl(), 0, 500),
            'titulo' => mb_substr($titulo, 0, 160),
            'entrada_at' => now(),
        ]);
    }

    /**
     * Deja un asiento en la bitácora.
     *
     * @param  array<string, mixed>|null  $datos
     */
    public static function registrar(
        AccionBitacora $accion,
        string $descripcion,
        ?string $entidad = null,
        int|string|null $entidadId = null,
        ?array $datos = null,
    ): ?Bitacora {
        $usuario = Auth::user();
        $peticion = request();

        try {
            return Bitacora::query()->create([
                'sesion_id' => self::sesion($peticion)?->getKey(),
                'user_id' => $usuario?->getKey(),
                'usuario_nombre' => $usuario?->name,
                'accion' => $accion,
                'entidad' => $entidad,
                'entidad_id' => is_numeric($entidadId) ? (int) $entidadId : null,
                'descripcion' => mb_substr($descripcion, 0, 300),
                'datos' => $datos,
                'ruta' => $peticion->route()?->getName(),
                'ip' => $peticion->ip(),
            ]);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Cierra las sesiones que llevan demasiado tiempo sin actividad. Se llama
     * al abrir el panel de trazabilidad para que los números no mientan.
     */
    public static function vencerInactivas(): int
    {
        $limite = now()->subMinutes(self::MINUTOS_INACTIVIDAD);
        $vencidas = 0;

        Sesion::query()
            ->whereNull('cerrada_at')
            ->where('ultima_at', '<', $limite)
            ->get()
            ->each(function (Sesion $sesion) use (&$vencidas) {
                self::cerrarVisitaAbierta($sesion);

                $sesion->forceFill([
                    'cerrada_at' => $sesion->ultima_at,
                    'motivo_cierre' => 'expirada',
                    'segundos' => (int) $sesion->iniciada_at->diffInSeconds($sesion->ultima_at),
                ])->save();

                $vencidas++;
            });

        return $vencidas;
    }

    private static function visitaAbierta(Sesion $sesion): ?Visita
    {
        return Visita::query()
            ->where('sesion_id', $sesion->getKey())
            ->whereNull('salida_at')
            ->latest('entrada_at')
            ->first();
    }

    private static function cerrarVisitaAbierta(Sesion $sesion): void
    {
        $visita = self::visitaAbierta($sesion);

        if (! $visita) {
            return;
        }

        $visita->forceFill([
            'salida_at' => now(),
            'segundos' => (int) $visita->entrada_at->diffInSeconds(now()),
        ])->save();
    }

    /** Olvida la sesión memorizada (lo usan las pruebas y el cierre de sesión). */
    public static function olvidar(): void
    {
        self::$sesion = null;
        self::$resuelta = false;
    }

    private static function navegador(string $agente): string
    {
        return match (true) {
            str_contains($agente, 'Edg/') => 'Edge',
            str_contains($agente, 'OPR/') => 'Opera',
            str_contains($agente, 'Chrome/') => 'Chrome',
            str_contains($agente, 'Firefox/') => 'Firefox',
            str_contains($agente, 'Safari/') => 'Safari',
            default => 'Otro',
        };
    }

    private static function plataforma(string $agente): string
    {
        return match (true) {
            str_contains($agente, 'Android') => 'Android',
            str_contains($agente, 'iPhone'), str_contains($agente, 'iPad') => 'iOS',
            str_contains($agente, 'Windows') => 'Windows',
            str_contains($agente, 'Mac OS') => 'macOS',
            str_contains($agente, 'Linux') => 'Linux',
            default => 'Otra',
        };
    }
}
