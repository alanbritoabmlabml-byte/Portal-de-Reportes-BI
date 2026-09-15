<?php

use App\Enums\Tema;
use App\Models\User;

test('el usuario nuevo sigue el tema del sistema', function () {
    expect(User::factory()->create()->fresh()->tema)->toBe(Tema::Sistema);
});

test('guardar la preferencia de tema la deja en la cuenta', function () {
    $usuario = User::factory()->create();

    $this->actingAs($usuario)
        ->postJson(route('tema.guardar'), ['tema' => 'oscuro'])
        ->assertOk()
        ->assertJson(['tema' => 'oscuro']);

    expect($usuario->fresh()->tema)->toBe(Tema::Oscuro);
});

test('un tema desconocido se rechaza', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('tema.guardar'), ['tema' => 'neon'])
        ->assertStatus(422);
});

test('la preferencia guardada viaja en el html para que no parpadee', function () {
    $usuario = User::factory()->create(['tema' => Tema::Oscuro]);

    $this->actingAs($usuario)->get(route('menu'))
        ->assertOk()
        ->assertSee('data-tema-pref="oscuro"', false);
});
