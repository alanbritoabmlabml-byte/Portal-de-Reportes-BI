<?php

use App\Models\User;

test('la pantalla de acceso se muestra a los visitantes', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Portafolio de Reportes BI')
        ->assertSee('Accede con tus credenciales corporativas');
});

test('el menú exige sesión y redirige al acceso', function () {
    $this->get(route('menu'))->assertRedirect(route('login'));
});

test('un usuario activo entra y llega al menú', function () {
    $usuario = User::factory()->create(['email' => 'ana@plasticoscarmen.com']);

    $this->post(route('login'), ['email' => 'ana@plasticoscarmen.com', 'password' => 'password'])
        ->assertRedirect(route('menu'));

    $this->assertAuthenticatedAs($usuario);
});

test('una clave incorrecta no abre sesión', function () {
    User::factory()->create(['email' => 'ana@plasticoscarmen.com']);

    $this->from(route('login'))
        ->post(route('login'), ['email' => 'ana@plasticoscarmen.com', 'password' => 'otra'])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('un usuario desactivado no puede entrar aunque su clave sea correcta', function () {
    User::factory()->inactivo()->create(['email' => 'baja@plasticoscarmen.com']);

    $this->post(route('login'), ['email' => 'baja@plasticoscarmen.com', 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('con sesión iniciada el acceso redirige al menú', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('login'))
        ->assertRedirect(route('menu'));
});

test('cerrar sesión vuelve al acceso', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('el acceso se limita a cinco intentos por minuto', function () {
    User::factory()->create(['email' => 'ana@plasticoscarmen.com']);

    foreach (range(1, 5) as $i) {
        $this->post(route('login'), ['email' => 'ana@plasticoscarmen.com', 'password' => 'mal']);
    }

    $this->post(route('login'), ['email' => 'ana@plasticoscarmen.com', 'password' => 'mal'])
        ->assertStatus(429);
});
