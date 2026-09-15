<!DOCTYPE html>
@php
    // Preferencia guardada del usuario; un visitante sin sesión usa la del navegador.
    $temaPreferido = auth()->user()?->tema?->value ?? 'sistema';
@endphp
<html lang="es" data-tema-pref="{{ $temaPreferido }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('titulo') · Reportes BI · Plásticos Carmen</title>

{{-- Se resuelve el tema ANTES de pintar para que no haya parpadeo blanco.
     El usuario con sesión manda; si eligió «sistema» decide el navegador. --}}
<script>
(function () {
  var raiz = document.documentElement;
  var pref = raiz.dataset.temaPref || 'sistema';
  try {
    if (pref === 'sistema' && !{{ auth()->check() ? 'true' : 'false' }}) {
      pref = localStorage.getItem('pc-tema') || 'sistema';
      raiz.dataset.temaPref = pref;
    }
  } catch (e) {}
  var oscuro = pref === 'oscuro' ||
    (pref === 'sistema' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
  raiz.dataset.tema = oscuro ? 'oscuro' : 'claro';
})();
</script>

{{-- Favicons generados desde el monograma del logotipo (mismos que el portafolio) --}}
<link rel="icon" href="{{ asset('favicon.ico') }}?v={{ filemtime(public_path('favicon.ico')) }}" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon-16x16.png') }}">
<link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
<meta name="theme-color" content="#063381">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
{{-- marca.css trae la paleta base compartida con el portafolio y tema.css la
     ajusta a la v2 (fondos neutros, azul de acento y modo noche): por eso va
     SIEMPRE al final, después de las hojas de cada página. --}}
@foreach (array_merge(['marca.css', 'base.css'], $hojas ?? [], ['tema.css']) as $hoja)
<link rel="stylesheet" href="{{ asset("css/{$hoja}") }}?v={{ filemtime(public_path("css/{$hoja}")) }}">
@endforeach
</head>
<body class="@yield('clase-body')">
@yield('contenido')

<footer class="pie-marca">
  Copyright © {{ date('Y') }} Plásticos Carmen. Todos los derechos reservados.
  Desarrollado por Departamento de IT
  <span class="version">versión {{ config('app.version') }}</span>
</footer>

{{-- Avisos flotantes (toasts): los llena base.js con los mensajes de sesión
     y con las respuestas de favoritos y notificaciones --}}
<div class="toasts" id="toasts" aria-live="polite" aria-atomic="false"></div>

@if (session('aviso'))
  <script>window.__aviso = @json(session('aviso'));</script>
@endif
@if ($errors->any() && ! View::hasSection('sin-toast-errores'))
  <script>window.__avisoError = @json($errors->first());</script>
@endif

<script src="{{ asset('js/base.js') }}?v={{ filemtime(public_path('js/base.js')) }}"></script>
@auth
<script src="{{ asset('js/trazabilidad.js') }}?v={{ filemtime(public_path('js/trazabilidad.js')) }}"></script>
@endauth
@stack('scripts')
</body>
</html>
