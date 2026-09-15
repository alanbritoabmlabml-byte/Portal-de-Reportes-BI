@extends('layouts.app', ['hojas' => ['panel-crud.css', 'notificaciones.css']])

@section('titulo', 'Notificaciones')

@section('contenido')
<x-barra-sup encabezado="Notificaciones" bajada="Avisos, accesos nuevos y novedades" :volver="route('menu')"/>

<main class="contenido">
  <section class="tarjeta-panel">
    <header>
      <div>
        <h2>Tus notificaciones</h2>
        <p>{{ $notificaciones->total() }} en total</p>
      </div>
      @if ($usuario->notificacionesSinLeer()->exists())
        <form method="POST" action="{{ route('notificaciones.leerTodas') }}">
          @csrf
          <button type="submit" class="btn btn--fantasma btn--sm menu-exp-boton-claro">Marcar todas como leídas</button>
        </form>
      @endif
    </header>

    <ul class="notif-lista">
      @forelse ($notificaciones as $n)
        <li class="notif notif--{{ $n->tipo->value }} @if(! $n->leida()) notif--nueva @endif">
          <form method="POST" action="{{ route('notificaciones.leer', $n) }}">
            @csrf
            <button type="submit" class="notif-boton">
              <span class="notif-punto" aria-hidden="true"></span>
              <span class="notif-texto">
                <b>{{ $n->titulo }}</b>
                @if ($n->mensaje)<span>{{ $n->mensaje }}</span>@endif
                <time datetime="{{ $n->created_at->toIso8601String() }}">{{ $n->created_at->translatedFormat('d \d\e F, H:i') }} · {{ $n->created_at->diffForHumans() }}</time>
              </span>
              @if ($n->url)<x-icono nombre="externo" :tamano="15" class="notif-externo"/>@endif
            </button>
          </form>
        </li>
      @empty
        <li class="tabla-vacia">Todavía no tienes notificaciones.</li>
      @endforelse
    </ul>

    {{ $notificaciones->links() }}
  </section>
</main>
@endsection
