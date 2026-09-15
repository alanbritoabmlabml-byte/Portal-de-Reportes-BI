/**
 * Pantallas de administración.
 *
 * - data-editar="id"   abre la fila de edición con ese id (y cierra las demás)
 * - data-cancelar="id" la cierra
 * - data-filtrar-casillas filtra la lista de usuarios de los accesos de vista
 * - data-marcar="todos|ninguno" marca o desmarca las casillas visibles
 */
(function () {
  'use strict';

  document.addEventListener('click', function (e) {
    var abrir = e.target.closest('[data-editar]');
    if (abrir) {
      var id = abrir.dataset.editar;
      document.querySelectorAll('.fila-edicion:not([hidden])').forEach(function (f) { if (f.id !== id) f.hidden = true; });
      var fila = document.getElementById(id);
      if (fila) {
        fila.hidden = !fila.hidden;
        if (!fila.hidden) {
          fila.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          var primero = fila.querySelector('input:not([type=hidden]):not([disabled]), select, textarea');
          if (primero) primero.focus({ preventScroll: true });
        }
      }
      return;
    }

    var cerrar = e.target.closest('[data-cancelar]');
    if (cerrar) {
      var f = document.getElementById(cerrar.dataset.cancelar);
      if (f) f.hidden = true;
      return;
    }

    var marcar = e.target.closest('[data-marcar]');
    if (marcar) {
      var form = marcar.closest('form');
      form.querySelectorAll('.casilla:not([hidden]) input[type=checkbox]:not([disabled])').forEach(function (c) {
        c.checked = marcar.dataset.marcar === 'todos';
      });
    }
  });

  document.addEventListener('input', function (e) {
    var buscador = e.target.closest('[data-filtrar-casillas]');
    if (!buscador) return;
    var q = buscador.value.trim().toLowerCase();
    buscador.closest('form').querySelectorAll('.casilla[data-texto]').forEach(function (c) {
      c.hidden = !!q && c.dataset.texto.indexOf(q) === -1;
    });
  });

  // Si la página vuelve con una fila de edición abierta por error de
  // validación, se lleva la vista hasta ella.
  var abierta = document.querySelector('.fila-edicion:not([hidden])');
  if (abierta) abierta.scrollIntoView({ block: 'center' });
})();
