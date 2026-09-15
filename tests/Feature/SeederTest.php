<?php

use App\Models\Acceso;
use App\Models\Area;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

test('los seeders levantan un portal navegable de ejemplo', function () {
    Artisan::call('db:seed', ['--force' => true]);

    expect(Acceso::query()->where('activo', true)->count())->toBeGreaterThanOrEqual(8);
    expect(Area::query()->whereIn('slug', ['rrhh', 'gerencia', 'direccion', 'bolsas', 'termoformado', 'expandido', 'inyeccion'])->count())->toBe(7);
    expect(Reporte::query()->count())->toBeGreaterThan(20);

    $admin = User::query()->where('email', 'amoscoso@plasticoscarmen.com')->firstOrFail();
    expect($admin->esAdministrador())->toBeTrue();

    $this->actingAs($admin)->get(route('menu'))->assertOk()->assertSee('Reportes BI');
    $this->actingAs($admin)->get(route('bi.area', 'rrhh'))->assertOk()->assertSee('Control de asistencia');

    // La cuenta de prueba de RRHH ve su área pero no Dirección
    $rrhh = User::query()->where('email', 'rrhh.prueba@plasticoscarmen.com')->firstOrFail();
    $this->actingAs($rrhh)->get(route('bi.area', 'rrhh'))->assertOk();
    $this->actingAs($rrhh)->get(route('bi.area', 'direccion'))->assertForbidden();
});
