<?php

namespace App\Http\Controllers;

use App\Enums\GrupoAcceso;
use App\Models\Acceso;
use App\Models\Area;
use App\Models\Departamento;
use App\Models\Favorito;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Menú principal: la pantalla que aparece en cuanto se inicia sesión.
 */
class MenuController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var User $usuario */
        $usuario = $request->user();
        $usuario->load('favoritos');

        $accesos = Acceso::query()->where('activo', true)->orderBy('orden')->orderBy('nombre')->get();

        // Un mosaico por grupo, en el orden en que los declara el enum y sin
        // los grupos que hoy no tienen ninguna tarjeta activa.
        $porGrupo = collect(GrupoAcceso::cases())
            ->map(fn (GrupoAcceso $g) => ['grupo' => $g, 'tarjetas' => $accesos->where('grupo', $g)->values()])
            ->filter(fn (array $m) => $m['tarjetas']->isNotEmpty())
            ->values();
        $departamentos = $this->departamentosVisibles($usuario);
        $areasVisibles = $departamentos->flatMap->areas->keyBy('id');

        return view('menu.index', [
            'usuario' => $usuario,
            'accesos' => $accesos,
            'mosaicos' => $porGrupo,
            'departamentos' => $departamentos,
            'favoritos' => $this->favoritosOrdenados($usuario, $accesos, $areasVisibles),
            'totalTableros' => $areasVisibles->sum('reportes_visibles_count'),
        ]);
    }

    /**
     * Departamentos con las áreas en las que el usuario tiene al menos un
     * reporte visible. Un área sin nada que mostrar no aparece en la lista.
     *
     * @return Collection<int, Departamento>
     */
    private function departamentosVisibles(User $usuario): Collection
    {
        $departamentos = Departamento::query()
            ->orderBy('orden')->orderBy('nombre')
            ->with(['areas' => fn ($q) => $q->withCount([
                'reportes as reportes_visibles_count' => fn (Builder $r) => $r->visiblesPara($usuario),
            ])])
            ->get();

        return $departamentos
            ->each(function (Departamento $d) {
                $areas = $d->areas
                    ->filter(fn (Area $a) => $a->reportes_visibles_count > 0)
                    ->each(fn (Area $a) => $a->setRelation('departamento', $d))
                    ->values();
                $d->setRelation('areas', $areas);
            })
            ->filter(fn (Departamento $d) => $d->areas->isNotEmpty())
            ->values();
    }

    /**
     * Elementos anclados por el usuario, en el orden en que los ancló, solo si
     * siguen activos y visibles para él.
     *
     * @param  Collection<int, Acceso>  $accesos
     * @param  Collection<int, Area>  $areasVisibles
     * @return Collection<int, Acceso|Area>
     */
    private function favoritosOrdenados(User $usuario, Collection $accesos, Collection $areasVisibles): Collection
    {
        $porId = $accesos->keyBy('id');

        return $usuario->favoritos
            ->sortBy('id')
            ->map(fn (Favorito $f) => match ($f->favorito_type) {
                'acceso' => $porId->get($f->favorito_id),
                'area' => $areasVisibles->get($f->favorito_id),
                default => null,
            })
            ->filter()
            ->values();
    }
}
