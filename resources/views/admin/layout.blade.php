@extends('layouts.app', ['hojas' => array_merge(['panel-crud.css', 'admin.css'], $hojas ?? [], ['tabla-tarjetas.css'])])

@section('clase-body', 'pagina-admin')

@section('contenido')
<x-barra-sup :encabezado="$encabezado" :bajada="$bajada ?? ''" :volver="route('menu')"/>

<main class="contenido contenido--ancha admin">
  {{-- Subpestañas de administración --}}
  <nav class="admin-nav" aria-label="Secciones de administración">
    <a href="{{ route('admin.reportes.index') }}" @class(['activo' => request()->routeIs('admin.reportes.*')])><x-icono nombre="tablero" :tamano="15"/> Reportes BI</a>
    <a href="{{ route('admin.estructura.index') }}" @class(['activo' => request()->routeIs('admin.estructura.*')])><x-icono nombre="edificio" :tamano="15"/> Departamentos y áreas</a>
    <a href="{{ route('admin.tarjetas.index') }}" @class(['activo' => request()->routeIs('admin.tarjetas.*')])><x-icono nombre="enlace" :tamano="15"/> Tarjetas del menú</a>
    <a href="{{ route('admin.usuarios.index') }}" @class(['activo' => request()->routeIs('admin.usuarios.*')])><x-icono nombre="personas" :tamano="15"/> Usuarios</a>
    <a href="{{ route('admin.trazabilidad.index') }}" @class(['activo' => request()->routeIs('admin.trazabilidad.*')])><x-icono nombre="huella" :tamano="15"/> Trazabilidad</a>
  </nav>

  @yield('admin')
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}"></script>
<script src="{{ asset('js/limpiar-form.js') }}?v={{ filemtime(public_path('js/limpiar-form.js')) }}"></script>
@endpush
