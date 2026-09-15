<?php

namespace App\Providers;

use App\Models\Acceso;
use App\Models\Area;
use App\Models\Reporte;
use App\Models\User;
use App\Policies\ReportePolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        // Los favoritos guardan el tipo como alias corto, no como FQCN
        Relation::enforceMorphMap([
            'acceso' => Acceso::class,
            'area' => Area::class,
        ]);

        Gate::define('administrar', fn (User $usuario) => $usuario->esAdministrador());
        Gate::policy(Reporte::class, ReportePolicy::class);

        // Cinco intentos de acceso por minuto y por IP
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        Paginator::defaultView('vendor.pagination.marca');
        Paginator::defaultSimpleView('vendor.pagination.marca');
    }
}
