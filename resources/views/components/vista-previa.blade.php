@props(['acceso'])

{{--
  Imagen de la tarjeta. Si es un SVG propio (bajo public/) se incrusta en la
  página: así el CSS de la tarjeta puede animarlo al pasar el puntero. Una
  imagen externa o de mapa de bits se muestra con <img>. Sin imagen se pinta
  un fondo de marca con la inicial.
--}}
@php
    $ruta = $acceso->imagen;
    $esSvgLocal = $ruta && ! Str::startsWith($ruta, ['http://', 'https://', '//'])
        && Str::endsWith($ruta, '.svg') && is_file(public_path($ruta));
@endphp

@if ($esSvgLocal)
  <div class="vista vista--svg" aria-hidden="true">{!! file_get_contents(public_path($ruta)) !!}</div>
@elseif ($acceso->urlImagen())
  <img class="vista vista--img" src="{{ $acceso->urlImagen() }}" alt="" loading="lazy">
@else
  <div class="vista vista--inicial" aria-hidden="true"><span>{{ mb_strtoupper(mb_substr($acceso->nombre, 0, 1)) }}</span></div>
@endif
