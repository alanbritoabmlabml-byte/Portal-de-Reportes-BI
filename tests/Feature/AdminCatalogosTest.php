<?php

use App\Models\Acceso;
use App\Models\Area;
use App\Models\Departamento;
use App\Models\Reporte;
use App\Models\User;

// ---------- Usuarios ----------

test('el administrador crea un usuario', function () {
    $admin = User::factory()->administrador()->create();

    $this->actingAs($admin)->post(route('admin.usuarios.store'), [
        'name' => 'Nuevo Usuario',
        'email' => 'nuevo@plasticoscarmen.com',
        'password' => 'clave-segura-1',
        'rol' => 'usuario',
        'activo' => 1,
    ])->assertRedirect(route('admin.usuarios.index'));

    $this->assertDatabaseHas('users', ['email' => 'nuevo@plasticoscarmen.com', 'rol' => 'usuario', 'activo' => true]);
});

test('el administrador no puede degradarse ni desactivarse a sí mismo', function () {
    $admin = User::factory()->administrador()->create();

    $this->actingAs($admin)->put(route('admin.usuarios.update', $admin), [
        'name' => $admin->name,
        'email' => $admin->email,
        'rol' => 'usuario',
    ])->assertRedirect();

    expect($admin->fresh())->esAdministrador()->toBeTrue()->activo->toBeTrue();

    $this->actingAs($admin)->delete(route('admin.usuarios.destroy', $admin))->assertForbidden();
});

test('el administrador desactiva a otro usuario y este ya no puede entrar', function () {
    $admin = User::factory()->administrador()->create();
    $otro = User::factory()->create(['email' => 'otro@plasticoscarmen.com']);

    $this->actingAs($admin)->put(route('admin.usuarios.update', $otro), [
        'name' => $otro->name,
        'email' => $otro->email,
        'rol' => 'usuario',
        // sin 'activo' => queda desactivado
    ])->assertRedirect();

    expect($otro->fresh()->activo)->toBeFalse();

    $this->post(route('logout'));
    $this->post(route('login'), ['email' => 'otro@plasticoscarmen.com', 'password' => 'password'])
        ->assertSessionHasErrors('email');
});

// ---------- Tarjetas ----------

test('el administrador crea y oculta una tarjeta del menú', function () {
    $admin = User::factory()->administrador()->create();

    $this->actingAs($admin)->post(route('admin.tarjetas.store'), [
        'nombre' => 'Mesa de ayuda',
        'url' => 'https://ayuda.plasticoscarmen.com',
        'tono' => 'rojo',
        'grupo' => 'propio',
        'activo' => 1,
        'nueva_pestana' => 1,
    ])->assertRedirect(route('admin.tarjetas.index'));

    $tarjeta = Acceso::query()->where('nombre', 'Mesa de ayuda')->firstOrFail();
    expect($tarjeta->texto_boton)->toBe('Ingresar');

    $this->actingAs($admin)->get(route('menu'))->assertSee('<h3>Mesa de ayuda</h3>', false);

    $this->actingAs($admin)->put(route('admin.tarjetas.update', $tarjeta), [
        'nombre' => 'Mesa de ayuda',
        'url' => 'https://ayuda.plasticoscarmen.com',
        'tono' => 'rojo',
        'grupo' => 'propio',
        // sin activo
    ])->assertRedirect();

    $this->actingAs($admin)->get(route('menu'))->assertDontSee('<h3>Mesa de ayuda</h3>', false);
});

test('una tarjeta con URL inválida se rechaza', function () {
    $admin = User::factory()->administrador()->create();

    $this->actingAs($admin)->from(route('admin.tarjetas.index'))->post(route('admin.tarjetas.store'), [
        'nombre' => 'Rara',
        'url' => 'javascript:alert(1)',
        'tono' => 'azul',
        'grupo' => 'propio',
    ])->assertSessionHasErrors('url');
});

// ---------- Estructura ----------

test('el administrador crea un departamento y un área con slug único', function () {
    $admin = User::factory()->administrador()->create();

    $this->actingAs($admin)->post(route('admin.departamentos.store'), [
        'nombre' => 'Calidad', 'icono' => 'engranaje', 'orden' => 3,
    ])->assertRedirect();

    $dep = Departamento::query()->where('slug', 'calidad')->firstOrFail();

    $this->actingAs($admin)->post(route('admin.areas.store'), [
        'departamento_id' => $dep->id, 'nombre' => 'Laboratorio', 'icono' => 'cubo',
    ])->assertRedirect();
    $this->actingAs($admin)->post(route('admin.areas.store'), [
        'departamento_id' => $dep->id, 'nombre' => 'Laboratorio ', 'icono' => 'cubo',
    ]);

    expect(Area::query()->where('departamento_id', $dep->id)->pluck('slug')->all())
        ->toContain('laboratorio');
});

test('no se elimina un área con reportes ni un departamento con áreas', function () {
    $admin = User::factory()->administrador()->create();
    $reporte = Reporte::factory()->create();
    $area = $reporte->area;
    $dep = $area->departamento;

    $this->actingAs($admin)->delete(route('admin.areas.destroy', $area))->assertStatus(422);
    $this->actingAs($admin)->delete(route('admin.departamentos.destroy', $dep))->assertStatus(422);

    $reporte->delete();
    $this->actingAs($admin)->delete(route('admin.areas.destroy', $area))->assertRedirect();
    $this->actingAs($admin)->delete(route('admin.departamentos.destroy', $dep))->assertRedirect();

    $this->assertModelMissing($dep);
});

test('la página de estructura se muestra al administrador', function () {
    $admin = User::factory()->administrador()->create();
    $dep = Departamento::factory()->create(['nombre' => 'Producción']);
    Area::factory()->for($dep)->create(['nombre' => 'Bolsas']);

    $this->actingAs($admin)->get(route('admin.estructura.index'))
        ->assertOk()
        ->assertSeeInOrder(['Producción', 'Bolsas']);

    $this->actingAs($admin)->get(route('admin.tarjetas.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.usuarios.index'))->assertOk();
});

// ---------- Avisos ----------

test('el administrador envía un aviso a todos los usuarios activos', function () {
    $admin = User::factory()->administrador()->create();
    User::factory()->count(2)->create();
    User::factory()->inactivo()->create();

    $this->actingAs($admin)->post(route('admin.notificaciones.store'), [
        'titulo' => 'Mantenimiento el sábado',
        'mensaje' => 'El portal estará fuera de servicio de 8 a 10.',
        'tipo' => 'alerta',
    ])->assertRedirect();

    $this->assertDatabaseCount('notificaciones', 3);
});
