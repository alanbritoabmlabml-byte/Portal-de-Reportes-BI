<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccionBitacora;
use App\Enums\GrupoAcceso;
use App\Http\Controllers\Controller;
use App\Models\Acceso;
use App\Support\Rastro;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Tarjetas del menú principal (accesos a otros sitios).
 */
class AccesoController extends Controller
{
    public function index(): View
    {
        return view('admin.tarjetas.index', [
            'tarjetas' => Acceso::query()->orderBy('orden')->orderBy('nombre')->get()->groupBy(fn (Acceso $a) => $a->grupo->value),
            'tonos' => Acceso::TONOS,
            'grupos' => GrupoAcceso::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $acceso = Acceso::query()->create($this->validar($request));

        Rastro::registrar(AccionBitacora::Crear, "Creó la tarjeta «{$acceso->nombre}»", 'Tarjeta', $acceso->getKey());

        return redirect()->route('admin.tarjetas.index')->with('aviso', "Tarjeta «{$acceso->nombre}» creada.");
    }

    public function update(Request $request, Acceso $acceso): RedirectResponse
    {
        $antes = $acceso->only(['nombre', 'url', 'grupo', 'activo']);
        $acceso->update($this->validar($request, $acceso));

        Rastro::registrar(
            AccionBitacora::Editar,
            "Editó la tarjeta «{$acceso->nombre}»",
            'Tarjeta',
            $acceso->getKey(),
            ['antes' => $antes, 'despues' => $acceso->only(['nombre', 'url', 'grupo', 'activo'])],
        );

        return back()->with('aviso', "Tarjeta «{$acceso->nombre}» actualizada.");
    }

    public function destroy(Acceso $acceso): RedirectResponse
    {
        $nombre = $acceso->nombre;
        $acceso->delete();

        Rastro::registrar(AccionBitacora::Eliminar, "Eliminó la tarjeta «{$nombre}»", 'Tarjeta', $acceso->getKey());

        return back()->with('aviso', "Tarjeta «{$nombre}» eliminada.");
    }

    /**
     * El orden ya no se teclea: la tarjeta nueva se va al final de su mosaico
     * y desde ahí se sube o se baja con las flechas del listado.
     */
    private function siguienteOrden(GrupoAcceso $grupo): int
    {
        return (int) Acceso::query()->where('grupo', $grupo)->max('orden') + 1;
    }

    /**
     * @return array<string, mixed>
     */
    private function validar(Request $request, ?Acceso $acceso = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80', Rule::unique('accesos', 'nombre')->ignore($acceso)],
            'descripcion' => ['nullable', 'string', 'max:200'],
            'url' => ['required', 'string', 'max:500', 'regex:#^(https?://|mailto:)#i'],
            'texto_boton' => ['nullable', 'string', 'max:40'],
            'imagen' => ['nullable', 'string', 'max:300'],
            'tono' => ['required', Rule::in(Acceso::TONOS)],
            'grupo' => ['required', Rule::enum(GrupoAcceso::class)],
            'etiqueta' => ['nullable', 'string', 'max:40'],
            'nueva_pestana' => ['nullable', 'boolean'],
            'destacado' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ], ['url.regex' => 'La URL debe empezar con http://, https:// o mailto:.']);

        return [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'url' => $datos['url'],
            'texto_boton' => ($datos['texto_boton'] ?? null) ?: 'Ingresar',
            'imagen' => ($datos['imagen'] ?? null) ?: null,
            'tono' => $datos['tono'],
            'grupo' => $datos['grupo'],
            'etiqueta' => $datos['etiqueta'] ?? null,
            'orden' => $acceso?->orden ?? $this->siguienteOrden(GrupoAcceso::from($datos['grupo'])),
            'nueva_pestana' => $request->boolean('nueva_pestana'),
            'destacado' => $request->boolean('destacado'),
            'activo' => $request->boolean('activo'),
        ];
    }
}
