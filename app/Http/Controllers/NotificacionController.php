<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $usuario */
        $usuario = $request->user();

        return view('notificaciones.index', [
            'usuario' => $usuario,
            'notificaciones' => $usuario->notificaciones()->paginate(20),
        ]);
    }

    /** Marca una como leída y, si trae enlace, lleva a él. */
    public function leer(Request $request, Notificacion $notificacion): JsonResponse|RedirectResponse
    {
        abort_unless($notificacion->user_id === $request->user()->id, 404);

        if (! $notificacion->leida()) {
            $notificacion->forceFill(['leida_at' => now()])->save();
        }

        if ($request->expectsJson()) {
            return response()->json(['leida' => true, 'url' => $notificacion->url]);
        }

        return $notificacion->url ? redirect()->to($notificacion->url) : back();
    }

    public function leerTodas(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->notificacionesSinLeer()->update(['leida_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['leidas' => true]);
        }

        return back()->with('aviso', 'Todas las notificaciones quedaron como leídas.');
    }
}
