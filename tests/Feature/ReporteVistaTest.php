<?php

use App\Enums\TipoReporte;
use App\Models\Area;
use App\Models\Reporte;
use App\Models\User;

test('el usuario ve en el área solo los reportes públicos o asignados', function () {
    $usuario = User::factory()->create();
    $area = Area::factory()->create();

    Reporte::factory()->publico()->for($area)->create(['titulo' => 'Reporte público']);
    $asignado = Reporte::factory()->for($area)->create(['titulo' => 'Reporte asignado']);
    $asignado->usuarios()->attach($usuario);
    Reporte::factory()->for($area)->create(['titulo' => 'Reporte ajeno']);
    Reporte::factory()->publico()->inactivo()->for($area)->create(['titulo' => 'Reporte apagado']);

    $this->actingAs($usuario)->get(route('bi.area', $area))
        ->assertOk()
        ->assertSee('Reporte público')
        ->assertSee('Reporte asignado')
        ->assertDontSee('Reporte ajeno')
        ->assertDontSee('Reporte apagado');
});

test('sin reportes visibles el área responde 403', function () {
    $usuario = User::factory()->create();
    $area = Area::factory()->create();
    Reporte::factory()->for($area)->create();

    $this->actingAs($usuario)->get(route('bi.area', $area))->assertForbidden();
});

test('el administrador ve todos los reportes activos del área', function () {
    $admin = User::factory()->administrador()->create();
    $area = Area::factory()->create();
    Reporte::factory()->for($area)->create(['titulo' => 'Solo para asignados']);

    $this->actingAs($admin)->get(route('bi.area', $area))
        ->assertOk()
        ->assertSee('Solo para asignados');
});

test('el filtro por tipo deja solo los reportes de ese tipo', function () {
    $admin = User::factory()->administrador()->create();
    $area = Area::factory()->create();
    Reporte::factory()->for($area)->create(['titulo' => 'De control', 'tipo' => TipoReporte::Control]);
    Reporte::factory()->for($area)->create(['titulo' => 'Gerencial mensual', 'tipo' => TipoReporte::Gerencial]);

    $this->actingAs($admin)->get(route('bi.area', [$area, 'tipo' => 'control']))
        ->assertOk()
        ->assertSee('De control')
        ->assertDontSee('Gerencial mensual');
});

test('el iframe apunta a la URL guardada o al tablero de muestra', function () {
    $admin = User::factory()->administrador()->create();
    $area = Area::factory()->create();
    $real = Reporte::factory()->for($area)->create(['url_iframe' => 'https://app.powerbi.com/reportEmbed?reportId=abc']);
    $demo = Reporte::factory()->for($area)->create(['url_iframe' => 'demo']);

    $this->actingAs($admin)->get(route('bi.area', $area))
        ->assertOk()
        ->assertSee('https://app.powerbi.com/reportEmbed?reportId=abc', false)
        ->assertSee(route('demo.reporte', $demo), false);
});

test('el tablero de muestra respeta los permisos del reporte', function () {
    $usuario = User::factory()->create();
    $reporte = Reporte::factory()->create(['url_iframe' => 'demo']);

    $this->actingAs($usuario)->get(route('demo.reporte', $reporte))->assertForbidden();

    $reporte->usuarios()->attach($usuario);

    $this->actingAs($usuario)->get(route('demo.reporte', $reporte))
        ->assertOk()
        ->assertSee($reporte->titulo);
});
