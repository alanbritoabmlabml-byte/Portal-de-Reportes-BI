@props(['tipo', 'id', 'primero' => false, 'ultimo' => false])

{{-- Subir / bajar un elemento dentro de su grupo. Sustituye al campo «orden». --}}
<div class="flechas-orden" role="group" aria-label="Cambiar el orden">
  <form method="POST" action="{{ route('admin.orden.mover', [$tipo, $id, 'arriba']) }}">
    @csrf @method('PUT')
    <button type="submit" class="flecha-orden" title="Subir" aria-label="Subir" @disabled($primero)>
      <x-icono nombre="subir" :tamano="14"/>
    </button>
  </form>
  <form method="POST" action="{{ route('admin.orden.mover', [$tipo, $id, 'abajo']) }}">
    @csrf @method('PUT')
    <button type="submit" class="flecha-orden" title="Bajar" aria-label="Bajar" @disabled($ultimo)>
      <x-icono nombre="bajar" :tamano="14"/>
    </button>
  </form>
</div>
