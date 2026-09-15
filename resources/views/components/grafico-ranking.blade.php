@props(['titulo', 'bajada' => null, 'datos', 'vacio' => 'Todavía no hay datos.'])

@php
  /** @var \Illuminate\Support\Collection<int, array{etiqueta:string,valor:int,texto:string,detalle:?string}> $serie */
  $serie = collect($datos);
  $maximo = max(1, (int) $serie->max('valor'));
@endphp

<figure class="gr gr--rank">
  <figcaption class="gr-cabecera">
    <h3>{{ $titulo }}</h3>
    @if ($bajada)<p>{{ $bajada }}</p>@endif
  </figcaption>

  @if ($serie->isEmpty())
    <p class="gr-vacio">{{ $vacio }}</p>
  @else
    <ol class="rank">
      @foreach ($serie as $d)
        <li title="{{ $d['detalle'] ?? $d['etiqueta'] }}">
          <span class="rank-nombre">{{ $d['etiqueta'] }}</span>
          <span class="rank-pista"><i style="--w:{{ max(2, round($d['valor'] / $maximo * 100)) }}%"></i></span>
          <span class="rank-cifra">{{ $d['texto'] }}</span>
        </li>
      @endforeach
    </ol>
  @endif
</figure>
