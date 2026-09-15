<?php

use App\Enums\AccionBitacora;
use App\Enums\Rol;
use App\Models\Bitacora;
use App\Models\Sesion;
use App\Models\User;
use App\Models\Visita;
use App\Support\Rastro;

test('iniciar sesión abre una sesión trazada y la anota en la bitácora', function () {
    $usuario = User::factory()->create(['password' => 'secreta-12345']);

    $this->post(route('login'), ['email' => $usuario->email, 'password' => 'secreta-12345'])
        ->assertRedirect(route('menu'));

    $sesion = Sesion::query()->where('user_id', $usuario->id)->firstOrFail();

    expect($sesion->abierta())->toBeTrue();
    $this->assertDatabaseHas('bitacora', ['user_id' => $usuario->id, 'accion' => AccionBitacora::Entrar->value]);
});

test('cerrar sesión cierra la sesión trazada', function () {
    $usuario = User::factory()->create(['password' => 'secreta-12345']);

    $this->post(route('login'), ['email' => $usuario->email, 'password' => 'secreta-12345']);
    $this->post(route('logout'));

    expect(Sesion::query()->where('user_id', $usuario->id)->first()->cerrada_at)->not->toBeNull();
    $this->assertDatabaseHas('bitacora', ['user_id' => $usuario->id, 'accion' => AccionBitacora::Salir->value]);
});

test('navegar deja una visita con su permanencia', function () {
    $usuario = User::factory()->create();

    $this->actingAs($usuario)->get(route('menu'))->assertOk();
    $this->actingAs($usuario)->get(route('notificaciones.index'))->assertOk();

    $visitas = Visita::query()->where('user_id', $usuario->id)->orderBy('id')->get();

    expect($visitas)->toHaveCount(2)
        ->and($visitas[0]->titulo)->toBe('Menú principal')
        ->and($visitas[0]->salida_at)->not->toBeNull();
});

test('el latido no se registra a sí mismo como página visitada', function () {
    $usuario = User::factory()->create();

    $this->actingAs($usuario)->get(route('menu'));
    $this->actingAs($usuario)->postJson(route('trazabilidad.latido'))->assertOk();

    expect(Visita::query()->count())->toBe(1);
});

test('una sesión sin actividad se vence sola', function () {
    $usuario = User::factory()->create();

    $sesion = Sesion::factory()->abierta()->for($usuario, 'usuario')->create([
        'ultima_at' => now()->subHours(3),
    ]);

    expect(Rastro::vencerInactivas())->toBe(1)
        ->and($sesion->fresh()->motivo_cierre)->toBe('expirada');
});

test('el panel de trazabilidad es solo para administradores', function () {
    $this->actingAs(User::factory()->create(['rol' => Rol::Usuario]))
        ->get(route('admin.trazabilidad.index'))
        ->assertForbidden();
});

test('el panel muestra las métricas y el recorrido de una sesión', function () {
    $admin = User::factory()->create(['rol' => Rol::Administrador]);
    $sesion = Sesion::factory()->for(User::factory()->create(['name' => 'Rita Pérez']), 'usuario')->create([
        'iniciada_at' => now()->subDay(),
    ]);
    Visita::factory()->for($sesion)->create(['titulo' => 'Tableros · RRHH', 'entrada_at' => now()->subDay()]);
    Bitacora::factory()->for($sesion)->create(['descripcion' => 'Editó un tablero', 'created_at' => now()->subDay()]);

    $this->actingAs($admin)->get(route('admin.trazabilidad.index'))
        ->assertOk()
        ->assertSee('Rita Pérez')
        ->assertSee('Editó un tablero');

    $this->actingAs($admin)->get(route('admin.trazabilidad.sesion', $sesion))
        ->assertOk()
        ->assertSee('Tableros · RRHH');
});

test('la bitácora se exporta en xlsx y en vista imprimible', function () {
    $admin = User::factory()->create(['rol' => Rol::Administrador]);
    Sesion::factory()->create(['iniciada_at' => now()->subDay()]);

    $xlsx = $this->actingAs($admin)->get(route('admin.trazabilidad.exportar', 'xlsx'));
    $xlsx->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    expect(substr($xlsx->getContent(), 0, 2))->toBe('PK');

    $this->actingAs($admin)->get(route('admin.trazabilidad.exportar', 'pdf'))
        ->assertOk()
        ->assertSee('Bitácora de trazabilidad');
});

test('un formato de exportación desconocido no existe', function () {
    $this->actingAs(User::factory()->create(['rol' => Rol::Administrador]))
        ->get(url('/admin/trazabilidad/exportar/csv'))
        ->assertNotFound();
});
