@props(['favoritos', 'tope'])

{{--
  Riel de favoritos: lista vertical desplegable. En pantallas grandes se queda
  fijo en la columna lateral; en pantallas chicas es un panel más del flujo.
  El tope de anclados lo fija User::MAX_FAVORITOS.
--}}
<aside class="riel" id="favoritos" data-riel data-tope="{{ $tope }}" @if($favoritos->count() >= $tope) data-lleno @endif>
  <details class="riel-panel" open>
    <summary>
      <x-icono nombre="pin" :tamano="17"/>
      <span class="riel-titulo">Mis favoritos</span>
      <span class="riel-cuenta" data-riel-cuenta>{{ $favoritos->count() }}/{{ $tope }}</span>
      <x-icono nombre="abajo" :tamano="14" class="riel-flecha"/>
    </summary>

    <ul class="riel-lista" data-riel-lista @if($favoritos->isEmpty()) hidden @endif>
      @foreach ($favoritos as $fav)
        @php
          $esAcceso = $fav instanceof \App\Models\Acceso;
          $destino = $esAcceso ? $fav->url : route('bi.area', $fav);
        @endphp
        <li data-fav-tipo="{{ $esAcceso ? 'acceso' : 'area' }}" data-fav-id="{{ $fav->id }}">
          <a class="riel-item" href="{{ $destino }}"
             @if($esAcceso && $fav->nueva_pestana) target="_blank" rel="noopener" @endif>
            <span class="riel-icono">
              <x-icono :nombre="$esAcceso ? 'enlace' : $fav->icono" :tamano="15"/>
            </span>
            <span class="riel-nombre">{{ $fav->nombre }}</span>
            @if ($esAcceso)<x-icono nombre="externo" :tamano="13"/>@else<x-icono nombre="flecha" :tamano="13"/>@endif
          </a>
          <form method="POST" action="{{ route('favoritos.alternar') }}" class="riel-quitar-form"
                data-favorito data-tipo="{{ $esAcceso ? 'acceso' : 'area' }}" data-id="{{ $fav->id }}">
            @csrf
            <input type="hidden" name="tipo" value="{{ $esAcceso ? 'acceso' : 'area' }}">
            <input type="hidden" name="id" value="{{ $fav->id }}">
            <button type="submit" class="riel-quitar" title="Quitar de favoritos" aria-label="Quitar {{ $fav->nombre }} de favoritos">
              <x-icono nombre="cerrar" :tamano="14"/>
            </button>
          </form>
        </li>
      @endforeach
    </ul>

    <p class="riel-vacio" data-riel-vacio @if($favoritos->isNotEmpty()) hidden @endif>
      Ancla hasta {{ $tope }} accesos o áreas con <b>Anclar a favoritos</b> y aparecerán aquí,
      siempre a la vista.
    </p>

    <p class="riel-tope" data-riel-pie>
      {{ $favoritos->count() }} de {{ $tope }} espacios usados
    </p>
  </details>
</aside>
