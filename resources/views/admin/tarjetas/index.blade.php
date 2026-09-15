@extends('admin.layout', ['encabezado' => 'Tarjetas del menú', 'bajada' => 'Accesos a otros sitios que se muestran en el menú principal'])

@section('titulo', 'Administrar tarjetas')

@php $formActivo = old('_form', 'alta'); @endphp

@section('admin')
  <details class="tarjeta-panel panel-plegable" @if($formActivo === 'alta' && $errors->any()) open @endif>
    <summary>
      <div>
        <h2>Nueva tarjeta</h2>
        <p>La tarjeta de Reportes BI se arma sola a partir de departamentos y áreas; aquí van los demás accesos</p>
      </div>
      <span class="btn btn--sm menu-exp-boton-claro">Agregar <x-icono nombre="abajo" :tamano="14"/></span>
    </summary>
    <form class="form-alta" method="POST" action="{{ route('admin.tarjetas.store') }}">
      @csrf
      <input type="hidden" name="_form" value="alta">
      @if ($formActivo === 'alta' && $errors->any())
        <div class="alerta alerta--error" role="alert">Revisa los datos: {{ $errors->first() }}</div>
      @endif
      @include('admin.tarjetas.campos', ['acceso' => null, 'activa' => $formActivo === 'alta', 'sufijo' => 'nuevo'])
      <div class="form-acciones">
        <button type="button" class="btn btn--fantasma" data-limpiar>Limpiar</button>
        <button type="submit" class="btn btn--primario">Crear tarjeta</button>
      </div>
    </form>
  </details>

  @foreach ($grupos as $grupo)
    @php $delGrupo = $tarjetas[$grupo->value] ?? collect(); @endphp
    <section class="tarjeta-panel">
      <header>
        <div>
          <h2><x-icono :nombre="$grupo->icono()" :tamano="17"/> {{ $grupo->etiqueta() }}</h2>
          <p>{{ $grupo->bajada() }} · {{ $delGrupo->count() }} {{ Str::plural('tarjeta', $delGrupo->count()) }}</p>
        </div>
      </header>

      <div class="tabla-scroll">
        <table class="tarjetas-admin tabla-tarjetas">
          <thead>
            <tr>
              <th>Vista</th>
              <th>Tarjeta</th>
              <th>Etiqueta</th>
              <th>Orden</th>
              <th>Estado</th>
              <th class="col-acciones">Acciones</th>
            </tr>
          </thead>
          <tbody>
          @forelse ($delGrupo as $indice => $acceso)
            @php $formEdicion = "edicion-{$acceso->id}"; $activa = $formActivo === $formEdicion; @endphp
            <tr @class(['fila-inactiva' => ! $acceso->activo])>
              <td class="col-vista" data-etiqueta="Vista"><div class="mini-vista"><x-vista-previa :acceso="$acceso"/></div></td>
              <td class="celda-titulo">
                <div class="nombre">{{ $acceso->nombre }} @if ($acceso->destacado)<span class="sin-acceso">destacada</span>@endif</div>
                <div class="correo"><a href="{{ $acceso->url }}" target="_blank" rel="noopener">{{ Str::limit($acceso->url, 60) }}</a></div>
              </td>
              <td data-etiqueta="Etiqueta">{{ $acceso->etiqueta ?: '—' }}</td>
              <td data-etiqueta="Orden">
                <x-flechas-orden tipo="tarjeta" :id="$acceso->id"
                                 :primero="$indice === 0" :ultimo="$indice === $delGrupo->count() - 1"/>
              </td>
              <td data-etiqueta="Estado"><span class="pastilla {{ $acceso->activo ? 'pastilla--activo' : 'pastilla--inactivo' }}">{{ $acceso->activo ? 'Activa' : 'Oculta' }}</span></td>
              <td class="col-acciones">
                <div class="acciones-celda">
                  <button type="button" class="btn btn--fantasma btn--sm" data-editar="{{ $formEdicion }}">Editar</button>
                  <form method="POST" action="{{ route('admin.tarjetas.destroy', $acceso) }}"
                        data-confirmar="¿Eliminar la tarjeta «{{ $acceso->nombre }}»?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn--peligro btn--sm">Eliminar</button>
                  </form>
                </div>
              </td>
            </tr>
            <tr class="fila-edicion" id="{{ $formEdicion }}" @if(! $activa) hidden @endif>
              <td colspan="6">
                <form class="form-edicion" method="POST" action="{{ route('admin.tarjetas.update', $acceso) }}">
                  @csrf @method('PUT')
                  <input type="hidden" name="_form" value="{{ $formEdicion }}">
                  <div class="titulo-edicion">Editando «{{ $acceso->nombre }}»</div>
                  @if ($activa && $errors->any())
                    <div class="alerta alerta--error" role="alert">{{ $errors->first() }}</div>
                  @endif
                  @include('admin.tarjetas.campos', ['acceso' => $acceso, 'activa' => $activa, 'sufijo' => $acceso->id])
                  <div class="form-acciones">
                    <button type="button" class="btn btn--fantasma" data-cancelar="{{ $formEdicion }}">Cancelar</button>
                    <button type="submit" class="btn btn--primario">Guardar cambios</button>
                  </div>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="tabla-vacia">Todavía no hay tarjetas en este mosaico.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </section>
  @endforeach
@endsection
