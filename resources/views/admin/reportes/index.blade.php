@extends('admin.layout', ['encabezado' => 'Reportes BI', 'bajada' => 'Links de Power BI y quién puede ver cada uno'])

@section('titulo', 'Administrar reportes')

@php
    // Al fallar la validación Laravel vuelve atrás: este campo dice qué
    // formulario hay que reabrir y dónde pintar los errores.
    $formActivo = old('_form', 'alta');
@endphp

@section('admin')
  {{-- ---------- Alta ---------- --}}
  <details class="tarjeta-panel panel-plegable" @if($formActivo === 'alta' && $errors->any()) open @endif>
    <summary>
      <div>
        <h2>Nuevo reporte</h2>
        <p>Pega la URL de inserción (o el código &lt;iframe&gt;) que entrega Power BI en Archivo → Insertar informe</p>
      </div>
      <span class="btn btn--sm menu-exp-boton-claro">Agregar <x-icono nombre="abajo" :tamano="14"/></span>
    </summary>

    <form class="form-alta" method="POST" action="{{ route('admin.reportes.store') }}">
      @csrf
      <input type="hidden" name="_form" value="alta">

      @if ($formActivo === 'alta' && $errors->any())
        <div class="alerta alerta--error" role="alert">Revisa los datos: {{ $errors->first() }}</div>
      @endif

      @include('admin.reportes.campos', ['reporte' => null, 'activa' => $formActivo === 'alta', 'sufijo' => 'nuevo'])

      <div class="form-acciones">
        <button type="button" class="btn btn--fantasma" data-limpiar>Limpiar</button>
        <button type="submit" class="btn btn--primario">Crear reporte</button>
      </div>
    </form>
  </details>

  {{-- ---------- Listado ---------- --}}
  <section class="tarjeta-panel">
    <header>
      <div>
        <h2>Reportes registrados</h2>
        <p>Por departamento y área. Las flechas mueven el reporte dentro de su área.</p>
      </div>
    </header>

    <form class="barra-filtros" method="GET" action="{{ route('admin.reportes.index') }}">
      <div class="filtros-rejilla">
        <div class="campo campo--filtro campo--ancho2">
          <label for="f-q">Buscar</label>
          <input id="f-q" name="q" type="search" value="{{ $filtros['q'] }}" placeholder="Título del reporte">
        </div>
        <div class="campo campo--filtro">
          <label for="f-area">Área</label>
          <select id="f-area" name="area">
            <option value="">Todas</option>
            @foreach ($departamentos as $d)
              <optgroup label="{{ $d->nombre }}">
                @foreach ($d->areas as $a)
                  <option value="{{ $a->id }}" @selected($filtros['area'] === $a->id)>{{ $a->nombre }}</option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
        </div>
        <div class="campo campo--filtro">
          <label for="f-tipo">Tipo</label>
          <select id="f-tipo" name="tipo">
            <option value="">Todos</option>
            @foreach ($tipos as $valor => $etiqueta)
              <option value="{{ $valor }}" @selected($filtros['tipo'] === $valor)>{{ $etiqueta }}</option>
            @endforeach
          </select>
        </div>
        <div class="campo campo--filtro filtros-acciones">
          <a class="btn btn--fantasma" href="{{ route('admin.reportes.index') }}">Limpiar</a>
          <button type="submit" class="btn btn--primario">Filtrar</button>
        </div>
      </div>
    </form>

    <div class="tabla-resumen">
      <span><strong>{{ $reportes->total() }}</strong> {{ Str::plural('reporte', $reportes->total()) }}</span>
      <span class="tabla-resumen-nota">Los marcados «muestra» todavía no tienen su URL real de Power BI</span>
    </div>

    <div class="tabla-scroll">
      <table class="reportes tabla-tarjetas">
        <thead>
          <tr>
            <th>Reporte</th>
            <th>Área</th>
            <th>Tipo</th>
            <th>Orden</th>
            <th>Visibilidad</th>
            <th>Estado</th>
            <th class="col-acciones">Acciones</th>
          </tr>
        </thead>
        <tbody>
        @forelse ($reportes as $reporte)
          @php
            $formEdicion = "edicion-{$reporte->id}";
            $formAccesos = "accesos-{$reporte->id}";
            $activa = $formActivo === $formEdicion;
            $accesosActivo = $formActivo === $formAccesos;
          @endphp
          <tr @class(['fila-inactiva' => ! $reporte->activo])>
            <td class="celda-titulo">
              <div class="nombre">{{ $reporte->titulo }}
                @if ($reporte->esDemo())<span class="sin-acceso" title="Tablero de muestra">muestra</span>@endif
              </div>
              <div class="correo url-corta" title="{{ $reporte->url_iframe }}">{{ $reporte->esDemo() ? 'Tablero de ejemplo del sistema' : Str::limit($reporte->url_iframe, 70) }}</div>
            </td>
            <td data-etiqueta="Área">
              <b>{{ $reporte->area->nombre }}</b>
              <div class="correo">{{ $reporte->area->departamento->nombre }}</div>
            </td>
            <td data-etiqueta="Tipo"><span class="tipo tipo--{{ $reporte->tipo->value }}">{{ $reporte->tipo->etiqueta() }}</span></td>
            <td data-etiqueta="Orden">
              <x-flechas-orden tipo="reporte" :id="$reporte->id"/>
            </td>
            <td data-etiqueta="Visibilidad">
              @if ($reporte->publico)
                <span class="pastilla pastilla--publico">Todos los usuarios</span>
              @else
                <span class="pastilla">{{ $reporte->usuarios->count() }} {{ Str::plural('usuario', $reporte->usuarios->count()) }}</span>
              @endif
            </td>
            <td data-etiqueta="Estado">
              <span class="pastilla {{ $reporte->activo ? 'pastilla--activo' : 'pastilla--inactivo' }}">{{ $reporte->activo ? 'Activo' : 'Inactivo' }}</span>
            </td>
            <td class="col-acciones">
              <div class="acciones-celda">
                <a class="btn btn--fantasma btn--sm btn--icono" href="{{ route('bi.area', [$reporte->area, '#reporte-'.$reporte->id]) }}" title="Ver en el portal"><x-icono nombre="externo" :tamano="14"/></a>
                <button type="button" class="btn btn--fantasma btn--sm" data-editar="{{ $formAccesos }}">Accesos</button>
                <button type="button" class="btn btn--fantasma btn--sm" data-editar="{{ $formEdicion }}">Editar</button>
                <form method="POST" action="{{ route('admin.reportes.destroy', $reporte) }}"
                      data-confirmar="¿Eliminar el reporte «{{ $reporte->titulo }}»? Esta acción no se puede deshacer.">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn--peligro btn--sm">Eliminar</button>
                </form>
              </div>
            </td>
          </tr>

          {{-- Edición --}}
          <tr class="fila-edicion" id="{{ $formEdicion }}" @if(! $activa) hidden @endif>
            <td colspan="7">
              <form class="form-edicion" method="POST" action="{{ route('admin.reportes.update', $reporte) }}">
                @csrf @method('PUT')
                <input type="hidden" name="_form" value="{{ $formEdicion }}">
                <div class="titulo-edicion">Editando «{{ $reporte->titulo }}»</div>
                @if ($activa && $errors->any())
                  <div class="alerta alerta--error" role="alert">{{ $errors->first() }}</div>
                @endif
                @include('admin.reportes.campos', ['reporte' => $reporte, 'activa' => $activa, 'sufijo' => $reporte->id])
                <div class="form-acciones">
                  <button type="button" class="btn btn--fantasma" data-cancelar="{{ $formEdicion }}">Cancelar</button>
                  <button type="submit" class="btn btn--primario">Guardar cambios</button>
                </div>
              </form>
            </td>
          </tr>

          {{-- Accesos de vista --}}
          <tr class="fila-edicion" id="{{ $formAccesos }}" @if(! $accesosActivo) hidden @endif>
            <td colspan="7">
              <form class="form-edicion" method="POST" action="{{ route('admin.reportes.accesos', $reporte) }}">
                @csrf @method('PUT')
                <input type="hidden" name="_form" value="{{ $formAccesos }}">
                <div class="titulo-edicion">Quién puede ver «{{ $reporte->titulo }}»</div>

                @if ($reporte->publico)
                  <p class="campo-nota">Este reporte es <b>público</b>: lo ve cualquier usuario con sesión. La lista de abajo solo aplica si lo desmarcas como público.</p>
                @else
                  <p class="campo-nota">Solo los usuarios marcados (y los administradores) verán este tablero. Los que agregues recibirán una notificación.</p>
                @endif

                <div class="accesos-buscar">
                  <x-icono nombre="buscar" :tamano="15"/>
                  <input type="search" placeholder="Filtrar usuarios…" data-filtrar-casillas aria-label="Filtrar usuarios">
                  <button type="button" class="enlace-suave" data-marcar="todos">Todos</button>
                  <button type="button" class="enlace-suave" data-marcar="ninguno">Ninguno</button>
                </div>

                @php $conAcceso = $reporte->usuarios->pluck('id')->all(); @endphp
                <div class="casillas casillas--usuarios">
                  @foreach ($usuarios as $u)
                    <label class="casilla" data-texto="{{ Str::lower($u->name.' '.$u->email) }}">
                      <input type="checkbox" name="usuarios[]" value="{{ $u->id }}"
                             @checked(in_array($u->id, $conAcceso, true) || $u->esAdministrador())
                             @disabled($u->esAdministrador())>
                      <span>{{ $u->name }} <small>{{ $u->email }}@if ($u->esAdministrador()) · administrador @endif</small></span>
                    </label>
                  @endforeach
                </div>

                <div class="form-acciones">
                  <button type="button" class="btn btn--fantasma" data-cancelar="{{ $formAccesos }}">Cancelar</button>
                  <button type="submit" class="btn btn--primario">Guardar accesos</button>
                </div>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="tabla-vacia">Ningún reporte coincide con el filtro.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    {{ $reportes->links() }}
  </section>
@endsection
