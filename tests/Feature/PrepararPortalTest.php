<?php

use App\Models\User;

test('portal:preparar siembra los datos de ejemplo solo cuando la base está vacía', function () {
    $this->artisan('portal:preparar')->assertSuccessful();

    expect(User::query()->count())->toBeGreaterThan(0);
    $cuantos = User::query()->count();

    // Segunda corrida: idempotente, no duplica nada
    $this->artisan('portal:preparar')->assertSuccessful();
    expect(User::query()->count())->toBe($cuantos);
});

test('portal:preparar --sin-datos deja la base migrada pero vacía', function () {
    $this->artisan('portal:preparar', ['--sin-datos' => true])->assertSuccessful();

    expect(User::query()->count())->toBe(0);
});
