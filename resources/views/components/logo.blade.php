@props([
    // 'monograma' = solo el símbolo pc (barras compactas)
    // 'completo'  = símbolo + nombre + eslogan (pantalla de acceso)
    'variante' => 'monograma',
    'alto' => 34,
])

@php
    $archivo = $variante === 'completo'
        ? 'img/logo-plasticos-carmen.png'
        : 'img/logo-pc.png';

    // Relaciones de aspecto reales de cada recorte: se fija el ancho para que
    // el navegador reserve el espacio y el encabezado no salte al cargar.
    $proporcion = $variante === 'completo' ? 710 / 500 : 456 / 338;
    $ancho = (int) round($alto * $proporcion);
@endphp

<img
    class="logo-marca"
    src="{{ asset($archivo) }}?v={{ filemtime(public_path($archivo)) }}"
    width="{{ $ancho }}"
    height="{{ $alto }}"
    alt="Plásticos Carmen"
    {{ $attributes }}
>
