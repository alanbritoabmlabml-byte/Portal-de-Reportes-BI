@props(['nombre', 'tamano' => 20])

{{--
  Iconos de línea (trazo currentColor). Los de departamentos y áreas son los
  que ofrece Administración → Estructura; el resto son de la interfaz.
--}}
@php
    $trazos = [
        // ---- Departamentos y áreas ----
        'edificio'  => '<path d="M3 21h18M5 21V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v16M15 9h3a1 1 0 0 1 1 1v11M8 8h3M8 12h3M8 16h3"/>',
        'fabrica'   => '<path d="M3 21V10l5 3V10l5 3V10l5 3v8H3zM7 21v-4h3v4M14 21v-4h3v4M17 10V4h3v9"/>',
        'tienda'    => '<path d="M4 10 5 4h14l1 6M4 10a2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0M5 12v9h14v-9M10 21v-6h4v6"/>',
        'almacen'   => '<path d="M3 21V9l9-5 9 5v12M7 21v-7h10v7M7 17h10M10 14v7"/>',
        'chip'      => '<rect x="7" y="7" width="10" height="10" rx="1.5"/><path d="M10 10h4v4h-4zM9 3v4M15 3v4M9 17v4M15 17v4M3 9h4M3 15h4M17 9h4M17 15h4"/>',
        'brujula'   => '<circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5z"/>',
        'maletin'   => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 12h18"/>',
        'personas'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M21.5 20a6.5 6.5 0 0 0-4.5-6.2"/>',
        'moneda'    => '<circle cx="12" cy="12" r="9"/><path d="M14.5 9.5c-.4-1-1.4-1.5-2.5-1.5-1.5 0-2.5.8-2.5 2 0 2.6 5 1.4 5 4 0 1.2-1 2-2.5 2-1.1 0-2.1-.5-2.5-1.5M12 6v2M12 16v2"/>',
        'bolsa'     => '<path d="M6 8h12l1 13H5zM9 8V6a3 3 0 0 1 6 0v2"/>',
        'vaso'      => '<path d="M6 4h12l-1.5 16h-9zM7 9h10"/>',
        'cubo'      => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9zM4 7.5l8 4.5 8-4.5M12 12v9"/>',
        'engranaje' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1 7 17M17 7l2.1-2.1"/>',
        'rollo'     => '<ellipse cx="7" cy="12" rx="3" ry="8"/><path d="M7 4h10c1.7 0 3 3.6 3 8s-1.3 8-3 8H7M7 12h.01"/>',
        'grafico'   => '<path d="M3 3v18h18M7 15l4-5 4 3 5-7"/>',
        'megafono'  => '<path d="M3 11v2a1 1 0 0 0 1 1h2l4 4V6L6 10H4a1 1 0 0 0-1 1zM14 9.5a3 3 0 0 1 0 5M17 7a7 7 0 0 1 0 10"/>',
        'camion'    => '<path d="M3 6h11v10H3zM14 9h4l3 3v4h-7"/><circle cx="7" cy="17.5" r="1.5"/><circle cx="17" cy="17.5" r="1.5"/>',
        'cajas'     => '<path d="M3 13h8v8H3zM13 13h8v8h-8zM8 3h8v8H8zM7 13v8M17 13v8M12 3v8"/>',
        'pellets'   => '<circle cx="7" cy="8" r="2.5"/><circle cx="15" cy="6" r="2.5"/><circle cx="17" cy="14" r="2.5"/><circle cx="8" cy="16" r="2.5"/><circle cx="12.5" cy="11" r="1.5"/>',

        // ---- Interfaz ----
        'campana'   => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10 21a2 2 0 0 0 4 0"/>',
        'pin'       => '<path d="M9 4h6l-1 6 3 3v1H7v-1l3-3zM12 14v7"/>',
        'pin-lleno' => '<path fill="currentColor" d="M9 4h6l-1 6 3 3v1H7v-1l3-3zM12 14v7"/>',
        'buscar'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'salir'     => '<path d="M10 17H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h5M14 16l4-4-4-4M18 12H9"/>',
        'ajustes'   => '<path d="M4 6h16M4 12h16M4 18h16"/><circle cx="9" cy="6" r="2" fill="#fff"/><circle cx="15" cy="12" r="2" fill="#fff"/><circle cx="8" cy="18" r="2" fill="#fff"/>',
        'flecha'    => '<path d="m9 6 6 6-6 6"/>',
        'abajo'     => '<path d="m6 9 6 6 6-6"/>',
        'externo'   => '<path d="M14 4h6v6M20 4l-9 9M18 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6"/>',
        'expandir'  => '<path d="M4 9V4h5M15 4h5v5M20 15v5h-5M9 20H4v-5"/>',
        'cerrar'    => '<path d="M6 6l12 12M18 6 6 18"/>',
        'check'     => '<path d="m5 12 5 5L20 7"/>',
        'info'      => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'alerta'    => '<path d="M12 3 2 21h20zM12 10v5M12 18h.01"/>',
        'casa'      => '<path d="m3 11 9-7 9 7v9a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z"/>',
        'usuario'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'tablero'   => '<rect x="3" y="3" width="8" height="10" rx="1.5"/><rect x="13" y="3" width="8" height="6" rx="1.5"/><rect x="13" y="11" width="8" height="10" rx="1.5"/><rect x="3" y="15" width="8" height="6" rx="1.5"/>',
        'enlace'    => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1.5 1.5M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1.5-1.5"/>',
        'refrescar' => '<path d="M20 12a8 8 0 1 1-2.3-5.7M20 4v5h-5"/>',
        'ojo'       => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'sol'       => '<circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8"/>',
        'luna'      => '<path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5z"/>',
        'subir'     => '<path d="M12 19V5M6 11l6-6 6 6"/>',
        'bajar'     => '<path d="M12 5v14M6 13l6 6 6-6"/>',
        'descargar' => '<path d="M12 3v12M7 11l5 5 5-5M4 20h16"/>',
        'reloj'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5.2l3.3 2"/>',
        'historial' => '<path d="M3 12a9 9 0 1 0 3-6.7M3 4v4.5h4.5M12 7.5V12l3 2"/>',
        'huella'    => '<path d="M12 4a6 6 0 0 0-6 6v3M12 4a6 6 0 0 1 6 6v6M9 10a3 3 0 0 1 6 0v6M12 10v8M6.5 18.5A9 9 0 0 0 7 20"/>',
        'excel'     => '<path d="M14 3H6a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8z"/><path d="M14 3v5h5M9.5 12l5 5M14.5 12l-5 5"/>',
        'impresora' => '<path d="M7 9V3h10v6M7 19H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 15h10v6H7z"/>',
        'estrella'  => '<path d="m12 3 2.8 5.8 6.2.9-4.5 4.4 1.1 6.3L12 17.4l-5.6 3 1.1-6.3L3 9.7l6.2-.9z"/>',
    ];
    $d = $trazos[$nombre] ?? $trazos['grafico'];
@endphp
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="{{ $tamano }}" height="{{ $tamano }}"
     fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
     aria-hidden="true" focusable="false" {{ $attributes->merge(['class' => 'icono']) }}>{!! $d !!}</svg>
