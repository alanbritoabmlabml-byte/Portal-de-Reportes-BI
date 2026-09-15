<?php

namespace App\Http\Controllers;

use App\Enums\Tema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Preferencia de apariencia (claro / oscuro / según el sistema). Se guarda en
 * el usuario para que lo siga en cualquier equipo; el navegador solo la aplica.
 */
class TemaController extends Controller
{
    public function guardar(Request $request): JsonResponse|RedirectResponse
    {
        $datos = $request->validate([
            'tema' => ['required', Rule::enum(Tema::class)],
        ]);

        $request->user()->forceFill(['tema' => $datos['tema']])->save();

        if ($request->expectsJson()) {
            return response()->json(['tema' => $datos['tema']]);
        }

        return back();
    }
}
