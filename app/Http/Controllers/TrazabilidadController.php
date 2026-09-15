<?php

namespace App\Http\Controllers;

use App\Enums\AccionBitacora;
use App\Support\Rastro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Señales que manda el navegador: el latido que mantiene viva la permanencia
 * en la página y los eventos de clic en enlaces externos.
 */
class TrazabilidadController extends Controller
{
    public function latido(Request $request): JsonResponse
    {
        $sesion = Rastro::latir($request);

        return response()->json(['ok' => $sesion !== null]);
    }

    public function evento(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'accion' => ['required', Rule::in([AccionBitacora::Abrir->value, AccionBitacora::Ver->value])],
            'descripcion' => ['required', 'string', 'max:300'],
            'entidad' => ['nullable', 'string', 'max:60'],
            'entidad_id' => ['nullable', 'integer'],
            'url' => ['nullable', 'string', 'max:500'],
        ]);

        Rastro::registrar(
            AccionBitacora::from($datos['accion']),
            $datos['descripcion'],
            $datos['entidad'] ?? null,
            $datos['entidad_id'] ?? null,
            isset($datos['url']) ? ['url' => $datos['url']] : null,
        );

        return response()->json(['ok' => true]);
    }
}
