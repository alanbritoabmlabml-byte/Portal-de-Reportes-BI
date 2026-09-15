@extends('admin.layout', [
  'encabezado' => 'Recorrido de sesión',
  'bajada' => ($sesion->usuario?->name ?? 'Cuenta eliminada').' · '.$sesion->iniciada_at->translatedFormat('j \d\e F \d\e Y, H:i'),
  'hojas' => ['trazabilidad.css'],
])

@section('titulo', 'Recorrido de sesión')

@section('admin')
  <p class="migas"><a href="{{ route('admin.trazabilidad.index') }}">← Volver a trazabilidad</a></p>

  <section class="kpis">
    <article class="kpi">
      <span class="kpi-etiqueta">Duración</span>
      <b class="kpi-cifra">{{ $sesion->duracionLegible() }}</b>
      <span class="kpi-pie">
        {{ $sesion->iniciada_at->format('H:i:s') }} →
        {{ $sesion->cerrada_at?->format('H:i:s') ?? 'en curso' }}
      </span>
    </article>
    <article class="kpi">
      <span class="kpi-etiqueta">Páginas visitadas</span>
      <b class="kpi-cifra">{{ $sesion->visitas->count() }}</b>
      <span class="kpi-pie">{{ $sesion->visitas->pluck('ruta')->unique()->count() }} pantallas distintas</span>
    </article>
    <article class="kpi">
      <span class="kpi-etiqueta">Acciones</span>
      <b class="kpi-cifra">{{ $sesion->asientos->count() }}</b>
      <span class="kpi-pie">anotadas en la bitácora</span>
    </article>
    <article class="kpi">
      <span class="kpi-etiqueta">Equipo</span>
      <b class="kpi-cifra kpi-cifra--texto">{{ $sesion->navegador }}</b>
      <span class="kpi-pie">{{ $sesion->plataforma }} · {{ $sesion->ip }}</span>
    </article>
  </section>

  <section class="tarjeta-panel">
    <header>
      <div>
        <h2>Página por página</h2>
        <p>En el orden en que las abrió, con el tiempo que pasó en cada una</p>
      </div>
    </header>

    @php $maxSegundos = max(1, (int) $sesion->visitas->max('segundos')); @endphp

    <ol class="recorrido">
      @forelse ($sesion->visitas as $visita)
        <li>
          <span class="recorrido-hora">{{ $visita->entrada_at->format('H:i:s') }}</span>
          <span class="recorrido-cuerpo">
            <span class="recorrido-titulo">{{ $visita->titulo }}</span>
            <span class="recorrido-url">{{ $visita->url }}</span>
            <span class="rank-pista"><i style="--w:{{ max(2, round($visita->segundos / $maxSegundos * 100)) }}%"></i></span>
          </span>
          <span class="recorrido-tiempo">{{ $visita->permanenciaLegible() }}</span>
        </li>
      @empty
        <li class="tabla-vacia">No alcanzó a abrir ninguna página.</li>
      @endforelse
    </ol>
  </section>

  <section class="tarjeta-panel">
    <header>
      <div>
        <h2>Qué hizo</h2>
        <p>Acciones registradas durante esta sesión</p>
      </div>
    </header>

    <ol class="bitacora">
      @forelse ($sesion->asientos as $asiento)
        <li class="bit bit--{{ $asiento->accion->tono() }}">
          <span class="bit-hora">
            <b>{{ $asiento->created_at->format('H:i') }}</b>
            <span>{{ $asiento->created_at->format('s') }}s</span>
          </span>
          <span class="bit-cuerpo">
            <span class="bit-titulo">
              <span class="pastilla pastilla--{{ $asiento->accion->tono() }}">{{ $asiento->accion->etiqueta() }}</span>
              {{ $asiento->descripcion }}
            </span>
            @if ($asiento->ruta)<span class="bit-pie">{{ $asiento->ruta }}</span>@endif
            @if ($asiento->datos)
              <details class="bit-datos">
                <summary>Detalle</summary>
                <pre>{{ json_encode($asiento->datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
              </details>
            @endif
          </span>
        </li>
      @empty
        <li class="tabla-vacia">No registró acciones administrativas.</li>
      @endforelse
    </ol>
  </section>
@endsection
