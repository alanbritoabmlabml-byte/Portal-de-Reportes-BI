@props(['area', 'anclado' => false, 'compacta' => false])

{{-- Tarjeta de un área de BI: lleva a la página con sus Power BI. --}}
<article {{ $attributes->class(['tarjeta', 'tarjeta--area', 'tarjeta--compacta' => $compacta]) }}
  data-tarjeta data-buscar="{{ Str::lower($area->nombre.' '.$area->descripcion.' '.$area->departamento?->nombre.' power bi reporte') }}"
  data-fav-nombre="{{ $area->nombre }}" data-fav-url="{{ route('bi.area', $area) }}"
  data-tilt>

  <a class="tarjeta-vista tarjeta-vista--area" href="{{ route('bi.area', $area) }}" aria-label="Abrir tableros de {{ $area->nombre }}" tabindex="-1">
    <span class="area-icono" data-fav-icono><x-icono :nombre="$area->icono" :tamano="30"/></span>
    <span class="area-minis" aria-hidden="true">
      <i style="--h:55%"></i><i style="--h:80%"></i><i style="--h:40%"></i><i style="--h:95%"></i><i style="--h:65%"></i>
    </span>
    <span class="tarjeta-etiqueta">Power BI</span>
    <span class="tarjeta-brillo" aria-hidden="true"></span>
  </a>

  <div class="tarjeta-cuerpo">
    <h3>{{ $area->nombre }}</h3>
    @if (! $compacta)
      <p>{{ $area->descripcion ?: 'Tableros de Power BI del área.' }}</p>
    @endif
    <small class="tarjeta-dominio">
      <x-icono nombre="tablero" :tamano="12"/>
      {{ $area->reportes_visibles_count }} {{ Str::plural('tablero', $area->reportes_visibles_count) }}
      @if ($area->departamento) · {{ $area->departamento->nombre }} @endif
    </small>
  </div>

  <div class="tarjeta-acciones">
    <a class="btn btn--primario" href="{{ route('bi.area', $area) }}">Ver tableros <x-icono nombre="flecha" :tamano="14"/></a>
    <x-boton-favorito tipo="area" :id="$area->id" :anclado="$anclado" :texto="! $compacta"/>
  </div>
</article>
