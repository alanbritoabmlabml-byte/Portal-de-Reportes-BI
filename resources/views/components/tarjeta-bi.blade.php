@props(['departamentos', 'total'])

{{--
  Tarjeta «Reportes BI»: una lista desplegable por departamento → área. Cada
  área lleva a su página de Power BI. Solo aparecen las áreas con al menos un
  reporte visible para quien mira.
--}}
<article class="tarjeta tarjeta--bi" data-tarjeta data-buscar="reportes bi power bi tableros {{ Str::lower($departamentos->pluck('nombre')->implode(' ').' '.$departamentos->flatMap->areas->pluck('nombre')->implode(' ')) }}">
  <div class="tarjeta-vista tarjeta-vista--bi" aria-hidden="true">
    <svg viewBox="0 0 320 150" class="bi-ilustracion" preserveAspectRatio="xMidYMid meet">
      <defs>
        <linearGradient id="bi-g" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0" stop-color="#063381"/><stop offset="1" stop-color="#04205a"/>
        </linearGradient>
      </defs>
      <rect width="320" height="150" fill="url(#bi-g)"/>
      <g class="bi-barras" fill="#fff" opacity=".9">
        <rect x="34" y="88" width="22" height="42" rx="3" style="--h:42px"/>
        <rect x="66" y="62" width="22" height="68" rx="3" style="--h:68px"/>
        <rect x="98" y="76" width="22" height="54" rx="3" style="--h:54px"/>
        <rect x="130" y="40" width="22" height="90" rx="3" fill="#E00613" style="--h:90px"/>
        <rect x="162" y="70" width="22" height="60" rx="3" style="--h:60px"/>
      </g>
      <polyline class="bi-linea" points="34,60 66,44 98,52 130,24 162,40 200,30" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" opacity=".85"/>
      <circle class="bi-dona" cx="258" cy="76" r="34" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="14"/>
      <circle class="bi-dona bi-dona--valor" cx="258" cy="76" r="34" fill="none" stroke="#fff" stroke-width="14" stroke-dasharray="150 214" stroke-linecap="butt" transform="rotate(-90 258 76)"/>
      <text x="258" y="81" text-anchor="middle" fill="#fff" font-family="Poppins, sans-serif" font-weight="700" font-size="16">{{ $total }}</text>
    </svg>
    <span class="tarjeta-etiqueta">Power BI</span>
  </div>

  <div class="tarjeta-cuerpo">
    <h3>Reportes BI</h3>
    <p>Tableros de Power BI por departamento y área. Ves solo los que tienes habilitados.</p>
    <small class="tarjeta-dominio"><x-icono nombre="tablero" :tamano="12"/> {{ $total }} {{ Str::plural('tablero', $total) }} disponibles</small>
  </div>

  @if ($departamentos->isEmpty())
    <div class="bi-vacio">Todavía no tienes reportes habilitados. Pídeselos a un administrador.</div>
  @else
    <div class="bi-arbol">
      @foreach ($departamentos as $departamento)
        <details class="bi-dep" @if($loop->first) open @endif>
          <summary>
            <span class="bi-dep-icono"><x-icono :nombre="$departamento->icono" :tamano="16"/></span>
            <span class="bi-dep-nombre">{{ $departamento->nombre }}</span>
            <span class="bi-dep-cuenta">{{ $departamento->areas->count() }} {{ Str::plural('área', $departamento->areas->count()) }}</span>
            <x-icono nombre="abajo" :tamano="14" class="bi-dep-flecha"/>
          </summary>
          <ul>
            @foreach ($departamento->areas as $area)
              <li>
                <a href="{{ route('bi.area', $area) }}">
                  <x-icono :nombre="$area->icono" :tamano="15"/>
                  <span>{{ $area->nombre }}</span>
                  <em>{{ $area->reportes_visibles_count }}</em>
                  <x-icono nombre="flecha" :tamano="13" class="bi-area-flecha"/>
                </a>
              </li>
            @endforeach
          </ul>
        </details>
      @endforeach
    </div>
  @endif
</article>
