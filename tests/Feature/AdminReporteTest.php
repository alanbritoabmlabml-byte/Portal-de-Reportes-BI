<?php

use App\Models\Area;
use App\Models\Notificacion;
use App\Models\Reporte;
use App\Models\User;

test('la administración está cerrada a los usuarios sin rol administrador', function () {
    $usuario = User::factory()->create();

    $this->actingAs($usuario)->get(route('admin.reportes.index'))->assertForbidden();
    $this->actingAs($usuario)->post(route('admin.reportes.store'), [])->assertForbidden();
});

test('el administrador ve el listado de reportes', function () {
    $admin = User::factory()->administrador()->create();
    Reporte::factory()->create(['titulo' => 'Ventas del mes']);

    $this->actingAs($admin)->get(route('admin.reportes.index'))
        ->assertOk()
        ->assertSee('Ventas del mes')
        ->assertSee('Accesos');
});

test('el administrador crea un reporte pegando el código iframe de Power BI', function () {
    $admin = User::factory()->administrador()->create();
    $area = Area::factory()->create();

    $this->actingAs($admin)->post(route('admin.reportes.store'), [
        'area_id' => $area->id,
        'titulo' => 'Producción diaria',
        'tipo' => 'operativo',
        'url_iframe' => '<iframe title="x" width="1140" height="541" src="https://app.powerbi.com/reportEmbed?reportId=123&amp;autoAuth=true" frameborder="0" allowFullScreen="true"></iframe>',
        'activo' => 1,
    ])->assertRedirect(route('admin.reportes.index', ['area' => $area->id]));

    $this->assertDatabaseHas('reportes', [
        'titulo' => 'Producción diaria',
        'url_iframe' => 'https://app.powerbi.com/reportEmbed?reportId=123&autoAuth=true',
        'publico' => false,
    ]);
});

test('una URL que no es https se rechaza', function () {
    $admin = User::factory()->administrador()->create();
    $area = Area::factory()->create();

    $this->actingAs($admin)->from(route('admin.reportes.index'))->post(route('admin.reportes.store'), [
        'area_id' => $area->id,
        'titulo' => 'Malo',
        'tipo' => 'control',
        'url_iframe' => 'javascript:alert(1)',
    ])->assertRedirect(route('admin.reportes.index'))
        ->assertSessionHasErrors('url_iframe');
});

test('el administrador edita el link del iframe', function () {
    $admin = User::factory()->administrador()->create();
    $reporte = Reporte::factory()->create(['url_iframe' => 'demo']);

    $this->actingAs($admin)->put(route('admin.reportes.update', $reporte), [
        'area_id' => $reporte->area_id,
        'titulo' => $reporte->titulo,
        'tipo' => $reporte->tipo->value,
        'url_iframe' => 'https://app.powerbi.com/view?r=nuevo',
        'activo' => 1,
        'publico' => 1,
    ])->assertRedirect();

    expect($reporte->fresh())
        ->url_iframe->toBe('https://app.powerbi.com/view?r=nuevo')
        ->publico->toBeTrue();
});

test('el administrador define quién ve un reporte y los nuevos reciben notificación', function () {
    $admin = User::factory()->administrador()->create();
    $reporte = Reporte::factory()->create();
    [$ana, $beto, $carla] = User::factory()->count(3)->create();
    $reporte->usuarios()->attach($ana);

    $this->actingAs($admin)
        ->put(route('admin.reportes.accesos', $reporte), ['usuarios' => [$ana->id, $beto->id]])
        ->assertRedirect();

    expect($reporte->usuarios()->pluck('users.id')->sort()->values()->all())
        ->toBe(collect([$ana->id, $beto->id])->sort()->values()->all());

    // Solo Beto ganó acceso: solo él recibe aviso
    expect(Notificacion::query()->where('user_id', $beto->id)->count())->toBe(1);
    expect(Notificacion::query()->where('user_id', $ana->id)->count())->toBe(0);
    expect(Notificacion::query()->where('user_id', $carla->id)->count())->toBe(0);

    $this->actingAs($beto)->get(route('bi.area', $reporte->area))->assertOk();
    $this->actingAs($carla)->get(route('bi.area', $reporte->area))->assertForbidden();
});

test('publicar un reporte público avisa a todos los usuarios activos', function () {
    $admin = User::factory()->administrador()->create();
    User::factory()->count(2)->create();
    User::factory()->inactivo()->create();
    $area = Area::factory()->create();

    $this->actingAs($admin)->post(route('admin.reportes.store'), [
        'area_id' => $area->id,
        'titulo' => 'Para todos',
        'tipo' => 'gerencial',
        'url_iframe' => 'demo',
        'activo' => 1,
        'publico' => 1,
    ]);

    // admin + 2 activos = 3; el inactivo no
    expect(Notificacion::query()->where('titulo', 'Nuevo reporte: Para todos')->count())->toBe(3);
});

test('el administrador elimina un reporte', function () {
    $admin = User::factory()->administrador()->create();
    $reporte = Reporte::factory()->create();

    $this->actingAs($admin)->delete(route('admin.reportes.destroy', $reporte))->assertRedirect();

    $this->assertModelMissing($reporte);
});
