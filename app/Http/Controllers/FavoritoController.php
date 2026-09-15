<?php

namespace App\Http\Controllers;

use App\Models\Acceso;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FavoritoController extends Controller
{
    /**
     * Ancla o desancla un elemento. Responde JSON al fetch del menú y con una
     * redirección si llega desde un formulario sin JavaScript.
     */
    public function alternar(Request $request): JsonResponse|RedirectResponse
    {
        $datos = $request->validate([
            'tipo' => ['required', Rule::in(['acceso', 'area'])],
            'id' => ['required', 'integer'],
        ]);

        /** @var User $usuario */
        $usuario = $request->user();

        $elemento = $datos['tipo'] === 'acceso'
            ? Acceso::query()->where('activo', true)->findOrFail($datos['id'])
            : Area::query()->whereHas('reportes', fn ($q) => $q->visiblesPara($usuario))->findOrFail($datos['id']);

        $existente = $usuario->favoritos()
            ->where('favorito_type', $elemento->getMorphClass())
            ->where('favorito_id', $elemento->getKey())
            ->first();

        if ($existente) {
            $existente->delete();
            $anclado = false;
        } elseif ($usuario->favoritos()->count() >= User::MAX_FAVORITOS) {
            // Tope de anclados: el riel lateral muestra como máximo seis
            $mensaje = 'Ya tienes '.User::MAX_FAVORITOS.' favoritos anclados. Quita uno para anclar otro.';

            if ($request->expectsJson()) {
                return response()->json(['anclado' => false, 'tope' => true, 'mensaje' => $mensaje], 422);
            }

            return back()->withErrors(['favoritos' => $mensaje]);
        } else {
            $usuario->favoritos()->create([
                'favorito_type' => $elemento->getMorphClass(),
                'favorito_id' => $elemento->getKey(),
            ]);
            $anclado = true;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'anclado' => $anclado,
                'mensaje' => $anclado ? 'Anclado a favoritos' : 'Quitado de favoritos',
            ]);
        }

        return back()->with('aviso', $anclado ? 'Anclado a favoritos.' : 'Quitado de favoritos.');
    }
}
