@props(['tipo', 'id', 'anclado' => false, 'texto' => true])

{{--
  Botón «Anclar a favoritos». Es un formulario real (funciona sin JavaScript);
  base.js lo intercepta para hacerlo por fetch y actualizar el estado en sitio.
--}}
<form method="POST" action="{{ route('favoritos.alternar') }}" class="form-favorito" data-favorito data-tipo="{{ $tipo }}" data-id="{{ $id }}">
  @csrf
  <input type="hidden" name="tipo" value="{{ $tipo }}">
  <input type="hidden" name="id" value="{{ $id }}">
  <button type="submit"
          {{ $attributes->class(['btn', 'btn--fantasma', 'btn-favorito', 'btn-favorito--anclado' => $anclado, 'btn--icono' => ! $texto]) }}
          aria-pressed="{{ $anclado ? 'true' : 'false' }}"
          title="{{ $anclado ? 'Quitar de favoritos' : 'Anclar a favoritos' }}">
    <x-icono nombre="pin" :tamano="15" class="icono-pin"/>
    @if ($texto)
      <span class="btn-favorito-texto">{{ $anclado ? 'Anclado' : 'Anclar a favoritos' }}</span>
    @endif
  </button>
</form>
