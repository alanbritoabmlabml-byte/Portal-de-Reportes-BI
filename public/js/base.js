/**
 * Comportamiento común a todas las páginas con sesión.
 *
 * - Avisos flotantes (toasts).
 * - Campana y cuenta: cerrar una al abrir la otra, y al hacer clic fuera.
 * - Favoritos por fetch (los formularios funcionan igual sin JS).
 * - Notificaciones: marcar leída por fetch y seguir al enlace si lo hay.
 *
 * Sin dependencias. Todo se engancha por atributos data-* del marcado.
 */
(function () {
  'use strict';

  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

  // ---------- Toasts ----------
  var ICONOS = {
    info: '<svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>',
    exito: '<svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>',
    error: '<svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 3 2 21h20zM12 10v5M12 18h.01"/></svg>'
  };

  function toast(texto, tipo) {
    var cont = document.getElementById('toasts');
    if (!cont || !texto) return;
    tipo = tipo || 'info';
    var el = document.createElement('div');
    el.className = 'toast toast--' + tipo;
    el.setAttribute('role', 'status');
    el.innerHTML = (ICONOS[tipo] || ICONOS.info) + '<span></span>';
    el.querySelector('span').textContent = texto;
    cont.appendChild(el);
    setTimeout(function () {
      el.classList.add('toast--saliendo');
      setTimeout(function () { el.remove(); }, 300);
    }, 3600);
  }
  window.PC = window.PC || {};
  window.PC.toast = toast;

  if (window.__aviso) toast(window.__aviso, 'exito');
  if (window.__avisoError) toast(window.__avisoError, 'error');

  // ---------- Desplegables del encabezado ----------
  var desplegables = document.querySelectorAll('details.campana, details.cuenta');
  desplegables.forEach(function (d) {
    d.addEventListener('toggle', function () {
      if (!d.open) return;
      desplegables.forEach(function (otro) { if (otro !== d) otro.open = false; });
    });
  });
  document.addEventListener('click', function (e) {
    desplegables.forEach(function (d) {
      if (d.open && !d.contains(e.target)) d.open = false;
    });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') desplegables.forEach(function (d) { d.open = false; });
  });

  // ---------- Apariencia (claro / oscuro / sistema) ----------
  var RAIZ = document.documentElement;

  function aplicarTema(pref) {
    var oscuro = pref === 'oscuro' ||
      (pref === 'sistema' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
    RAIZ.dataset.tema = oscuro ? 'oscuro' : 'claro';
    RAIZ.dataset.temaPref = pref;
    try { localStorage.setItem('pc-tema', pref); } catch (e) {}
  }

  var SIGUIENTE = { claro: 'oscuro', oscuro: 'sistema', sistema: 'claro' };
  var NOMBRE = { claro: 'Claro', oscuro: 'Oscuro', sistema: 'Según el sistema' };

  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-tema-form]');
    if (!form) return;
    e.preventDefault();

    var campo = form.querySelector('input[name=tema]');
    var pref = campo.value;
    aplicarTema(pref);

    // El botón queda listo para el siguiente paso del ciclo
    campo.value = SIGUIENTE[pref] || 'claro';
    var boton = form.querySelector('button');
    if (boton) {
      boton.title = 'Apariencia: ' + NOMBRE[pref];
      boton.setAttribute('aria-label', 'Cambiar apariencia (ahora: ' + NOMBRE[pref] + ')');
    }
    toast('Apariencia: ' + NOMBRE[pref], 'info');

    enviar(form, { tema: pref }).catch(function () {});
  });

  // Con «según el sistema» se sigue al vuelo el cambio del sistema operativo
  if (window.matchMedia) {
    var consulta = window.matchMedia('(prefers-color-scheme: dark)');
    var alCambiar = function () {
      if ((RAIZ.dataset.temaPref || 'sistema') === 'sistema') aplicarTema('sistema');
    };
    if (consulta.addEventListener) consulta.addEventListener('change', alCambiar);
    else if (consulta.addListener) consulta.addListener(alCambiar);
  }

  // ---------- Peticiones ----------
  function enviar(form, extra) {
    var datos = new FormData(form);
    Object.keys(extra || {}).forEach(function (k) { datos.set(k, extra[k]); });
    return fetch(form.action, {
      method: form.getAttribute('method') || 'POST',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: datos,
      credentials: 'same-origin'
    }).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (datos) {
        if (!r.ok) {
          var error = new Error('HTTP ' + r.status);
          error.datos = datos;
          error.estado = r.status;
          throw error;
        }
        return datos;
      });
    });
  }

  // ---------- Favoritos ----------
  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-favorito]');
    if (!form) return;
    e.preventDefault();
    var boton = form.querySelector('button');
    boton.disabled = true;

    enviar(form).then(function (r) {
      var tipo = form.dataset.tipo, id = form.dataset.id;
      // Todos los botones del mismo elemento (rejilla y carrusel) cambian juntos
      document.querySelectorAll('form[data-favorito][data-tipo="' + tipo + '"][data-id="' + id + '"] button').forEach(function (b) {
        b.classList.toggle('btn-favorito--anclado', r.anclado);
        b.setAttribute('aria-pressed', String(r.anclado));
        b.title = r.anclado ? 'Quitar de favoritos' : 'Anclar a favoritos';
        var t = b.querySelector('.btn-favorito-texto');
        if (t) t.textContent = r.anclado ? 'Anclado' : 'Anclar a favoritos';
      });
      toast(r.mensaje, r.anclado ? 'exito' : 'info');
      document.dispatchEvent(new CustomEvent('pc:favorito', { detail: { tipo: tipo, id: id, anclado: r.anclado, form: form } }));
    }).catch(function (error) {
      var datos = error && error.datos;
      if (datos && datos.tope) {
        toast(datos.mensaje, 'error');
      } else {
        toast('No se pudo guardar el favorito. Intenta de nuevo.', 'error');
      }
    }).finally(function () { boton.disabled = false; });
  });

  // ---------- Notificaciones ----------
  function bajarContador(cuantas) {
    var badge = document.querySelector('[data-contador-notificaciones]');
    var texto = document.querySelector('[data-contador-notificaciones-texto]');
    if (badge) {
      var n = badge.textContent.indexOf('+') > -1 ? 10 : parseInt(badge.textContent, 10) || 0;
      n = cuantas === 'todas' ? 0 : Math.max(0, n - 1);
      if (n === 0) badge.remove(); else badge.textContent = n > 9 ? '9+' : n;
    }
    if (texto) {
      var m = parseInt(texto.textContent, 10) || 0;
      texto.textContent = cuantas === 'todas' ? 0 : Math.max(0, m - 1);
    }
  }

  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-leer]');
    if (!form) return;
    e.preventDefault();
    var fila = form.closest('.notif');
    var eraNueva = fila && fila.classList.contains('notif--nueva');
    enviar(form).then(function (r) {
      if (fila) fila.classList.remove('notif--nueva');
      if (eraNueva) bajarContador(1);
      if (r.url) window.location.href = r.url;
    }).catch(function () { form.submit(); });
  });

  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-leer-todas]');
    if (!form) return;
    e.preventDefault();
    enviar(form).then(function () {
      document.querySelectorAll('.notif--nueva').forEach(function (n) { n.classList.remove('notif--nueva'); });
      bajarContador('todas');
      form.remove();
      toast('Todas las notificaciones quedaron como leídas.', 'exito');
    }).catch(function () { form.submit(); });
  });

  // ---------- Confirmaciones ----------
  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-confirmar]');
    if (form && !window.confirm(form.dataset.confirmar)) e.preventDefault();
  });
})();
