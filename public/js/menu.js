/**
 * Menú principal.
 *
 * - Búsqueda instantánea sobre las tarjetas (atajo «/»).
 * - Pestañas de departamento para las tarjetas de área.
 * - Inclinación 3D suave de las tarjetas con el puntero.
 * - El riel lateral de favoritos se actualiza en sitio al anclar/desanclar.
 */
(function () {
  'use strict';

  var TOPE = parseInt((document.querySelector('[data-riel]') || {}).dataset ? document.querySelector('[data-riel]').dataset.tope : '', 10) || 6;

  // ---------- Búsqueda ----------
  var buscador = document.getElementById('buscar-tarjetas');
  var sinResultados = document.getElementById('sin-resultados');
  var tarjetas = Array.prototype.slice.call(document.querySelectorAll('[data-tarjeta]'));
  var secciones = Array.prototype.slice.call(document.querySelectorAll('.seccion'));
  var mosaicos = Array.prototype.slice.call(document.querySelectorAll('[data-mosaico]'));

  function normalizar(s) {
    return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
  }

  function filtrar() {
    var q = normalizar(buscador.value.trim());
    var visibles = 0;

    tarjetas.forEach(function (t) {
      var coincide = !q || normalizar(t.dataset.buscar).indexOf(q) > -1;
      // La pestaña activa manda cuando no hay búsqueda
      if (!q && t.dataset.departamento && pestanaActiva !== 'todos') {
        coincide = t.dataset.departamento === pestanaActiva;
      }
      t.hidden = !coincide;
      if (coincide) visibles++;
    });

    // Un mosaico sin tarjetas visibles estorba: se oculta mientras dure la búsqueda
    mosaicos.forEach(function (m) {
      var alguna = m.querySelector('[data-tarjeta]:not([hidden])');
      m.hidden = !!q && !alguna;
      if (q && alguna) m.open = true;
    });

    // Secciones sin ninguna tarjeta visible se ocultan durante la búsqueda
    secciones.forEach(function (s) {
      var alguna = s.querySelector('[data-tarjeta]:not([hidden])');
      s.hidden = !!q && !alguna;
    });

    if (sinResultados) sinResultados.hidden = !(q && visibles === 0);
  }

  if (buscador) {
    buscador.addEventListener('input', filtrar);
    document.addEventListener('keydown', function (e) {
      if (e.key === '/' && document.activeElement !== buscador && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName)) {
        e.preventDefault();
        buscador.focus();
      }
      if (e.key === 'Escape' && document.activeElement === buscador) {
        buscador.value = '';
        filtrar();
        buscador.blur();
      }
    });
  }

  // ---------- Pestañas ----------
  var pestanaActiva = 'todos';
  var pestanas = document.querySelectorAll('[data-pestana]');
  pestanas.forEach(function (p) {
    p.addEventListener('click', function () {
      pestanaActiva = p.dataset.pestana;
      pestanas.forEach(function (o) {
        var activa = o === p;
        o.classList.toggle('pestana--activa', activa);
        o.setAttribute('aria-selected', String(activa));
      });
      if (buscador && buscador.value) { buscador.value = ''; }
      filtrar();
    });
  });

  // ---------- Inclinación 3D ----------
  var finoConHover = window.matchMedia('(hover:hover) and (pointer:fine)').matches;
  var sinMovimiento = window.matchMedia('(prefers-reduced-motion:reduce)').matches;
  if (finoConHover && !sinMovimiento) {
    document.addEventListener('mousemove', function (e) {
      var t = e.target.closest('[data-tilt]');
      if (!t) return;
      var r = t.getBoundingClientRect();
      var x = (e.clientX - r.left) / r.width - 0.5;
      var y = (e.clientY - r.top) / r.height - 0.5;
      t.style.setProperty('--ry', (x * 6).toFixed(2) + 'deg');
      t.style.setProperty('--rx', (-y * 6).toFixed(2) + 'deg');
    });
    document.addEventListener('mouseout', function (e) {
      var t = e.target.closest('[data-tilt]');
      if (t && !t.contains(e.relatedTarget)) {
        t.style.setProperty('--ry', '0deg');
        t.style.setProperty('--rx', '0deg');
      }
    });
  }

  // ---------- Riel de favoritos ----------
  var riel = document.querySelector('[data-riel]');
  var lista = document.querySelector('[data-riel-lista]');
  var cuenta = document.querySelector('[data-riel-cuenta]');
  var vacio = document.querySelector('[data-riel-vacio]');
  var pie = document.querySelector('[data-riel-pie]');
  var contadorFav = document.querySelector('a.cifra[href="#favoritos"] b');

  var ICONO_EXTERNO = '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4h6v6M20 4 10 14M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>';
  var ICONO_FLECHA = '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h13M13 6l6 6-6 6"/></svg>';
  var ICONO_ENLACE = '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/></svg>';
  var ICONO_TABLERO = '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 15v-3M12 15V9M17 15v-5"/></svg>';
  var ICONO_CERRAR = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';

  function csrf() {
    return (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  }

  /** Datos que la tarjeta de origen expone para poder dibujar la fila del riel. */
  function datosDeTarjeta(tarjeta, tipo) {
    if (!tarjeta) return null;
    var icono = tarjeta.querySelector('[data-fav-icono] svg');
    return {
      nombre: tarjeta.dataset.favNombre || 'Favorito',
      url: tarjeta.dataset.favUrl || '#',
      nueva: tarjeta.hasAttribute('data-fav-nueva'),
      icono: icono ? icono.outerHTML : (tipo === 'acceso' ? ICONO_ENLACE : ICONO_TABLERO)
    };
  }

  function crearFila(tipo, id, datos, accion) {
    var li = document.createElement('li');
    li.setAttribute('data-fav-tipo', tipo);
    li.setAttribute('data-fav-id', id);

    var a = document.createElement('a');
    a.className = 'riel-item';
    a.href = datos.url;
    if (datos.nueva) { a.target = '_blank'; a.rel = 'noopener'; }
    a.innerHTML = '<span class="riel-icono">' + datos.icono + '</span>' +
      '<span class="riel-nombre"></span>' +
      (tipo === 'acceso' ? ICONO_EXTERNO : ICONO_FLECHA);
    a.querySelector('.riel-nombre').textContent = datos.nombre;

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = accion;
    form.className = 'riel-quitar-form';
    form.setAttribute('data-favorito', '');
    form.setAttribute('data-tipo', tipo);
    form.setAttribute('data-id', id);
    form.innerHTML = '<input type="hidden" name="_token" value="' + csrf() + '">' +
      '<input type="hidden" name="tipo" value="' + tipo + '">' +
      '<input type="hidden" name="id" value="' + id + '">' +
      '<button type="submit" class="riel-quitar" title="Quitar de favoritos">' + ICONO_CERRAR + '</button>';
    form.querySelector('button').setAttribute('aria-label', 'Quitar ' + datos.nombre + ' de favoritos');

    li.appendChild(a);
    li.appendChild(form);
    return li;
  }

  function refrescarRiel() {
    if (!lista) return;
    var n = lista.children.length;
    lista.hidden = n === 0;
    if (vacio) vacio.hidden = n > 0;
    if (cuenta) cuenta.textContent = n + '/' + TOPE;
    if (pie) pie.textContent = n + ' de ' + TOPE + ' espacios usados';
    if (riel) riel.toggleAttribute('data-lleno', n >= TOPE);
    if (contadorFav) contadorFav.textContent = n;
  }

  document.addEventListener('pc:favorito', function (e) {
    if (!lista) return;
    var d = e.detail;
    var existente = lista.querySelector('[data-fav-tipo="' + d.tipo + '"][data-fav-id="' + d.id + '"]');

    if (!d.anclado) {
      if (existente) existente.remove();
    } else if (!existente) {
      var origen = d.form.closest('[data-tarjeta]');
      var datos = datosDeTarjeta(origen, d.tipo);
      if (datos) {
        lista.appendChild(crearFila(d.tipo, d.id, datos, d.form.action));
        if (riel) {
          var panel = riel.querySelector('.riel-panel');
          if (panel) panel.open = true;
        }
      }
    }

    refrescarRiel();
  });

  refrescarRiel();
})();
