<?php

use App\Enums\GrupoAcceso;
use App\Models\Acceso;
use App\Models\Area;
use App\Models\Departamento;
use App\Models\User;

test('las flechas suben y bajan una tarjeta dentro de su mosaico', function () {
    $admin = User::factory()->administrador()->create();

    $a = Acceso::factory()->create(['nombre' => 'Uno', 'grupo' => GrupoAcceso::Propio, 'orden' => 0]);
    $b = Acceso::factory()->create(['nombre' => 'Dos', 'grupo' => GrupoAcceso::Propio, 'orden' => 1]);

    $this->actingAs($admin)->from(route('admin.tarjetas.index'))
        ->put(route('admin.orden.mover', ['tarjeta', $b->id, 'arriba']))
        ->assertRedirect(route('admin.tarjetas.index'));

    expect($b->fresh()->orden)->toBe(0)->and($a->fresh()->orden)->toBe(1);

    $this->actingAs($admin)->put(route('admin.orden.mover', ['tarjeta', $b->id, 'abajo']));

    expect($b->fresh()->orden)->toBe(1)->and($a->fresh()->orden)->toBe(0);
});

test('mover más allá del extremo no cambia nada', function () {
    $admin = User::factory()->administrador()->create();
    $a = Acceso::factory()->create(['grupo' => GrupoAcceso::Propio, 'orden' => 0]);

    $this->actingAs($admin)->put(route('admin.orden.mover', ['tarjeta', $a->id, 'arriba']));

    expect($a->fresh()->orden)->toBe(0);
});

test('una tarjeta no se mezcla con el orden de otro mosaico', function () {
    $admin = User::factory()->administrador()->create();

    $propia = Acceso::factory()->create(['grupo' => GrupoAcceso::Propio, 'orden' => 0]);
    $publica = Acceso::factory()->create(['grupo' => GrupoAcceso::Publico, 'orden' => 0]);

    $this->actingAs($admin)->put(route('admin.orden.mover', ['tarjeta', $publica->id, 'arriba']));

    expect($propia->fresh()->orden)->toBe(0)->and($publica->fresh()->orden)->toBe(0);
});

test('las áreas se ordenan dentro de su departamento', function () {
    $admin = User::factory()->administrador()->create();
    $departamento = Departamento::factory()->create();

    $uno = Area::factory()->for($departamento)->create(['orden' => 0]);
    $dos = Area::factory()->for($departamento)->create(['orden' => 1]);

    $this->actingAs($admin)->put(route('admin.orden.mover', ['area', $dos->id, 'arriba']));

    expect($dos->fresh()->orden)->toBe(0)->and($uno->fresh()->orden)->toBe(1);
});

test('la tarjeta nueva se va al final de su mosaico', function () {
    $admin = User::factory()->administrador()->create();
    Acceso::factory()->create(['grupo' => GrupoAcceso::SharePoint, 'orden' => 7]);

    $this->actingAs($admin)->post(route('admin.tarjetas.store'), [
        'nombre' => 'SharePoint Calidad',
        'url' => 'https://ejemplo.sharepoint.com/sites/Calidad',
        'tono' => 'azul',
        'grupo' => 'sharepoint',
        'activo' => 1,
    ])->assertRedirect(route('admin.tarjetas.index'));

    expect(Acceso::query()->where('nombre', 'SharePoint Calidad')->value('orden'))->toBe(8);
});

test('un usuario normal no puede reordenar', function () {
    $acceso = Acceso::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.orden.mover', ['tarjeta', $acceso->id, 'arriba']))
        ->assertForbidden();
});
