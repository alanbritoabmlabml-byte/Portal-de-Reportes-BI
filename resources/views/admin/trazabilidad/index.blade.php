@extends('admin.layout', [
  'encabezado' => 'Trazabilidad',
  'bajada' => 'Quién entró, cuánto duró, por dónde pasó y qué hizo',
  'hojas' => ['trazabilidad.css'],
])

@section('titulo', 'Trazabilidad')

@php
  $consulta = request()->only(['desde', 'hasta', 'usuario', 'accion']);
  $dias = collect($metricas['dias']);
  $horas = collect($metricas['horas']);
@endphp

@section('admin')

  {{-- ---------- Filtros ---------- --}}
  <section class="tarjeta-panel">
    <header>
      <div>
        <h2>Rango de análisis</h2>
        <p>Del {{ $filtros['desde']->translatedFormat('j \d\e F \d\e Y') }} al {{ $filtros['hasta']->translatedFormat('j \d\e F \d\e Y') }}</p>
      </div>
      <div class="acciones-celda">
        <a class="btn btn--fantasma btn--sm" href="{{ route('admin.trazabilidad.exportar', ['formato' => 'xlsx'] + $consulta) }}">
          <x-icono nombre="excel" :tamano="15"/> Excel
        </a>
        <a class="btn btn--fantasma btn--sm" href="{{ route('admin.trazabilidad.exportar', ['formato' => 'pdf'] + $consulta) }}" target="_blank" rel="noopener">
          <x-icono nombre="impresora" :tamano="15"/> PDF
        </a>
      </div>
    </header>

    <form class="barra-filtros" method="GET" action="{{ route('admin.trazabilidad.index') }}">
      <div class="filtros-rejilla">
        <div class="campo campo--filtro">
          <label for="f-desde">Desde</label>
          <input id="f-desde" name="desde" type="date" value="{{ $filtros['desde']->toDateString() }}">
        </div>
        <div class="campo campo--filtro">
          <label for="f-hasta">Hasta</label>
          <input id="f-hasta" name="hasta" type="date" value="{{ $filtros['hasta']->toDateString() }}">
        </div>
        <div class="campo campo--filtro">
          <label for="f-usuario">Usuario</label>
          <select id="f-usuario" name="usuario">
            <option value="">Todos</option>
            @foreach ($usuarios as $u)
              <option value="{{ $u->id }}" @selected($filtros['usuario'] === $u->id)>{{ $u->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="campo campo--filtro">
          <label for="f-accion">Acción</label>
          <select id="f-accion" name="accion">
            <option value="">Todas</option>
            @foreach ($acciones as $valor => $etiqueta)
              <option value="{{ $valor }}" @selected($filtros['accion'] === $valor)>{{ $etiqueta }}</option>
            @endforeach
          </select>
        </div>
        <div class="campo campo--filtro filtros-acciones">
          <a class="btn btn--fantasma" href="{{ route('admin.trazabilidad.index') }}">Limpiar</a>
          <button type="submit" class="btn btn--primario">Aplicar</button>
        </div>
      </div>
    </form>
  </section>

  {{-- ---------- Cifras ---------- --}}
  <section class="kpis">
    <article class="kpi">
      <span class="kpi-etiqueta">Sesiones</span>
      <b class="kpi-cifra">{{ number_format($metricas['sesiones']) }}</b>
      <span class="kpi-pie">{{ $metricas['abiertas'] }} en curso ahora</span>
    </article>
    <article class="kpi">
      <span class="kpi-etiqueta">Usuarios distintos</span>
      <b class="kpi-cifra">{{ number_format($metricas['usuarios']) }}</b>
      <span class="kpi-pie">entraron al portal en el rango</span>
    </article>
    <article class="kpi">
      <span class="kpi-etiqueta">Tiempo en el portal</span>
      <b class="kpi-cifra">{{ \App\Models\Sesion::formatear($metricas['segundos_total']) }}</b>
      <span class="kpi-pie">promedio {{ \App\Models\Sesion::formatear($metricas['promedio_sesion']) }} por sesión</span>
    </article>
    <article class="kpi">
      <span class="kpi-etiqueta">Páginas vistas</span>
      <b class="kpi-cifra">{{ number_format($metricas['paginas']) }}</b>
      <span class="kpi-pie">sesión más larga: {{ \App\Models\Sesion::formatear($metricas['sesion_larga']) }}</span>
    </article>
    <article class="kpi">
      <span class="kpi-etiqueta">Acciones registradas</span>
      <b class="kpi-cifra">{{ number_format($metricas['acciones']) }}</b>
      <span class="kpi-pie">altas, ediciones, bajas y accesos</span>
    </article>
  </section>

  {{-- ---------- Gráficos ---------- --}}
  <div class="graficos">
    <section class="tarjeta-panel panel-grafico panel-grafico--ancho">
      <x-grafico-barras
        titulo="Sesiones por día"
        bajada="Cuántas veces se entró al portal cada día del rango"
        :etiqueta-cada="max(1, (int) ceil($dias->count() / 12))"
        :datos="$dias->map(fn ($d) => [
          'etiqueta' => $d['fecha']->translatedFormat('D j M'),
          'corta' => $d['fecha']->format('j/n'),
          'valor' => $d['sesiones'],
          'detalle' => $d['fecha']->translatedFormat('l j \d\e F').': '.$d['sesiones'].' sesiones · '.$d['usuarios'].' usuarios · '.$d['minutos'].' min',
        ])"/>
    </section>

    <section class="tarjeta-panel panel-grafico">
      <x-grafico-barras
        titulo="Horas de mayor uso"
        bajada="Páginas abiertas según la hora del día"
        :etiqueta-cada="3"
        :datos="$horas->map(fn ($h) => [
          'etiqueta' => sprintf('%02d:00', $h['hora']),
          'corta' => sprintf('%02d', $h['hora']),
          'valor' => $h['visitas'],
          'detalle' => sprintf('%02d:00 – %02d:59', $h['hora'], $h['hora']).': '.$h['visitas'].' páginas',
        ])"/>
    </section>

    <section class="tarjeta-panel panel-grafico">
      <figure class="gr">
        <figcaption class="gr-cabecera">
          <h3>Qué se hizo</h3>
          <p>Acciones anotadas en la bitácora</p>
        </figcaption>
        @if (collect($metricas['acciones_top'])->isEmpty())
          <p class="gr-vacio">Sin acciones registradas en este rango.</p>
        @else
          <ul class="acciones-resumen">
            @foreach ($metricas['acciones_top'] as $a)
              <li>
                <span class="pastilla pastilla--{{ $a['tono'] }}">{{ $a['etiqueta'] }}</span>
                <b>{{ number_format($a['total']) }}</b>
              </li>
            @endforeach
          </ul>
        @endif
      </figure>
    </section>
    <section class="tarjeta-panel panel-grafico">
      <x-grafico-ranking
        titulo="Páginas donde más tiempo se pasa"
        bajada="Tiempo acumulado por pantalla"
        vacio="Nadie ha navegado el portal en este rango."
        :datos="collect($metricas['paginas_top'])->map(fn ($p) => [
          'etiqueta' => $p['etiqueta'],
          'valor' => $p['segundos'],
          'texto' => \App\Models\Sesion::formatear($p['segundos']),
          'detalle' => $p['etiqueta'].': '.$p['visitas'].' visitas',
        ])"/>
    </section>

    <section class="tarjeta-panel panel-grafico">
      <x-grafico-ranking
        titulo="Usuarios más activos"
        bajada="Tiempo acumulado dentro del portal"
        vacio="Sin actividad de usuarios en este rango."
        :datos="collect($metricas['usuarios_top'])->map(fn ($u) => [
          'etiqueta' => $u['etiqueta'],
          'valor' => $u['segundos'],
          'texto' => \App\Models\Sesion::formatear($u['segundos']),
          'detalle' => $u['etiqueta'].': '.$u['visitas'].' páginas',
        ])"/>
    </section>

  </div>

  {{-- ---------- Sesiones ---------- --}}
  <section class="tarjeta-panel">
    <header>
      <div>
        <h2>Sesiones</h2>
        <p>Cada entrada al portal, con su duración y su recorrido</p>
      </div>
    </header>

    <div class="tabla-scroll">
      <table class="tabla-tarjetas tabla-sesiones">
        <thead>
          <tr>
            <th>Usuario</th><th>Inicio</th><th>Duración</th><th>Páginas</th>
            <th>Equipo</th><th>Estado</th><th class="col-acciones">Recorrido</th>
          </tr>
        </thead>
        <tbody>
        @forelse ($sesiones as $sesion)
          <tr>
            <td class="celda-titulo">
              <div class="nombre">{{ $sesion->usuario?->name ?? 'Cuenta eliminada' }}</div>
              <div class="correo">{{ $sesion->usuario?->email }}</div>
            </td>
            <td data-etiqueta="Inicio">
              {{ $sesion->iniciada_at->translatedFormat('j M Y') }}
              <div class="correo">{{ $sesion->iniciada_at->format('H:i:s') }}</div>
            </td>
            <td data-etiqueta="Duración"><b>{{ $sesion->duracionLegible() }}</b></td>
            <td data-etiqueta="Páginas">{{ $sesion->visitas()->count() }}</td>
            <td data-etiqueta="Equipo">
              {{ $sesion->navegador }} · {{ $sesion->plataforma }}
              <div class="correo">{{ $sesion->ip }}</div>
            </td>
            <td data-etiqueta="Estado">
              @if ($sesion->abierta())
                <span class="pastilla pastilla--activo">En curso</span>
              @elseif ($sesion->motivo_cierre === 'salida')
                <span class="pastilla">Cerró sesión</span>
              @else
                <span class="pastilla pastilla--inactivo">Expirada</span>
              @endif
            </td>
            <td class="col-acciones">
              <a class="btn btn--fantasma btn--sm" href="{{ route('admin.trazabilidad.sesion', $sesion) }}">Ver recorrido</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="tabla-vacia">Ninguna sesión en este rango.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    {{ $sesiones->links() }}
  </section>

  {{-- ---------- Bitácora ---------- --}}
  <section class="tarjeta-panel">
    <header>
      <div>
        <h2>Bitácora de cambios y accesos</h2>
        <p>Todo lo que se creó, editó, eliminó o consultó</p>
      </div>
    </header>

    <ol class="bitacora">
      @forelse ($asientos as $asiento)
        <li class="bit bit--{{ $asiento->accion->tono() }}">
          <span class="bit-hora">
            <b>{{ $asiento->created_at->format('H:i') }}</b>
            <span>{{ $asiento->created_at->translatedFormat('j M') }}</span>
          </span>
          <span class="bit-cuerpo">
            <span class="bit-titulo">
              <span class="pastilla pastilla--{{ $asiento->accion->tono() }}">{{ $asiento->accion->etiqueta() }}</span>
              {{ $asiento->descripcion }}
            </span>
            <span class="bit-pie">
              {{ $asiento->usuario?->name ?? $asiento->usuario_nombre ?? 'Cuenta eliminada' }}
              @if ($asiento->ruta) · {{ $asiento->ruta }} @endif
              @if ($asiento->ip) · {{ $asiento->ip }} @endif
            </span>
            @if ($asiento->datos)
              <details class="bit-datos">
                <summary>Detalle</summary>
                <pre>{{ json_encode($asiento->datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
              </details>
            @endif
          </span>
        </li>
      @empty
        <li class="tabla-vacia">Sin movimientos en este rango.</li>
      @endforelse
    </ol>

    {{ $asientos->links() }}
  </section>

  {{-- ---------- Documentación ---------- --}}
  <details class="tarjeta-panel panel-plegable doc-traza">
    <summary>
      <div>
        <h2>Qué se registra y cómo leerlo</h2>
        <p>Documentación de la trazabilidad del portal</p>
      </div>
      <span class="btn btn--sm menu-exp-boton-claro">Ver <x-icono nombre="abajo" :tamano="14"/></span>
    </summary>

    <div class="doc-cuerpo">
      <h3>Sesión</h3>
      <p>
        Se abre cuando el usuario inicia sesión y se cierra cuando pulsa «Cerrar sesión».
        Si deja de dar señales de vida durante {{ \App\Support\Rastro::MINUTOS_INACTIVIDAD }} minutos,
        la sesión se marca como <b>expirada</b> y su duración se corta en el último latido, no en la hora actual:
        así una pestaña olvidada no infla el tiempo de nadie.
      </p>

      <h3>Página visitada</h3>
      <p>
        Cada pantalla que se abre deja su hora de entrada. La de salida se escribe al abrir la siguiente
        página, al cerrar la pestaña o al vencer la sesión. El navegador avisa que sigue ahí cada minuto
        mientras la pestaña está a la vista; minimizada o en segundo plano no suma tiempo.
        No se registran las llamadas internas (latidos, anclar favoritos, marcar notificaciones, cambiar tema).
      </p>

      <h3>Bitácora</h3>
      <p>
        Anota las acciones con consecuencia: entradas y salidas, altas, ediciones y bajas de reportes,
        tarjetas, departamentos, áreas y usuarios, cambios de permisos de vista y exportaciones.
        En altas y ediciones se guarda el antes y el después de los campos relevantes: ábrelo en «Detalle».
      </p>

      <h3>Exportación</h3>
      <p>
        <b>Excel</b> descarga un libro con tres hojas (sesiones, páginas visitadas y bitácora) que respeta los
        filtros de arriba. <b>PDF</b> abre una hoja lista para imprimir: en el diálogo del navegador elige
        «Guardar como PDF». Ambas exportaciones quedan a su vez anotadas en la bitácora.
      </p>

      <h3>Qué NO se guarda</h3>
      <p>
        No se registran contraseñas, contenido de formularios ni lo que se ve dentro de un tablero de Power BI
        (eso vive en el servicio de Microsoft). De cada equipo se anota IP, navegador y sistema operativo,
        que es lo necesario para auditar un acceso.
      </p>
    </div>
  </details>

@endsection
