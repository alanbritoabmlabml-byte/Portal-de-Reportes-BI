@php
    $v = fn (string $campo, $porDefecto = '') => $activa ? old($campo, $acceso?->$campo ?? $porDefecto) : ($acceso?->$campo ?? $porDefecto);
    $marcado = fn (string $campo, bool $porDefecto) => $activa ? (bool) old($campo, $acceso ? $acceso->$campo : $porDefecto) : ($acceso ? $acceso->$campo : $porDefecto);
    $tonoActual = $v('tono', 'azul');
    $grupoActual = $v('grupo', \App\Enums\GrupoAcceso::Propio->value);
    $grupoActual = $grupoActual instanceof \App\Enums\GrupoAcceso ? $grupoActual->value : $grupoActual;
@endphp

<div class="form-rejilla">
  <div class="campo @if($activa) @error('nombre') campo--error @enderror @endif">
    <label for="nombre-{{ $sufijo }}">Nombre</label>
    <input id="nombre-{{ $sufijo }}" name="nombre" type="text" value="{{ $v('nombre') }}" maxlength="80" required>
    @if ($activa) @error('nombre')<span class="error">{{ $message }}</span>@enderror @endif
  </div>

  <div class="campo @if($activa) @error('url') campo--error @enderror @endif">
    <label for="url-{{ $sufijo }}">URL</label>
    <input id="url-{{ $sufijo }}" name="url" type="text" value="{{ $v('url') }}" maxlength="500" required placeholder="https://…">
    @if ($activa) @error('url')<span class="error">{{ $message }}</span>@enderror @endif
  </div>

  <div class="campo ancho-total">
    <label for="descripcion-{{ $sufijo }}">Descripción</label>
    <input id="descripcion-{{ $sufijo }}" name="descripcion" type="text" value="{{ $v('descripcion') }}" maxlength="200">
  </div>

  <div class="campo">
    <label for="texto-{{ $sufijo }}">Texto del botón</label>
    <input id="texto-{{ $sufijo }}" name="texto_boton" type="text" value="{{ $v('texto_boton', 'Ingresar') }}" maxlength="40">
  </div>

  <div class="campo">
    <label for="etiqueta-{{ $sufijo }}">Etiqueta</label>
    <input id="etiqueta-{{ $sufijo }}" name="etiqueta" type="text" value="{{ $v('etiqueta') }}" maxlength="40" placeholder="Interno, Público, Microsoft 365…">
  </div>

  <div class="campo @if($activa) @error('imagen') campo--error @enderror @endif">
    <label for="imagen-{{ $sufijo }}">Imagen de vista previa</label>
    <input id="imagen-{{ $sufijo }}" name="imagen" type="text" value="{{ $v('imagen') }}" maxlength="300" placeholder="img/tarjetas/mi-sitio.svg o https://…/captura.png">
    <span class="campo-ayuda">Ruta dentro de <code>public/</code> o URL absoluta. Vacío: se muestra la inicial sobre el tono.</span>
  </div>

  <div class="campo">
    <label for="tono-{{ $sufijo }}">Tono</label>
    <select id="tono-{{ $sufijo }}" name="tono">
      @foreach ($tonos as $tono)
        <option value="{{ $tono }}" @selected($tonoActual === $tono)>{{ ucfirst($tono) }}</option>
      @endforeach
    </select>
  </div>

  <div class="campo">
    <label for="grupo-{{ $sufijo }}">Mosaico</label>
    <select id="grupo-{{ $sufijo }}" name="grupo">
      @foreach (\App\Enums\GrupoAcceso::opciones() as $valor => $etiqueta)
        <option value="{{ $valor }}" @selected($grupoActual === $valor)>{{ $etiqueta }}</option>
      @endforeach
    </select>
    <span class="campo-ayuda">En qué rejilla del menú aparece la tarjeta.</span>
  </div>

  <div class="casillas ancho-total">
    <label class="casilla"><input type="checkbox" name="nueva_pestana" value="1" @checked($marcado('nueva_pestana', true))> Abrir en pestaña nueva</label>
    <label class="casilla"><input type="checkbox" name="destacado" value="1" @checked($marcado('destacado', false))> Destacada (doble ancho, franja «Acceder al portafolio»)</label>
    <label class="casilla"><input type="checkbox" name="activo" value="1" @checked($marcado('activo', true))> Visible en el menú</label>
  </div>
</div>
