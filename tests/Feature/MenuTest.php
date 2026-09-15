<?php

use App\Enums\GrupoAcceso;
use App\Models\Acceso;
use App\Models\Area;
use App\Models\Departamento;
use App\Models\Reporte;
use App\Models\User;

test('el menú saluda al usuario y muestra las tarjetas activas', function () {
    $usuario = User::factory()->create(['name' => 'Ana Pérez']);
    Acceso::factory()->create(['nombre' => 'Portafolio de Transformación', 'destacado' => true]);
    Acceso::factory()->create(['nombre' => 'Sitio oculto', 'activo' => false]);

    $this->actingAs($usuario)->get(route('menu'))
        ->assertOk()
        ->assertSee('Ana')
        ->assertSee('Portafolio de Transformación')
        ->assertSee('Acceder al portafolio')
        ->assertSee('Anclar a favoritos')
        ->assertDontSee('Sitio oculto');
});

test('el menú lista solo las áreas con reportes visibles para el usuario', function () {
    $usuario = User::factory()->create();
    $dep = Departamento::factory()->create(['nombre' => 'Producción']);
    $bolsas = Area::factory()->for($dep)->create(['nombre' => 'Bolsas']);
    $inyeccion = Area::factory()->for($dep)->create(['nombre' => 'Inyección']);
    $vacia = Area::factory()->for($dep)->create(['nombre' => 'Sin reportes']);

    Reporte::factory()->publico()->for($bolsas)->create();
    $privado = Reporte::factory()->for($inyeccion)->create();
    $privado->usuarios()->attach($usuario);
    Reporte::factory()->for($vacia)->create(); // privado, sin asignar

    $this->actingAs($usuario)->get(route('menu'))
        ->assertOk()
        ->assertSee('Bolsas')
        ->assertSee('Inyección')
        ->assertDontSee('Sin reportes');
});

test('el administrador ve todas las áreas con reportes activos', function () {
    $admin = User::factory()->administrador()->create();
    $area = Area::factory()->create(['nombre' => 'Dirección General']);
    Reporte::factory()->for($area)->create();

    $this->actingAs($admin)->get(route('menu'))
        ->assertOk()
        ->assertSee('Dirección General');
});

test('un área con solo reportes inactivos no aparece en el menú', function () {
    $admin = User::factory()->administrador()->create();
    $area = Area::factory()->create(['nombre' => 'Área Apagada']);
    Reporte::factory()->inactivo()->for($area)->create();

    $this->actingAs($admin)->get(route('menu'))
        ->assertOk()
        ->assertDontSee('Área Apagada');
});

test('los accesos se muestran agrupados en mosaicos', function () {
    $usuario = User::factory()->create();

    Acceso::factory()->create(['nombre' => 'Intranet propia', 'grupo' => GrupoAcceso::Propio]);
    Acceso::factory()->create(['nombre' => 'SharePoint Calidad', 'grupo' => GrupoAcceso::SharePoint]);

    $this->actingAs($usuario)->get(route('menu'))
        ->assertOk()
        ->assertSee('data-mosaico="propio"', false)
        ->assertSee('data-mosaico="sharepoint"', false)
        ->assertSeeInOrder(['Desarrollos propios', 'Intranet propia']);
});

test('un mosaico sin tarjetas no se dibuja', function () {
    $usuario = User::factory()->create();
    Acceso::factory()->create(['grupo' => GrupoAcceso::Propio]);

    $this->actingAs($usuario)->get(route('menu'))
        ->assertOk()
        ->assertDontSee('data-mosaico="microsoft"', false);
});
