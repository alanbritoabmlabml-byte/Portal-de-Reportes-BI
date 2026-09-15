<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccionBitacora;
use App\Enums\TipoNotificacion;
use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Models\User;
use App\Support\Rastro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Avisos que el administrador envía a todos los usuarios activos.
 */
class NotificacionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:120'],
            'mensaje' => ['nullable', 'string', 'max:500'],
            'tipo' => ['required', Rule::enum(TipoNotificacion::class)],
            'url' => ['nullable', 'string', 'max:500', 'url'],
        ], [], ['titulo' => 'título']);

        $destinatarios = User::query()->where('activo', true)->get();

        Notificacion::avisar(
            $destinatarios,
            $datos['titulo'],
            $datos['mensaje'] ?? null,
            TipoNotificacion::from($datos['tipo']),
            $datos['url'] ?? null,
        );

        Rastro::registrar(
            AccionBitacora::Crear,
            "Envió el aviso «{$datos['titulo']}» a {$destinatarios->count()} usuario(s)",
            'Notificacion',
            null,
            ['tipo' => $datos['tipo']],
        );

        return back()->with('aviso', "Aviso enviado a {$destinatarios->count()} usuario(s).");
    }
}
