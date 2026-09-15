@props(['encabezado', 'bajada' => '', 'volver' => null])

@php
    $usuario = auth()->user();
    $recientes = $usuario->notificaciones()->limit(8)->get();
    $sinLeer = $usuario->notificacionesSinLeer()->count();
    $temaActual = $usuario->tema ?? \App\Enums\Tema::Sistema;
@endphp

{{-- Encabezado común: marca, título, campana de notificaciones y sesión. --}}
<header class="barra-sup">
  <a class="marca" href="{{ route('menu') }}" aria-label="Ir al menú principal">
    <x-logo/>
    <div class="nombre">Plásticos<br>Carmen</div>
  </a>
  <div class="divisor"></div>
  <div class="titulo">
    @if ($volver)
      <a class="volver" href="{{ $volver }}"><x-icono nombre="flecha" :tamano="14" class="icono-volver"/> Menú principal</a>
    @endif
    <h1>{{ $encabezado }}</h1>
    @if ($bajada)<p>{{ $bajada }}</p>@endif
  </div>

  <div class="sesion">
    {{ $slot }}

    {{-- Apariencia: claro → oscuro → según el sistema --}}
    <form method="POST" action="{{ route('tema.guardar') }}" data-tema-form>
      @csrf
      <input type="hidden" name="tema" value="{{ $temaActual->siguiente()->value }}">
      <button type="submit" class="btn btn--fantasma btn--icono btn-tema"
              title="Apariencia: {{ $temaActual->etiqueta() }}"
              aria-label="Cambiar apariencia (ahora: {{ $temaActual->etiqueta() }})">
        <x-icono nombre="sol" :tamano="18" class="icono-sol"/>
        <x-icono nombre="luna" :tamano="18" class="icono-luna"/>
        <span class="punto-sistema" aria-hidden="true"></span>
      </button>
    </form>

    {{-- Campana --}}
    <details class="campana" id="campana">
      <summary class="btn btn--fantasma btn--icono" aria-label="Notificaciones{{ $sinLeer ? ", {$sinLeer} sin leer" : '' }}">
        <x-icono nombre="campana" :tamano="18"/>
        @if ($sinLeer)
          <span class="campana-contador" data-contador-notificaciones>{{ $sinLeer > 9 ? '9+' : $sinLeer }}</span>
        @endif
      </summary>
      <div class="campana-panel">
        <div class="campana-cabecera">
          <strong>Notificaciones</strong>
          @if ($sinLeer)
            <form method="POST" action="{{ route('notificaciones.leerTodas') }}" data-leer-todas>
              @csrf
              <button type="submit" class="enlace-suave">Marcar todas como leídas</button>
            </form>
          @endif
        </div>
        <ul class="campana-lista">
          @forelse ($recientes as $n)
            <li class="notif notif--{{ $n->tipo->value }} @if(! $n->leida()) notif--nueva @endif" data-notificacion="{{ $n->id }}">
              <form method="POST" action="{{ route('notificaciones.leer', $n) }}" data-leer>
                @csrf
                <button type="submit" class="notif-boton">
                  <span class="notif-punto" aria-hidden="true"></span>
                  <span class="notif-texto">
                    <b>{{ $n->titulo }}</b>
                    @if ($n->mensaje)<span>{{ Str::limit($n->mensaje, 110) }}</span>@endif
                    <time datetime="{{ $n->created_at->toIso8601String() }}">{{ $n->created_at->diffForHumans() }}</time>
                  </span>
                  @if ($n->url)<x-icono nombre="externo" :tamano="14" class="notif-externo"/>@endif
                </button>
              </form>
            </li>
          @empty
            <li class="notif-vacia">Sin notificaciones por ahora.</li>
          @endforelse
        </ul>
        <a class="campana-pie" href="{{ route('notificaciones.index') }}">Ver todas</a>
      </div>
    </details>

    {{-- Usuario --}}
    <details class="cuenta" id="cuenta">
      <summary aria-label="Cuenta de {{ $usuario->name }}">
        <span class="avatar" aria-hidden="true">{{ $usuario->iniciales() }}</span>
        <span class="quien">
          <b>{{ $usuario->name }}</b>
          <span class="rol rol--{{ $usuario->rol->value }}">{{ $usuario->rol->etiqueta() }}</span>
        </span>
        <x-icono nombre="abajo" :tamano="14" class="cuenta-flecha"/>
      </summary>
      <div class="cuenta-panel">
        <div class="cuenta-quien">
          <b>{{ $usuario->name }}</b>
          <span>{{ $usuario->email }}</span>
        </div>
        <a href="{{ route('menu') }}"><x-icono nombre="casa" :tamano="16"/> Menú principal</a>
        <a href="{{ route('notificaciones.index') }}"><x-icono nombre="campana" :tamano="16"/> Notificaciones</a>
        @can('administrar')
          <div class="cuenta-separador">Administración</div>
          <a href="{{ route('admin.reportes.index') }}" @class(['activo' => request()->routeIs('admin.reportes.*')])><x-icono nombre="tablero" :tamano="16"/> Reportes BI</a>
          <a href="{{ route('admin.estructura.index') }}" @class(['activo' => request()->routeIs('admin.estructura.*')])><x-icono nombre="edificio" :tamano="16"/> Departamentos y áreas</a>
          <a href="{{ route('admin.tarjetas.index') }}" @class(['activo' => request()->routeIs('admin.tarjetas.*')])><x-icono nombre="enlace" :tamano="16"/> Tarjetas del menú</a>
          <a href="{{ route('admin.usuarios.index') }}" @class(['activo' => request()->routeIs('admin.usuarios.*')])><x-icono nombre="personas" :tamano="16"/> Usuarios</a>
          <a href="{{ route('admin.trazabilidad.index') }}" @class(['activo' => request()->routeIs('admin.trazabilidad.*')])><x-icono nombre="huella" :tamano="16"/> Trazabilidad</a>
        @endcan
        <div class="cuenta-separador"></div>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="peligro"><x-icono nombre="salir" :tamano="16"/> Cerrar sesión</button>
        </form>
      </div>
    </details>
  </div>
</header>
