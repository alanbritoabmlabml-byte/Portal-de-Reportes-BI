@props(['acceso', 'anclado' => false, 'compacta' => false])

{{--
  Tarjeta de acceso a otro sitio. La destacada (portafolio) lleva una franja
  superior «Acceder al portafolio» y ocupa el doble de ancho en escritorio.
--}}
<article {{ $attributes->class([
    'tarjeta', "tarjeta--{$acceso->tono}",
    'tarjeta--destacada' => $acceso->destacado && ! $compacta,
    'tarjeta--compacta' => $compacta,
  ]) }}
  data-tarjeta data-buscar="{{ Str::lower($acceso->nombre.' '.$acceso->descripcion.' '.$acceso->etiqueta.' '.$acceso->dominio()) }}"
  data-fav-nombre="{{ $acceso->nombre }}" data-fav-url="{{ $acceso->url }}"
  @if($acceso->nueva_pestana) data-fav-nueva @endif
  data-tilt>

  @if ($acceso->destacado && ! $compacta)
    <a class="tarjeta-franja" href="{{ $acceso->url }}" @if($acceso->nueva_pestana) target="_blank" rel="noopener" @endif>
      <x-icono nombre="externo" :tamano="15"/>
      Acceder al portafolio
    </a>
  @endif

  <a class="tarjeta-vista" href="{{ $acceso->url }}" @if($acceso->nueva_pestana) target="_blank" rel="noopener" @endif
     aria-label="Abrir {{ $acceso->nombre }}" tabindex="-1">
    <x-vista-previa :acceso="$acceso"/>
    @if ($acceso->etiqueta)
      <span class="tarjeta-etiqueta">{{ $acceso->etiqueta }}</span>
    @endif
    <span class="tarjeta-brillo" aria-hidden="true"></span>
  </a>

  <div class="tarjeta-cuerpo">
    <h3>{{ $acceso->nombre }}</h3>
    @if ($acceso->descripcion && ! $compacta)
      <p>{{ $acceso->descripcion }}</p>
    @endif
    <small class="tarjeta-dominio"><x-icono nombre="enlace" :tamano="12"/> {{ $acceso->dominio() }}</small>
  </div>

  <div class="tarjeta-acciones">
    <a class="btn btn--primario" href="{{ $acceso->url }}" @if($acceso->nueva_pestana) target="_blank" rel="noopener" @endif>
      {{ $acceso->texto_boton }}
      <x-icono nombre="flecha" :tamano="14"/>
    </a>
    <x-boton-favorito tipo="acceso" :id="$acceso->id" :anclado="$anclado" :texto="! $compacta"/>
  </div>
</article>
