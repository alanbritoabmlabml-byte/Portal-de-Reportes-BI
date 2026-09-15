<?php

use App\Models\Notificacion;
use App\Models\User;

test('la campana muestra el contador de notificaciones sin leer', function () {
    $usuario = User::factory()->create();
    Notificacion::factory()->count(3)->for($usuario, 'usuario')->create();
    Notificacion::factory()->leida()->for($usuario, 'usuario')->create();

    $this->actingAs($usuario)->get(route('menu'))
        ->assertOk()
        ->assertSee('data-contador-notificaciones>3<', false);
});

test('marcar una notificación como leída la actualiza y devuelve su enlace', function () {
    $usuario = User::factory()->create();
    $n = Notificacion::factory()->for($usuario, 'usuario')->create(['url' => 'https://portafolio.plasticoscarmen.com']);

    $this->actingAs($usuario)
        ->postJson(route('notificaciones.leer', $n))
        ->assertOk()
        ->assertJson(['leida' => true, 'url' => 'https://portafolio.plasticoscarmen.com']);

    expect($n->fresh()->leida_at)->not->toBeNull();
});

test('nadie puede leer la notificación de otro usuario', function () {
    $ajena = Notificacion::factory()->create();

    $this->actingAs(User::factory()->create())
        ->postJson(route('notificaciones.leer', $ajena))
        ->assertNotFound();
});

test('marcar todas deja el contador en cero', function () {
    $usuario = User::factory()->create();
    Notificacion::factory()->count(4)->for($usuario, 'usuario')->create();

    $this->actingAs($usuario)->postJson(route('notificaciones.leerTodas'))->assertOk();

    expect($usuario->notificacionesSinLeer()->count())->toBe(0);
});

test('la página de notificaciones lista las del usuario', function () {
    $usuario = User::factory()->create();
    Notificacion::factory()->for($usuario, 'usuario')->create(['titulo' => 'Se habilitó tu tablero']);
    Notificacion::factory()->create(['titulo' => 'Aviso de otra persona']);

    $this->actingAs($usuario)->get(route('notificaciones.index'))
        ->assertOk()
        ->assertSee('Se habilitó tu tablero')
        ->assertDontSee('Aviso de otra persona');
});
