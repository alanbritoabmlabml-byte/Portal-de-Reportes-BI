@extends('layouts.app', ['hojas' => ['auth.css']])

@section('titulo', 'Acceso')
@section('sin-toast-errores', '1')

@section('contenido')
<main class="acceso">
  <div class="acceso-escena" aria-hidden="true">
    <span class="burbuja burbuja--1"></span>
    <span class="burbuja burbuja--2"></span>
    <span class="burbuja burbuja--3"></span>
  </div>

  <div class="acceso-tarjeta">
    <div class="acceso-cabecera">
      {{-- El logotipo completo ya trae el nombre, así que no se repite en texto --}}
      <x-logo variante="completo" :alto="96"/>
      <h1>Portafolio de Reportes BI</h1>
      <p>Accede con tus credenciales corporativas</p>
    </div>

    <div class="acceso-cuerpo">
      @if ($errors->any())
        <div class="alerta alerta--error" role="alert">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="campo @error('email') campo--error @enderror">
          <label for="email">Correo</label>
          <input id="email" name="email" type="email" value="{{ old('email') }}"
                 required autofocus autocomplete="username" placeholder="nombre@plasticoscarmen.com">
        </div>

        <div class="campo @error('password') campo--error @enderror">
          <label for="password">Contraseña</label>
          <div class="campo-clave">
            <input id="password" name="password" type="password"
                   required autocomplete="current-password">
            <button type="button" class="ver-clave" data-ver-clave aria-label="Mostrar contraseña" aria-pressed="false">
              <x-icono nombre="ojo" :tamano="16"/>
            </button>
          </div>
        </div>

        <label class="recordarme">
          <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
          Mantener la sesión iniciada
        </label>

        <button type="submit" class="btn btn--primario">Entrar <x-icono nombre="flecha" :tamano="15"/></button>
      </form>

      <p class="acceso-pie">
        ¿Sin acceso? Pídeselo a un administrador del área de Sistemas.
      </p>
    </div>
  </div>
</main>
@endsection

@push('scripts')
<script>
  // Mostrar / ocultar la contraseña
  document.querySelectorAll('[data-ver-clave]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = btn.parentElement.querySelector('input');
      var visible = input.type === 'text';
      input.type = visible ? 'password' : 'text';
      btn.setAttribute('aria-pressed', String(!visible));
      btn.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });
  });
</script>
@endpush
