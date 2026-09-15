{{--
  Campos del reporte, compartidos por el alta y la edición.
  $reporte: null en el alta. $activa: si este formulario es el que falló la
  validación (solo entonces se leen old() y se pintan errores). $sufijo: para
  que los id de los campos no se repitan en la página.
--}}
@php
    $v = fn (string $campo, $porDefecto = '') => $activa ? old($campo, $reporte?->$campo ?? $porDefecto) : ($reporte?->$campo ?? $porDefecto);
    $tipoActual = $activa ? old('tipo', $reporte?->tipo?->value) : $reporte?->tipo?->value;
    $areaActual = (int) ($activa ? old('area_id', $reporte?->area_id) : $reporte?->area_id);
    $marcado = fn (string $campo, bool $porDefecto) => $activa ? (bool) old($campo, $reporte ? $reporte->$campo : $porDefecto) : ($reporte ? $reporte->$campo : $porDefecto);
@endphp

<div class="form-rejilla">
  <div class="campo @if($activa) @error('titulo') campo--error @enderror @endif">
    <label for="titulo-{{ $sufijo }}">Título</label>
    <input id="titulo-{{ $sufijo }}" name="titulo" type="text" value="{{ $v('titulo') }}" maxlength="120" required>
    @if ($activa) @error('titulo')<span class="error">{{ $message }}</span>@enderror @endif
  </div>

  <div class="campo @if($activa) @error('area_id') campo--error @enderror @endif">
    <label for="area-{{ $sufijo }}">Área</label>
    <select id="area-{{ $sufijo }}" name="area_id" required>
      <option value="">Elige un área…</option>
      @foreach ($departamentos as $d)
        <optgroup label="{{ $d->nombre }}">
          @foreach ($d->areas as $a)
            <option value="{{ $a->id }}" @selected($areaActual === $a->id)>{{ $a->nombre }}</option>
          @endforeach
        </optgroup>
      @endforeach
    </select>
    @if ($activa) @error('area_id')<span class="error">{{ $message }}</span>@enderror @endif
  </div>

  <div class="campo @if($activa) @error('tipo') campo--error @enderror @endif">
    <label for="tipo-{{ $sufijo }}">Tipo de reporte</label>
    <select id="tipo-{{ $sufijo }}" name="tipo" required>
      <option value="">Elige…</option>
      @foreach ($tipos as $valor => $etiqueta)
        <option value="{{ $valor }}" @selected($tipoActual === $valor)>{{ $etiqueta }}</option>
      @endforeach
    </select>
    @if ($activa) @error('tipo')<span class="error">{{ $message }}</span>@enderror @endif
  </div>


  <div class="campo ancho-total @if($activa) @error('url_iframe') campo--error @enderror @endif">
    <label for="url-{{ $sufijo }}">URL del iframe de Power BI</label>
    <textarea id="url-{{ $sufijo }}" name="url_iframe" rows="2" required
              placeholder="https://app.powerbi.com/reportEmbed?reportId=…  — o pega aquí el código <iframe> completo">{{ $v('url_iframe') }}</textarea>
    <span class="campo-ayuda">En Power BI: Archivo → Insertar informe → Sitio web o portal. Escribe <b>demo</b> para mostrar el tablero de muestra.</span>
    @if ($activa) @error('url_iframe')<span class="error">{{ $message }}</span>@enderror @endif
  </div>

  <div class="campo ancho-total">
    <label for="descripcion-{{ $sufijo }}">Descripción</label>
    <input id="descripcion-{{ $sufijo }}" name="descripcion" type="text" value="{{ $v('descripcion') }}" maxlength="300" placeholder="Qué muestra este tablero, en una línea">
  </div>

  <div class="casillas ancho-total">
    <label class="casilla">
      <input type="checkbox" name="publico" value="1" @checked($marcado('publico', false))>
      Público: lo ve cualquier usuario con sesión
    </label>
    <label class="casilla">
      <input type="checkbox" name="activo" value="1" @checked($marcado('activo', true))>
      Activo
    </label>
  </div>
</div>
