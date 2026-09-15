<?php

use App\Models\Acceso;
use App\Models\Area;
use App\Models\Reporte;
use App\Models\User;

test('anclar y desanclar una tarjeta responde JSON y persiste', function () {
    $usuario = User::factory()->create();
    $acceso = Acceso::factory()->create();

    $this->actingAs($usuario)
        ->postJson(route('favoritos.alternar'), ['tipo' => 'acceso', 'id' => $acceso->id])
        ->assertOk()
        ->assertJson(['anclado' => true]);

    $this->assertDatabaseHas('favoritos', ['user_id' => $usuario->id, 'favorito_type' => 'acceso', 'favorito_id' => $acceso->id]);

    $this->actingAs($usuario)
        ->postJson(route('favoritos.alternar'), ['tipo' => 'acceso', 'id' => $acceso->id])
        ->assertOk()
        ->assertJson(['anclado' => false]);

    $this->assertDatabaseCount('favoritos', 0);
});

test('los favoritos aparecen en el menú en el orden en que se anclaron', function () {
    $usuario = User::factory()->create();
    $a = Acceso::factory()->create(['nombre' => 'Acceso Alfa']);
    $b = Acceso::factory()->create(['nombre' => 'Acceso Beta']);

    $this->actingAs($usuario)->postJson(route('favoritos.alternar'), ['tipo' => 'acceso', 'id' => $b->id]);
    $this->actingAs($usuario)->postJson(route('favoritos.alternar'), ['tipo' => 'acceso', 'id' => $a->id]);

    $this->actingAs($usuario)->get(route('menu'))
        ->assertOk()
        ->assertSeeInOrder(['Mis favoritos', 'Acceso Beta', 'Acceso Alfa']);
});

test('no se puede anclar un área sin reportes visibles', function () {
    $usuario = User::factory()->create();
    $area = Area::factory()->create();
    Reporte::factory()->for($area)->create();

    $this->actingAs($usuario)
        ->postJson(route('favoritos.alternar'), ['tipo' => 'area', 'id' => $area->id])
        ->assertNotFound();
});

test('un área con reportes visibles sí se puede anclar', function () {
    $usuario = User::factory()->create();
    $area = Area::factory()->create();
    Reporte::factory()->publico()->for($area)->create();

    $this->actingAs($usuario)
        ->postJson(route('favoritos.alternar'), ['tipo' => 'area', 'id' => $area->id])
        ->assertOk()
        ->assertJson(['anclado' => true]);
});

test('sin JavaScript el formulario de favorito redirige con aviso', function () {
    $usuario = User::factory()->create();
    $acceso = Acceso::factory()->create();

    $this->actingAs($usuario)->from(route('menu'))
        ->post(route('favoritos.alternar'), ['tipo' => 'acceso', 'id' => $acceso->id])
        ->assertRedirect(route('menu'))
        ->assertSessionHas('aviso');
});

test('no se pueden anclar más favoritos que el tope', function () {
    $usuario = User::factory()->create();
    $accesos = Acceso::factory()->count(User::MAX_FAVORITOS + 1)->create();

    foreach ($accesos->take(User::MAX_FAVORITOS) as $acceso) {
        $this->actingAs($usuario)
            ->postJson(route('favoritos.alternar'), ['tipo' => 'acceso', 'id' => $acceso->id])
            ->assertOk()->assertJson(['anclado' => true]);
    }

    $this->actingAs($usuario)
        ->postJson(route('favoritos.alternar'), ['tipo' => 'acceso', 'id' => $accesos->last()->id])
        ->assertStatus(422)
        ->assertJson(['anclado' => false, 'tope' => true]);

    expect($usuario->favoritos()->count())->toBe(User::MAX_FAVORITOS);
});

test('al llegar al tope todavía se puede desanclar', function () {
    $usuario = User::factory()->create();
    $accesos = Acceso::factory()->count(User::MAX_FAVORITOS)->create();

    foreach ($accesos as $acceso) {
        $this->actingAs($usuario)->postJson(route('favoritos.alternar'), ['tipo' => 'acceso', 'id' => $acceso->id]);
    }

    $this->actingAs($usuario)
        ->postJson(route('favoritos.alternar'), ['tipo' => 'acceso', 'id' => $accesos->first()->id])
        ->assertOk()->assertJson(['anclado' => false]);

    expect($usuario->favoritos()->count())->toBe(User::MAX_FAVORITOS - 1);
});
