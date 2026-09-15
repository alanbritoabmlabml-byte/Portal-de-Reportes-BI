{{--
  Paginador con la paleta de marca. Las vistas que trae Laravel asumen
  Tailwind, que este proyecto no compila, así que se usa esta.
  Se invoca con: $paginador->links('vendor.pagination.marca')
--}}
@if ($paginator->hasPages())
  <nav class="paginador" role="navigation" aria-label="Paginación">
    <p class="paginador-cuenta">
      {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
      de <strong>{{ $paginator->total() }}</strong>
    </p>

    <div class="paginador-botones">
      @if ($paginator->onFirstPage())
        <span class="paginador-btn" aria-disabled="true">‹ Anterior</span>
      @else
        <a class="paginador-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Anterior</a>
      @endif

      @foreach ($elements as $element)
        {{-- Los puntos suspensivos que inserta Laravel entre bloques de páginas --}}
        @if (is_string($element))
          <span class="paginador-puntos" aria-hidden="true">{{ $element }}</span>
        @endif

        @if (is_array($element))
          @foreach ($element as $pagina => $url)
            {{-- En móvil solo se dejan ver la primera, la última y la actual:
                 la lista completa no cabe y se pulsaba mal. Ver panel-crud.css. --}}
            @php $extremo = $pagina == 1 || $pagina == $paginator->lastPage(); @endphp

            @if ($pagina == $paginator->currentPage())
              <span class="paginador-btn paginador-btn--num paginador-btn--activo"
                    aria-current="page">{{ $pagina }}</span>
            @else
              <a class="paginador-btn paginador-btn--num{{ $extremo ? ' paginador-btn--extremo' : '' }}"
                 href="{{ $url }}">{{ $pagina }}</a>
            @endif
          @endforeach
        @endif
      @endforeach

      @if ($paginator->hasMorePages())
        <a class="paginador-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente ›</a>
      @else
        <span class="paginador-btn" aria-disabled="true">Siguiente ›</span>
      @endif
    </div>
  </nav>
@endif
