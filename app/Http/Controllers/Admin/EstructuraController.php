<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccionBitacora;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Departamento;
use App\Support\Rastro;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Departamentos y áreas: el árbol del que cuelgan los reportes.
 */
class EstructuraController extends Controller
{
    /** @var list<string> nombres de icono disponibles (ver components/icono) */
    public const ICONOS = [
        'edificio', 'fabrica', 'tienda', 'almacen', 'chip', 'brujula', 'maletin', 'personas',
        'moneda', 'bolsa', 'vaso', 'cubo', 'engranaje', 'rollo', 'grafico', 'megafono',
        'camion', 'cajas', 'pellets',
    ];

    public function index(): View
    {
        return view('admin.estructura.index', [
            'departamentos' => Departamento::query()->orderBy('orden')->orderBy('nombre')
                ->with(['areas' => fn ($q) => $q->withCount('reportes')])
                ->get(),
            'iconos' => self::ICONOS,
        ]);
    }

    public function guardarDepartamento(Request $request): RedirectResponse
    {
        $datos = $this->validarDepartamento($request);

        $departamento = Departamento::query()->create($datos + ['slug' => $this->slugUnico(Departamento::class, $datos['nombre'])]);

        Rastro::registrar(AccionBitacora::Crear, "Creó el departamento «{$departamento->nombre}»", 'Departamento', $departamento->getKey());

        return back()->with('aviso', "Departamento «{$departamento->nombre}» creado.");
    }

    public function actualizarDepartamento(Request $request, Departamento $departamento): RedirectResponse
    {
        $departamento->update($this->validarDepartamento($request, $departamento));

        Rastro::registrar(AccionBitacora::Editar, "Editó el departamento «{$departamento->nombre}»", 'Departamento', $departamento->getKey());

        return back()->with('aviso', "Departamento «{$departamento->nombre}» actualizado.");
    }

    public function eliminarDepartamento(Departamento $departamento): RedirectResponse
    {
        abort_if($departamento->areas()->exists(), 422, 'Primero elimina o mueve sus áreas.');

        $nombre = $departamento->nombre;
        $departamento->delete();

        Rastro::registrar(AccionBitacora::Eliminar, "Eliminó el departamento «{$nombre}»", 'Departamento', $departamento->getKey());

        return back()->with('aviso', "Departamento «{$nombre}» eliminado.");
    }

    public function guardarArea(Request $request): RedirectResponse
    {
        $datos = $this->validarArea($request);

        $area = Area::query()->create($datos + ['slug' => $this->slugUnico(Area::class, $datos['nombre'])]);

        Rastro::registrar(AccionBitacora::Crear, "Creó el área «{$area->nombre}»", 'Area', $area->getKey());

        return back()->with('aviso', "Área «{$area->nombre}» creada.");
    }

    public function actualizarArea(Request $request, Area $area): RedirectResponse
    {
        $area->update($this->validarArea($request, $area));

        Rastro::registrar(AccionBitacora::Editar, "Editó el área «{$area->nombre}»", 'Area', $area->getKey());

        return back()->with('aviso', "Área «{$area->nombre}» actualizada.");
    }

    public function eliminarArea(Area $area): RedirectResponse
    {
        abort_if($area->reportes()->exists(), 422, 'Primero elimina o mueve sus reportes.');

        $nombre = $area->nombre;
        $area->delete();

        Rastro::registrar(AccionBitacora::Eliminar, "Eliminó el área «{$nombre}»", 'Area', $area->getKey());

        return back()->with('aviso', "Área «{$nombre}» eliminada.");
    }

    /**
     * @return array{nombre:string,descripcion:?string,icono:string,orden:int}
     *
     * El orden ya no se teclea: se hereda o se va al final, y luego se mueve
     * con las flechas del listado.
     */
    private function validarDepartamento(Request $request, ?Departamento $departamento = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80', Rule::unique('departamentos', 'nombre')->ignore($departamento)],
            'descripcion' => ['nullable', 'string', 'max:200'],
            'icono' => ['required', Rule::in(self::ICONOS)],
        ]);

        return [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'icono' => $datos['icono'],
            'orden' => $departamento?->orden ?? ((int) Departamento::query()->max('orden') + 1),
        ];
    }

    /**
     * @return array{departamento_id:int,nombre:string,descripcion:?string,icono:string,orden:int}
     */
    private function validarArea(Request $request, ?Area $area = null): array
    {
        $datos = $request->validate([
            'departamento_id' => ['required', 'integer', 'exists:departamentos,id'],
            'nombre' => ['required', 'string', 'max:80', Rule::unique('areas', 'nombre')->ignore($area)],
            'descripcion' => ['nullable', 'string', 'max:200'],
            'icono' => ['required', Rule::in(self::ICONOS)],
        ]);

        return [
            'departamento_id' => (int) $datos['departamento_id'],
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'icono' => $datos['icono'],
            'orden' => $area?->orden ?? ((int) Area::query()->where('departamento_id', (int) $datos['departamento_id'])->max('orden') + 1),
        ];
    }

    /**
     * @param  class-string<Departamento|Area>  $modelo
     */
    private function slugUnico(string $modelo, string $nombre): string
    {
        $base = Str::slug($nombre) ?: 'item';
        $slug = $base;
        $n = 2;

        while ($modelo::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$n}";
            $n++;
        }

        return $slug;
    }
}
