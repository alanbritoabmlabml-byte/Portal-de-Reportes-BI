@extends('admin.layout', ['encabezado' => 'Usuarios', 'bajada' => 'Altas, roles y accesos de vista'])

@section('titulo', 'Administrar usuarios')

@php $formActivo = old('_form', 'alta'); @endphp

@section('admin')
  <div class="alerta">
    <b>Base de usuarios provisional.</b> Cuando se cierre la versión se cargará la base definitiva sobre esta misma tabla; mientras, aquí se administran las cuentas de prueba.
  </div>

  <details class="tarjeta-panel panel-plegable" @if($formActivo === 'alta' && $errors->any()) open @endif>
    <summary>
      <div>
        <h2>Nuevo usuario</h2>
        <p>El rol define si administra o solo consulta</p>
      </div>
      <span class="btn btn--sm menu-exp-boton-claro">Agregar <x-icono nombre="abajo" :tamano="14"/></span>
    </summary>

    <form class="form-alta" method="POST" action="{{ route('admin.usuarios.store') }}">
      @csrf
      <input type="hidden" name="_form" value="alta">
      @if ($formActivo === 'alta' && $errors->any())
        <div class="alerta alerta--error" role="alert">Revisa los datos: {{ $errors->first() }}</div>
      @endif
      @include('admin.usuarios.campos', ['usuario' => null, 'activa' => $formActivo === 'alta', 'sufijo' => 'nuevo'])
      <div class="form-acciones">
        <button type="button" class="btn btn--fantasma" data-limpiar>Limpiar</button>
        <button type="submit" class="btn btn--primario">Crear usuario</button>
      </div>
    </form>
  </details>

  {{-- ---------- Aviso a todos ---------- --}}
  <details class="tarjeta-panel panel-plegable" @if($formActivo === 'aviso' && $errors->any()) open @endif>
    <summary>
      <div>
        <h2>Enviar un aviso</h2>
        <p>Llega a la campana de todos los usuarios activos</p>
      </div>
      <span class="btn btn--sm menu-exp-boton-claro">Redactar <x-icono nombre="abajo" :tamano="14"/></span>
    </summary>
    <form class="form-alta" method="POST" action="{{ route('admin.notificaciones.store') }}">
      @csrf
      <input type="hidden" name="_form" value="aviso">
      @if ($formActivo === 'aviso' && $errors->any())
        <div class="alerta alerta--error" role="alert">{{ $errors->first() }}</div>
      @endif
      <div class="form-rejilla">
        <div class="campo">
          <label for="aviso-titulo">Título</label>
          <input id="aviso-titulo" name="titulo" type="text" maxlength="120" required value="{{ $formActivo === 'aviso' ? old('titulo') : '' }}">
        </div>
        <div class="campo">
          <label for="aviso-tipo">Tipo</label>
          <select id="aviso-tipo" name="tipo">
            @foreach (\App\Enums\TipoNotificacion::opciones() as $valor => $etiqueta)
              <option value="{{ $valor }}" @selected(($formActivo === 'aviso' ? old('tipo') : 'info') === $valor)>{{ $etiqueta }}</option>
            @endforeach
          </select>
        </div>
        <div class="campo ancho-total">
          <label for="aviso-mensaje">Mensaje</label>
          <textarea id="aviso-mensaje" name="mensaje" rows="2" maxlength="500">{{ $formActivo === 'aviso' ? old('mensaje') : '' }}</textarea>
        </div>
        <div class="campo ancho-total">
          <label for="aviso-url">Enlace (opcional)</label>
          <input id="aviso-url" name="url" type="url" maxlength="500" placeholder="https://…" value="{{ $formActivo === 'aviso' ? old('url') : '' }}">
        </div>
      </div>
      <div class="form-acciones">
        <button type="submit" class="btn btn--primario">Enviar a todos</button>
      </div>
    </form>
  </details>

  <section class="tarjeta-panel">
    <header>
      <div>
        <h2>Usuarios registrados</h2>
        <p>Ordenados por nombre</p>
      </div>
    </header>

    <form class="barra-filtros" method="GET" action="{{ route('admin.usuarios.index') }}">
      <div class="filtros-rejilla">
        <div class="campo campo--filtro campo--ancho2">
          <label for="f-q">Buscar</label>
          <input id="f-q" name="q" type="search" value="{{ $filtros['q'] }}" placeholder="Nombre o correo">
        </div>
        <div class="campo campo--filtro">
          <label for="f-rol">Rol</label>
          <select id="f-rol" name="rol">
            <option value="">Todos</option>
            @foreach ($roles as $valor => $etiqueta)
              <option value="{{ $valor }}" @selected($filtros['rol'] === $valor)>{{ $etiqueta }}</option>
            @endforeach
          </select>
        </div>
        <div class="campo campo--filtro filtros-acciones">
          <a class="btn btn--fantasma" href="{{ route('admin.usuarios.index') }}">Limpiar</a>
          <button type="submit" class="btn btn--primario">Filtrar</button>
        </div>
      </div>
    </form>

    <div class="tabla-resumen">
      <span><strong>{{ $usuarios->total() }}</strong> {{ Str::plural('cuenta', $usuarios->total()) }}</span>
    </div>

    <div class="tabla-scroll">
      <table class="usuarios tabla-tarjetas">
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Reportes asignados</th>
            <th>Estado</th>
            <th>Alta</th>
            <th class="col-acciones">Acciones</th>
          </tr>
        </thead>
        <tbody>
        @forelse ($usuarios as $usuario)
          @php $formEdicion = "edicion-{$usuario->id}"; $activa = $formActivo === $formEdicion; @endphp
          <tr @class(['fila-inactiva' => ! $usuario->activo])>
            <td class="celda-titulo">
              <div class="nombre">{{ $usuario->name }} @if ($usuario->is(auth()->user()))<span class="yo">tú</span>@endif</div>
              <div class="correo">{{ $usuario->email }}</div>
            </td>
            <td data-etiqueta="Rol"><span class="rol rol--{{ $usuario->rol->value }}">{{ $usuario->rol->etiqueta() }}</span></td>
            <td data-etiqueta="Reportes">
              @if ($usuario->esAdministrador())
                <span class="correo">Todos (administra)</span>
              @else
                {{ $usuario->reportes_count }} {{ Str::plural('reporte', $usuario->reportes_count) }} + públicos
              @endif
            </td>
            <td data-etiqueta="Estado"><span class="pastilla {{ $usuario->activo ? 'pastilla--activo' : 'pastilla--inactivo' }}">{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</span></td>
            <td class="correo" data-etiqueta="Alta">{{ $usuario->created_at?->format('d/m/Y') ?? '—' }}</td>
            <td class="col-acciones">
              <div class="acciones-celda">
                <button type="button" class="btn btn--fantasma btn--sm" data-editar="{{ $formEdicion }}">Editar</button>
                @unless ($usuario->is(auth()->user()))
                  <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}"
                        data-confirmar="¿Eliminar a {{ $usuario->name }}? Esta acción no se puede deshacer.">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn--peligro btn--sm">Eliminar</button>
                  </form>
                @endunless
              </div>
            </td>
          </tr>
          <tr class="fila-edicion" id="{{ $formEdicion }}" @if(! $activa) hidden @endif>
            <td colspan="6">
              <form class="form-edicion" method="POST" action="{{ route('admin.usuarios.update', $usuario) }}">
                @csrf @method('PUT')
                <input type="hidden" name="_form" value="{{ $formEdicion }}">
                <div class="titulo-edicion">Editando a {{ $usuario->name }}</div>
                @if ($activa && $errors->any())
                  <div class="alerta alerta--error" role="alert">{{ $errors->first() }}</div>
                @endif
                @include('admin.usuarios.campos', ['usuario' => $usuario, 'activa' => $activa, 'sufijo' => $usuario->id])
                <div class="form-acciones">
                  <button type="button" class="btn btn--fantasma" data-cancelar="{{ $formEdicion }}">Cancelar</button>
                  <button type="submit" class="btn btn--primario">Guardar cambios</button>
                </div>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="tabla-vacia">Ninguna cuenta coincide con el filtro.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    {{ $usuarios->links() }}
  </section>
@endsection
