@extends('layouts.app', ['hojas' => ['menu.css']])

@section('titulo', 'Menú principal')
@section('clase-body', 'pagina-menu')

@php
    $hora = (int) now()->format('G');
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    $sinLeer = $usuario->notificacionesSinLeer()->count();
    $areasPlanas = $departamentos->flatMap->areas;
@endphp

@section('contenido')
<x-barra-sup encabezado="Portafolio de Reportes BI" bajada="Accesos, tableros y sitios de Plásticos Carmen"/>

<main class="contenido contenido--ancha menu">

  {{-- ---------- Saludo + resumen ---------- --}}
  <section class="portada">
    <div class="portada-texto">
      <p class="portada-fecha">{{ now()->translatedFormat('l, j \d\e F \d\e Y') }}</p>
      <h2>{{ $saludo }}, <span>{{ $usuario->nombreCorto() }}</span></h2>
      <p class="portada-bajada">Este es tu punto de partida: entra al portafolio, revisa los tableros de tu área o salta a los sitios de la empresa.</p>
    </div>
    <div class="portada-cifras" role="list">
      <a role="listitem" class="cifra" href="#tableros">
        <b>{{ $totalTableros }}</b><span>{{ Str::plural('tablero', $totalTableros) }} BI</span>
      </a>
      <a role="listitem" class="cifra" href="#accesos">
        <b>{{ $accesos->count() }}</b><span>{{ Str::plural('acceso', $accesos->count()) }}</span>
      </a>
      <a role="listitem" class="cifra" href="#favoritos">
        <b>{{ $favoritos->count() }}</b><span>{{ Str::plural('favorito', $favoritos->count()) }}</span>
      </a>
      <a role="listitem" class="cifra @if($sinLeer) cifra--alerta @endif" href="{{ route('notificaciones.index') }}">
        <b data-contador-notificaciones-texto>{{ $sinLeer }}</b><span>sin leer</span>
      </a>
    </div>
  </section>

  <div class="menu-grid">
    <div class="menu-col">

      {{-- ---------- Buscador ---------- --}}
      <div class="buscador" role="search">
        <x-icono nombre="buscar" :tamano="19"/>
        <input type="search" id="buscar-tarjetas" placeholder="Buscar un acceso, un área o un tablero…" autocomplete="off" aria-label="Buscar en el menú">
        <span class="buscador-atajo" aria-hidden="true">/</span>
      </div>
      <p class="sin-resultados" id="sin-resultados" hidden>Nada coincide con la búsqueda.</p>

      {{-- ---------- Accesos en mosaicos ---------- --}}
      <section class="seccion" id="accesos">
        <header class="seccion-cabecera">
          <h2><x-icono nombre="enlace" :tamano="18"/> Accesos</h2>
          <p>Agrupados por tipo: lo que construye Sistemas, los SharePoint, los sitios públicos y Microsoft 365.</p>
        </header>

        @foreach ($mosaicos as $mosaico)
          @php $grupo = $mosaico['grupo']; @endphp
          <details class="mosaico mosaico--{{ $grupo->value }}" open data-mosaico="{{ $grupo->value }}">
            <summary>
              <span class="mosaico-icono"><x-icono :nombre="$grupo->icono()" :tamano="18"/></span>
              <span class="mosaico-titulo">
                <b>{{ $grupo->etiqueta() }}</b>
                <span>{{ $grupo->bajada() }}</span>
              </span>
              <span class="mosaico-cuenta">{{ $mosaico['tarjetas']->count() }}</span>
              <x-icono nombre="abajo" :tamano="15" class="mosaico-flecha"/>
            </summary>
            <div class="mosaico-rejilla">
              @foreach ($mosaico['tarjetas'] as $acceso)
                <x-tarjeta-acceso :acceso="$acceso" :anclado="$usuario->tieneFavorito($acceso)"/>
              @endforeach
            </div>
          </details>
        @endforeach

        {{-- La tarjeta de Reportes BI no es un acceso más: se arma sola --}}
        <div class="rejilla rejilla--bi">
          <x-tarjeta-bi :departamentos="$departamentos" :total="$totalTableros"/>
        </div>
      </section>

      {{-- ---------- Tableros por departamento ---------- --}}
      <section class="seccion" id="tableros">
        <header class="seccion-cabecera">
          <h2><x-icono nombre="tablero" :tamano="18"/> Tableros de Power BI</h2>
          <p>Por departamento. Cada área abre una página con todos sus informes.</p>
        </header>

        @if ($departamentos->isEmpty())
          <div class="alerta">Todavía no tienes reportes habilitados. Pídeselos a un administrador.</div>
        @else
          <div class="pestanas" role="tablist" aria-label="Departamentos">
            <button type="button" role="tab" class="pestana pestana--activa" data-pestana="todos" aria-selected="true">
              Todos <em>{{ $areasPlanas->count() }}</em>
            </button>
            @foreach ($departamentos as $departamento)
              <button type="button" role="tab" class="pestana" data-pestana="{{ $departamento->slug }}" aria-selected="false">
                <x-icono :nombre="$departamento->icono" :tamano="15"/>
                {{ $departamento->nombre }} <em>{{ $departamento->areas->count() }}</em>
              </button>
            @endforeach
          </div>

          <div class="rejilla rejilla--areas">
            @foreach ($departamentos as $departamento)
              @foreach ($departamento->areas as $area)
                <x-tarjeta-area :area="$area" :anclado="$usuario->tieneFavorito($area)" data-departamento="{{ $departamento->slug }}"/>
              @endforeach
            @endforeach
          </div>
        @endif
      </section>
    </div>

    {{-- ---------- Favoritos: columna lateral ---------- --}}
    <x-riel-favoritos :favoritos="$favoritos" :tope="\App\Models\User::MAX_FAVORITOS"/>
  </div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/menu.js') }}?v={{ filemtime(public_path('js/menu.js')) }}"></script>
@endpush
