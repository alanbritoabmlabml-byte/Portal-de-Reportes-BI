@php
    $v = fn (string $campo, $porDefecto = '') => $activa ? old($campo, $modelo?->$campo ?? $porDefecto) : ($modelo?->$campo ?? $porDefecto);
    $iconoActual = $v('icono', $conDepartamento ? 'grafico' : 'edificio');
    $depGuardado = $conDepartamento ? $modelo?->departamento_id : null;
    $depActual = (int) ($activa ? old('departamento_id', $depGuardado) : $depGuardado);
@endphp

<div class="form-rejilla">
  @if ($conDepartamento)
    <div class="campo @if($activa) @error('departamento_id') campo--error @enderror @endif">
      <label for="dep-{{ $sufijo }}">Departamento</label>
      <select id="dep-{{ $sufijo }}" name="departamento_id" required>
        <option value="">Elige…</option>
        @foreach ($departamentos as $d)
          <option value="{{ $d->id }}" @selected($depActual === $d->id)>{{ $d->nombre }}</option>
        @endforeach
      </select>
      @if ($activa) @error('departamento_id')<span class="error">{{ $message }}</span>@enderror @endif
    </div>
  @endif

  <div class="campo @if($activa) @error('nombre') campo--error @enderror @endif">
    <label for="nombre-{{ $sufijo }}">Nombre</label>
    <input id="nombre-{{ $sufijo }}" name="nombre" type="text" value="{{ $v('nombre') }}" maxlength="80" required>
    @if ($activa) @error('nombre')<span class="error">{{ $message }}</span>@enderror @endif
  </div>

  <div class="campo ancho-total">
    <label for="descripcion-{{ $sufijo }}">Descripción</label>
    <input id="descripcion-{{ $sufijo }}" name="descripcion" type="text" value="{{ $v('descripcion') }}" maxlength="200">
  </div>

  <div class="campo">
    <label for="icono-{{ $sufijo }}">Icono</label>
    <div class="iconos-radio" role="radiogroup" aria-label="Icono">
      @foreach ($iconos as $icono)
        <label class="icono-opcion" title="{{ $icono }}">
          <input type="radio" name="icono" value="{{ $icono }}" @checked($iconoActual === $icono)>
          <span><x-icono :nombre="$icono" :tamano="18"/></span>
        </label>
      @endforeach
    </div>
  </div>

</div>
