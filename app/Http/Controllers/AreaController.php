<?php

namespace App\Http\Controllers;

use App\Enums\AccionBitacora;
use App\Enums\TipoReporte;
use App\Models\Area;
use App\Models\Reporte;
use App\Models\User;
use App\Support\Rastro;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Página de un área de BI: todos los Power BI que el usuario puede ver,
 * cada uno en su iframe.
 */
class AreaController extends Controller
{
    public function show(Request $request, Area $area): View
    {
        /** @var User $usuario */
        $usuario = $request->user();
        $usuario->load('favoritos');

        $reportes = $area->reportes()->visiblesPara($usuario)->get();

        abort_if($reportes->isEmpty(), 403, 'No tienes reportes asignados en esta área.');

        $tipoFiltro = TipoReporte::tryFrom((string) $request->query('tipo'));

        // Solo se ofrecen como filtro los tipos que realmente hay en el área
        $tipos = $reportes->pluck('tipo')->unique()->sortBy(fn (TipoReporte $t) => $t->etiqueta())->values();

        if ($tipoFiltro && $tipos->contains($tipoFiltro)) {
            $reportes = $reportes->where('tipo', $tipoFiltro)->values();
        } else {
            $tipoFiltro = null;
        }

        Rastro::registrar(
            AccionBitacora::Ver,
            "Consultó los tableros del área {$area->nombre}",
            'Area',
            $area->getKey(),
            ['tableros' => $reportes->count()],
        );

        return view('bi.area', [
            'usuario' => $usuario,
            'area' => $area->load('departamento'),
            'reportes' => $reportes,
            'tipos' => $tipos,
            'tipoFiltro' => $tipoFiltro,
            'esFavorito' => $usuario->tieneFavorito($area),
            // Otras áreas del mismo departamento que también puede ver, para saltar entre ellas
            'hermanas' => $area->departamento->areas()
                ->whereKeyNot($area->id)
                ->whereHas('reportes', fn ($q) => $q->visiblesPara($usuario))
                ->get(),
        ]);
    }

    /** Tablero de muestra que se ve mientras no se pega la URL real de Power BI. */
    public function demo(Request $request, Reporte $reporte): View
    {
        $this->authorize('view', $reporte);

        return view('bi.demo', ['reporte' => $reporte->load('area')]);
    }
}
