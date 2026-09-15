@php
    $v = fn (string $campo) => $activa ? old($campo, $usuario?->$campo) : $usuario?->$campo;
    $rolActual = $activa ? old('rol', $usuario?->rol?->value) : $usuario?->rol?->value;
    $activoActual = $activa ? (bool) old('activo', $usuario ? $usuario->activo : true) : ($usuario ? $usuario->activo : true);
    $esYo = $usuario && $usuario->is(auth()->user());
@endphp

<div class="form-rejilla">
  <div class="campo @if($activa) @error('name') campo--error @enderror @endif">
    <label for="name-{{ $sufijo }}">Nombre y apellido</label>
    <input id="name-{{ $sufijo }}" name="name" type="text" value="{{ $v('name') }}" maxlength="100" required>
    @if ($activa) @error('name')<span class="error">{{ $message }}</span>@enderror @endif
  </div>

  <div class="campo @if($activa) @error('email') campo--error @enderror @endif">
    <label for="email-{{ $sufijo }}">Correo</label>
    <input id="email-{{ $sufijo }}" name="email" type="email" value="{{ $v('email') }}" maxlength="150" required>
    @if ($activa) @error('email')<span class="error">{{ $message }}</span>@enderror @endif
  </div>

  <div class="campo @if($activa) @error('password') campo--error @enderror @endif">
    <label for="password-{{ $sufijo }}">{{ $usuario ? 'Nueva contraseña' : 'Contraseña' }}</label>
    <input id="password-{{ $sufijo }}" name="password" type="password" autocomplete="new-password"
           minlength="8" @if(! $usuario) required @endif
           placeholder="{{ $usuario ? 'Dejar vacío para no cambiarla' : 'Mínimo 8 caracteres' }}">
    @if ($activa) @error('password')<span class="error">{{ $message }}</span>@enderror @endif
  </div>

  <div class="campo @if($activa) @error('rol') campo--error @enderror @endif">
    <label for="rol-{{ $sufijo }}">Rol</label>
    <select id="rol-{{ $sufijo }}" name="rol" required @disabled($esYo)>
      @foreach ($roles as $valor => $etiqueta)
        <option value="{{ $valor }}" @selected(($rolActual ?? 'usuario') === $valor)>{{ $etiqueta }}</option>
      @endforeach
    </select>
    @if ($esYo)<input type="hidden" name="rol" value="administrador"><span class="campo-ayuda">No puedes cambiar tu propio rol.</span>@endif
    @if ($activa) @error('rol')<span class="error">{{ $message }}</span>@enderror @endif
  </div>

  <div class="casillas ancho-total">
    <label class="casilla">
      <input type="checkbox" name="activo" value="1" @checked($activoActual) @disabled($esYo)>
      Activo (puede iniciar sesión)
    </label>
  </div>
</div>
