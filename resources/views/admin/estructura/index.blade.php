@extends('admin.layout', ['encabezado' => 'Departamentos y áreas', 'bajada' => 'El árbol del que cuelgan los reportes de Power BI'])

@section('titulo', 'Administrar estructura')

@php $formActivo = old('_form', 'dep-alta'); @endphp

@section('admin')
  <div class="estructura-rejilla">
    {{-- ---------- Alta de departamento ---------- --}}
    <details class="tarjeta-panel panel-plegable" @if($formActivo === 'dep-alta' && $errors->any()) open @endif>
      <summary>
        <div><h2>Nuevo departamento</h2><p>Agrupa áreas</p></div>
        <span class="btn btn--sm menu-exp-boton-claro">Agregar <x-icono nombre="abajo" :tamano="14"/></span>
      </summary>
      <form class="form-alta" method="POST" action="{{ route('admin.departamentos.store') }}">
        @csrf
        <input type="hidden" name="_form" value="dep-alta">
        @if ($formActivo === 'dep-alta' && $errors->any())
          <div class="alerta alerta--error" role="alert">{{ $errors->first() }}</div>
        @endif
        @include('admin.estructura.campos', ['modelo' => null, 'activa' => $formActivo === 'dep-alta', 'sufijo' => 'dep-nuevo', 'conDepartamento' => false])
        <div class="form-acciones">
          <button type="button" class="btn btn--fantasma" data-limpiar>Limpiar</button>
          <button type="submit" class="btn btn--primario">Crear departamento</button>
        </div>
      </form>
    </details>

    {{-- ---------- Alta de área ---------- --}}
    <details class="tarjeta-panel panel-plegable" @if($formActivo === 'area-alta' && $errors->any()) open @endif>
      <summary>
        <div><h2>Nueva área</h2><p>Dentro de un departamento</p></div>
        <span class="btn btn--sm menu-exp-boton-claro">Agregar <x-icono nombre="abajo" :tamano="14"/></span>
      </summary>
      <form class="form-alta" method="POST" action="{{ route('admin.areas.store') }}">
        @csrf
        <input type="hidden" name="_form" value="area-alta">
        @if ($formActivo === 'area-alta' && $errors->any())
          <div class="alerta alerta--error" role="alert">{{ $errors->first() }}</div>
        @endif
        @include('admin.estructura.campos', ['modelo' => null, 'activa' => $formActivo === 'area-alta', 'sufijo' => 'area-nuevo', 'conDepartamento' => true])
        <div class="form-acciones">
          <button type="button" class="btn btn--fantasma" data-limpiar>Limpiar</button>
          <button type="submit" class="btn btn--primario">Crear área</button>
        </div>
      </form>
    </details>
  </div>

  {{-- ---------- Árbol ---------- --}}
  @foreach ($departamentos as $iDep => $departamento)
    @php $formDep = "dep-{$departamento->id}"; $activaDep = $formActivo === $formDep; @endphp
    <section class="tarjeta-panel">
      <header>
        <div class="dep-cabecera">
          <span class="dep-icono"><x-icono :nombre="$departamento->icono" :tamano="20"/></span>
          <div>
            <h2>{{ $departamento->nombre }}</h2>
            <p>{{ $departamento->descripcion ?: 'Sin descripción' }} · {{ $departamento->areas->count() }} {{ Str::plural('área', $departamento->areas->count()) }}</p>
          </div>
        </div>
        <div class="acciones-celda">
          <x-flechas-orden tipo="departamento" :id="$departamento->id"
                           :primero="$iDep === 0" :ultimo="$iDep === $departamentos->count() - 1"/>
          <button type="button" class="btn btn--sm menu-exp-boton-claro" data-editar="{{ $formDep }}">Editar</button>
          @if ($departamento->areas->isEmpty())
            <form method="POST" action="{{ route('admin.departamentos.destroy', $departamento) }}" data-confirmar="¿Eliminar el departamento «{{ $departamento->nombre }}»?">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn--peligro btn--sm">Eliminar</button>
            </form>
          @endif
        </div>
      </header>

      <div class="fila-edicion fila-edicion--bloque" id="{{ $formDep }}" @if(! $activaDep) hidden @endif>
        <form class="form-edicion" method="POST" action="{{ route('admin.departamentos.update', $departamento) }}">
          @csrf @method('PUT')
          <input type="hidden" name="_form" value="{{ $formDep }}">
          <div class="titulo-edicion">Editando departamento «{{ $departamento->nombre }}»</div>
          @if ($activaDep && $errors->any())<div class="alerta alerta--error" role="alert">{{ $errors->first() }}</div>@endif
          @include('admin.estructura.campos', ['modelo' => $departamento, 'activa' => $activaDep, 'sufijo' => $formDep, 'conDepartamento' => false])
          <div class="form-acciones">
            <button type="button" class="btn btn--fantasma" data-cancelar="{{ $formDep }}">Cancelar</button>
            <button type="submit" class="btn btn--primario">Guardar cambios</button>
          </div>
        </form>
      </div>

      <div class="tabla-scroll">
        <table class="areas-admin tabla-tarjetas">
          <thead><tr><th>Área</th><th>Descripción</th><th>Reportes</th><th>Orden</th><th class="col-acciones">Acciones</th></tr></thead>
          <tbody>
          @forelse ($departamento->areas as $iArea => $area)
            @php $formArea = "area-{$area->id}"; $activaArea = $formActivo === $formArea; @endphp
            <tr>
              <td class="celda-titulo"><div class="nombre con-icono"><x-icono :nombre="$area->icono" :tamano="16"/> {{ $area->nombre }}</div><div class="correo">/bi/{{ $area->slug }}</div></td>
              <td data-etiqueta="Descripción">{{ $area->descripcion ?: '—' }}</td>
              <td data-etiqueta="Reportes"><a href="{{ route('admin.reportes.index', ['area' => $area->id]) }}">{{ $area->reportes_count }} {{ Str::plural('reporte', $area->reportes_count) }}</a></td>
              <td data-etiqueta="Orden">
                <x-flechas-orden tipo="area" :id="$area->id"
                                 :primero="$iArea === 0" :ultimo="$iArea === $departamento->areas->count() - 1"/>
              </td>
              <td class="col-acciones">
                <div class="acciones-celda">
                  <a class="btn btn--fantasma btn--sm btn--icono" href="{{ route('bi.area', $area) }}" title="Ver en el portal"><x-icono nombre="externo" :tamano="14"/></a>
                  <button type="button" class="btn btn--fantasma btn--sm" data-editar="{{ $formArea }}">Editar</button>
                  @if ($area->reportes_count === 0)
                    <form method="POST" action="{{ route('admin.areas.destroy', $area) }}" data-confirmar="¿Eliminar el área «{{ $area->nombre }}»?">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn--peligro btn--sm">Eliminar</button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
            <tr class="fila-edicion" id="{{ $formArea }}" @if(! $activaArea) hidden @endif>
              <td colspan="5">
                <form class="form-edicion" method="POST" action="{{ route('admin.areas.update', $area) }}">
                  @csrf @method('PUT')
                  <input type="hidden" name="_form" value="{{ $formArea }}">
                  <div class="titulo-edicion">Editando área «{{ $area->nombre }}»</div>
                  @if ($activaArea && $errors->any())<div class="alerta alerta--error" role="alert">{{ $errors->first() }}</div>@endif
                  @include('admin.estructura.campos', ['modelo' => $area, 'activa' => $activaArea, 'sufijo' => $formArea, 'conDepartamento' => true])
                  <div class="form-acciones">
                    <button type="button" class="btn btn--fantasma" data-cancelar="{{ $formArea }}">Cancelar</button>
                    <button type="submit" class="btn btn--primario">Guardar cambios</button>
                  </div>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="tabla-vacia">Este departamento todavía no tiene áreas.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </section>
  @endforeach
@endsection
