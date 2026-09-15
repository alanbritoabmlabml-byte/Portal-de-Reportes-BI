<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccionBitacora;
use App\Http\Controllers\Controller;
use App\Models\Acceso;
use App\Models\Area;
use App\Models\Departamento;
use App\Models\Reporte;
use App\Support\Rastro;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

/**
 * Ordena los elementos con flechas en lugar de pedir un número a mano.
 *
 * Mover es intercambiar la posición con el vecino de arriba o de abajo dentro
 * de su grupo (las tarjetas dentro de su mosaico, las áreas dentro de su
 * departamento, los reportes dentro de su área).
 */
class OrdenController extends Controller
{
    public function mover(string $tipo, int $id, string $direccion): RedirectResponse
    {
        abort_unless(in_array($direccion, ['arriba', 'abajo'], true), 404);

        $elemento = $this->buscar($tipo, $id);
        $hermanos = $this->hermanos($tipo, $elemento)
            ->orderBy('orden')->orderBy('id')->get();

        $posicion = $hermanos->search(fn (Model $m) => $m->getKey() === $elemento->getKey());
        $destino = $direccion === 'arriba' ? $posicion - 1 : $posicion + 1;

        if ($posicion === false || $destino < 0 || $destino >= $hermanos->count()) {
            return back();
        }

        // Se renumeran todos con el elemento ya intercambiado: así el orden
        // queda consecutivo aunque viniera con huecos o repetidos.
        $ordenados = $hermanos->all();
        [$ordenados[$posicion], $ordenados[$destino]] = [$ordenados[$destino], $ordenados[$posicion]];

        foreach ($ordenados as $indice => $modelo) {
            if ((int) $modelo->orden !== $indice) {
                $modelo->forceFill(['orden' => $indice])->save();
            }
        }

        Rastro::registrar(
            AccionBitacora::Editar,
            'Cambió el orden de '.$this->nombreLegible($tipo).' «'.$this->nombre($elemento).'»',
            ucfirst($tipo),
            $elemento->getKey(),
            ['direccion' => $direccion],
        );

        return back();
    }

    private function buscar(string $tipo, int $id): Model
    {
        return match ($tipo) {
            'tarjeta' => Acceso::query()->findOrFail($id),
            'departamento' => Departamento::query()->findOrFail($id),
            'area' => Area::query()->findOrFail($id),
            'reporte' => Reporte::query()->findOrFail($id),
            default => abort(404),
        };
    }

    /**
     * @return Builder<covariant Model>
     */
    private function hermanos(string $tipo, Model $elemento): Builder
    {
        return match ($tipo) {
            'tarjeta' => Acceso::query()->where('grupo', $elemento->grupo),
            'departamento' => Departamento::query(),
            'area' => Area::query()->where('departamento_id', $elemento->departamento_id),
            'reporte' => Reporte::query()->where('area_id', $elemento->area_id),
            default => abort(404),
        };
    }

    private function nombre(Model $elemento): string
    {
        return (string) ($elemento->nombre ?? $elemento->titulo ?? $elemento->getKey());
    }

    private function nombreLegible(string $tipo): string
    {
        return match ($tipo) {
            'tarjeta' => 'la tarjeta',
            'departamento' => 'el departamento',
            'area' => 'el área',
            'reporte' => 'el reporte',
            default => 'el elemento',
        };
    }
}
