@extends('layouts.app', ['hojas' => ['bi.css']])

@section('titulo', 'Tableros · '.$area->nombre)
@section('clase-body', 'pagina-bi')

@section('contenido')
<x-barra-sup :encabezado="$area->nombre" :bajada="$area->departamento->nombre.' · '.$reportes->count().' '.Str::plural('tablero', $reportes->count())" :volver="route('menu')"/>

<main class="contenido contenido--ancha bi">

  {{-- ---------- Cabecera del área ---------- --}}
  <section class="area-cabecera">
    <div class="area-cabecera-icono"><x-icono :nombre="$area->icono" :tamano="34"/></div>
    <div class="area-cabecera-texto">
      <nav class="migas" aria-label="Ubicación">
        <a href="{{ route('menu') }}">Menú</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('menu') }}#tableros">Reportes BI</a>
        <span aria-hidden="true">›</span>
        <span>{{ $area->departamento->nombre }}</span>
      </nav>
      <h2>{{ $area->nombre }}</h2>
      <p>{{ $area->descripcion ?: 'Tableros de Power BI del área.' }}</p>
    </div>
    <div class="area-cabecera-acciones">
      <x-boton-favorito tipo="area" :id="$area->id" :anclado="$esFavorito"/>
      @can('administrar')
        <a class="btn btn--fantasma" href="{{ route('admin.reportes.index', ['area' => $area->id]) }}">
          <x-icono nombre="ajustes" :tamano="15"/> Administrar
        </a>
      @endcan
    </div>
  </section>

  {{-- ---------- Filtros ---------- --}}
  <div class="bi-filtros">
    <div class="chips" role="group" aria-label="Filtrar por tipo de reporte">
      <a class="chip @if(! $tipoFiltro) chip--activo @endif" href="{{ route('bi.area', $area) }}">Todos</a>
      @foreach ($tipos as $tipo)
        <a class="chip @if($tipoFiltro === $tipo) chip--activo @endif" href="{{ route('bi.area', [$area, 'tipo' => $tipo->value]) }}">
          {{ $tipo->etiqueta() }}
        </a>
      @endforeach
    </div>

    @if ($hermanas->isNotEmpty())
      <label class="saltar">
        <span>Otra área</span>
        <select data-saltar-area aria-label="Ir a otra área del departamento">
          <option value="">{{ $area->departamento->nombre }}…</option>
          @foreach ($hermanas as $h)
            <option value="{{ route('bi.area', $h) }}">{{ $h->nombre }}</option>
          @endforeach
        </select>
      </label>
    @endif
  </div>

  {{-- ---------- Lista de tableros ---------- --}}
  <div class="bi-cuerpo">
    {{-- Índice lateral: solo en pantalla ancha. Son los mismos títulos. --}}
    <nav class="bi-indice" aria-label="Tableros de esta área">
      <p class="bi-indice-titulo">En esta página</p>
      <ol>
        @foreach ($reportes as $reporte)
          <li><a href="#reporte-{{ $reporte->id }}" data-indice="{{ $reporte->id }}">
            <span class="tipo tipo--{{ $reporte->tipo->value }}"></span>{{ $reporte->titulo }}
          </a></li>
        @endforeach
      </ol>
    </nav>

    <div class="bi-lista">
      @foreach ($reportes as $reporte)
        <article class="reporte" id="reporte-{{ $reporte->id }}" data-reporte="{{ $reporte->id }}" @if($loop->first) data-abierto @endif>
          <header class="reporte-cabecera">
            <div class="reporte-titulo">
              <span class="tipo tipo--{{ $reporte->tipo->value }}">{{ $reporte->tipo->etiqueta() }}</span>
              <h3>{{ $reporte->titulo }}</h3>
              @if ($reporte->descripcion)<p>{{ $reporte->descripcion }}</p>@endif
            </div>
            <div class="reporte-acciones">
              @if ($reporte->esDemo())
                <span class="reporte-demo" title="Este es un tablero de muestra. Un administrador debe pegar la URL real de Power BI.">Muestra</span>
              @else
                <a class="btn btn--fantasma btn--sm" href="{{ $reporte->url_iframe }}" target="_blank" rel="noopener" title="Abrir en Power BI">
                  <x-icono nombre="externo" :tamano="14"/> <span class="solo-ancho">Power BI</span>
                </a>
              @endif
              <button type="button" class="btn btn--fantasma btn--sm" data-recargar title="Recargar tablero">
                <x-icono nombre="refrescar" :tamano="14"/>
              </button>
              <button type="button" class="btn btn--fantasma btn--sm" data-pantalla-completa title="Pantalla completa">
                <x-icono nombre="expandir" :tamano="14"/> <span class="solo-ancho">Pantalla completa</span>
              </button>
              <button type="button" class="btn btn--primario btn--sm reporte-abrir" data-abrir aria-expanded="false">
                Ver tablero <x-icono nombre="abajo" :tamano="14"/>
              </button>
            </div>
          </header>

          {{-- El iframe se carga al abrir (data-src): no se piden 10 Power BI
               a la vez al entrar a la página --}}
          <div class="reporte-marco" hidden>
            <div class="reporte-cargando" aria-hidden="true"><span></span> Cargando tablero…</div>
            <iframe title="{{ $reporte->titulo }}" data-src="{{ $reporte->urlIframe() }}"
                    loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
          </div>
        </article>
      @endforeach
    </div>
  </div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/bi.js') }}?v={{ filemtime(public_path('js/bi.js')) }}"></script>
@endpush
