@props(['titulo', 'bajada' => null, 'datos', 'sufijo' => '', 'etiquetaCada' => 1])

@php
  /** @var \Illuminate\Support\Collection<int, array{etiqueta:string,corta:string,valor:int,detalle:string}> $serie */
  $serie = collect($datos);
  $maximo = max(1, (int) $serie->max('valor'));
  $cima = $serie->search(fn ($d) => (int) $d['valor'] === $maximo);
@endphp

<figure class="gr">
  <figcaption class="gr-cabecera">
    <h3>{{ $titulo }}</h3>
    @if ($bajada)<p>{{ $bajada }}</p>@endif
  </figcaption>

  @if ($serie->sum('valor') === 0)
    <p class="gr-vacio">Sin actividad registrada en este rango.</p>
  @else
    <div class="gr-lienzo" role="img" aria-label="{{ $titulo }}">
      <div class="gr-rejilla" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
      <ol class="gr-barras">
        @foreach ($serie as $i => $d)
          <li class="gr-barra" title="{{ $d['detalle'] }}">
            <span class="gr-valor @if($i === $cima) gr-valor--cima @endif">{{ $d['valor'] }}{{ $sufijo }}</span>
            <span class="gr-marca" style="--h:{{ max(2, round($d['valor'] / $maximo * 100)) }}%"></span>
            <span class="gr-eje @if($i % $etiquetaCada !== 0 && $i !== $serie->count() - 1) gr-eje--muda @endif">{{ $d['corta'] }}</span>
          </li>
        @endforeach
      </ol>
    </div>
  @endif
</figure>
