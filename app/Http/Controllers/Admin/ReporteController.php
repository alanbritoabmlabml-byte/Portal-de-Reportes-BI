<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccionBitacora;
use App\Enums\TipoNotificacion;
use App\Enums\TipoReporte;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReporteRequest;
use App\Models\Departamento;
use App\Models\Notificacion;
use App\Models\Reporte;
use App\Models\User;
use App\Support\Rastro;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Administración de los reportes de Power BI: link del iframe, tipo, área y
 * quién puede verlo.
 */
class ReporteController extends Controller
{
    public function index(Request $request): View
    {
        $consulta = Reporte::query()->with(['area.departamento', 'usuarios'])
            ->join('areas', 'areas.id', '=', 'reportes.area_id')
            ->join('departamentos', 'departamentos.id', '=', 'areas.departamento_id')
            ->orderBy('departamentos.orden')->orderBy('areas.orden')->orderBy('reportes.orden')->orderBy('reportes.titulo')
            ->select('reportes.*');

        if ($area = $request->integer('area')) {
            $consulta->where('reportes.area_id', $area);
        }

        if ($tipo = TipoReporte::tryFrom((string) $request->query('tipo'))) {
            $consulta->where('reportes.tipo', $tipo);
        }

        if ($busqueda = trim((string) $request->query('q'))) {
            $consulta->where('reportes.titulo', 'like', "%{$busqueda}%");
        }

        return view('admin.reportes.index', [
            'reportes' => $consulta->paginate(15)->withQueryString(),
            'departamentos' => Departamento::query()->orderBy('orden')->with('areas')->get(),
            'usuarios' => User::query()->where('activo', true)->orderBy('name')->get(),
            'tipos' => TipoReporte::opciones(),
            'filtros' => ['area' => $area, 'tipo' => $tipo?->value, 'q' => $busqueda],
        ]);
    }

    public function store(ReporteRequest $request): RedirectResponse
    {
        $reporte = Reporte::query()->create($request->datos());

        if ($reporte->publico && $reporte->activo) {
            Notificacion::avisar(
                User::query()->where('activo', true)->get(),
                "Nuevo reporte: {$reporte->titulo}",
                "Se publicó un nuevo tablero en {$reporte->area->nombre}.",
                TipoNotificacion::Info,
                route('bi.area', $reporte->area),
            );
        }

        Rastro::registrar(AccionBitacora::Crear, "Creó el reporte «{$reporte->titulo}» en {$reporte->area->nombre}", 'Reporte', $reporte->getKey());

        return redirect()->route('admin.reportes.index', ['area' => $reporte->area_id])
            ->with('aviso', "Reporte «{$reporte->titulo}» creado.");
    }

    public function update(ReporteRequest $request, Reporte $reporte): RedirectResponse
    {
        $antes = $reporte->only(['titulo', 'tipo', 'url_iframe', 'activo', 'publico']);
        $reporte->update($request->datos($reporte));

        Rastro::registrar(
            AccionBitacora::Editar,
            "Editó el reporte «{$reporte->titulo}»",
            'Reporte',
            $reporte->getKey(),
            ['antes' => $antes, 'despues' => $reporte->only(['titulo', 'tipo', 'url_iframe', 'activo', 'publico'])],
        );

        return back()->with('aviso', "Reporte «{$reporte->titulo}» actualizado.");
    }

    public function destroy(Reporte $reporte): RedirectResponse
    {
        $this->authorize('delete', $reporte);
        $titulo = $reporte->titulo;
        $reporte->delete();

        Rastro::registrar(AccionBitacora::Eliminar, "Eliminó el reporte «{$titulo}»", 'Reporte', $reporte->getKey());

        return back()->with('aviso', "Reporte «{$titulo}» eliminado.");
    }

    /**
     * Define exactamente qué usuarios pueden ver el reporte. Los que ganan
     * acceso reciben una notificación.
     */
    public function accesos(Request $request, Reporte $reporte): RedirectResponse
    {
        $this->authorize('update', $reporte);

        $datos = $request->validate([
            'usuarios' => ['nullable', 'array'],
            'usuarios.*' => ['integer', 'exists:users,id'],
        ]);

        $nuevos = collect($datos['usuarios'] ?? [])->map(fn ($id) => (int) $id)->unique();
        $anteriores = $reporte->usuarios()->pluck('users.id');

        $reporte->usuarios()->sync($nuevos->all());

        $agregados = User::query()->whereKey($nuevos->diff($anteriores)->all())->get();

        Notificacion::avisar(
            $agregados,
            "Tienes acceso a «{$reporte->titulo}»",
            "Se te habilitó la vista del tablero de {$reporte->area->nombre}.",
            TipoNotificacion::Exito,
            route('bi.area', $reporte->area),
        );

        Rastro::registrar(
            AccionBitacora::Acceso,
            "Cambió quién puede ver «{$reporte->titulo}»: {$nuevos->count()} usuario(s)",
            'Reporte',
            $reporte->getKey(),
            ['agregados' => $agregados->pluck('name')->all(), 'total' => $nuevos->count()],
        );

        return back()->with('aviso', "Accesos de «{$reporte->titulo}» guardados: {$nuevos->count()} usuario(s).");
    }
}
