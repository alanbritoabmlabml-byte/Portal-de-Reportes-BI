/**
 * Señales de trazabilidad que manda el navegador.
 *
 * - Latido: cada minuto avisa que la persona sigue en la página, para que la
 *   permanencia sea real y no solo «hasta que cambió de pantalla».
 * - Al ocultar la pestaña se manda un último latido con sendBeacon, que sí
 *   llega aunque la página se esté cerrando.
 * - Clic en un enlace externo (tarjetas del menú): queda anotado a qué sitio
 *   salió el usuario.
 */
(function () {
  'use strict';

  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var rutaLatido = '/trazabilidad/latido';
  var rutaEvento = '/trazabilidad/evento';
  var INTERVALO = 60000;

  function enviar(ruta, datos, conBeacon) {
    var cuerpo = new FormData();
    cuerpo.append('_token', csrf);
    Object.keys(datos || {}).forEach(function (k) {
      if (datos[k] !== null && datos[k] !== undefined) cuerpo.append(k, datos[k]);
    });

    // sendBeacon sobrevive al cierre de la pestaña; fetch no siempre
    if (conBeacon && navigator.sendBeacon) {
      try { navigator.sendBeacon(ruta, cuerpo); return; } catch (e) {}
    }

    fetch(ruta, {
      method: 'POST',
      body: cuerpo,
      credentials: 'same-origin',
      keepalive: true,
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    }).catch(function () {});
  }

  var reloj = setInterval(function () {
    if (document.visibilityState === 'visible') enviar(rutaLatido, {});
  }, INTERVALO);

  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'hidden') enviar(rutaLatido, {}, true);
  });
  window.addEventListener('pagehide', function () { enviar(rutaLatido, {}, true); });
  window.addEventListener('beforeunload', function () { clearInterval(reloj); });

  // ---------- Salidas a sitios externos ----------
  document.addEventListener('click', function (e) {
    var enlace = e.target.closest('a[href^="http"]');
    if (!enlace) return;

    var destino;
    try { destino = new URL(enlace.href); } catch (err) { return; }
    if (destino.host === window.location.host) return;

    var tarjeta = enlace.closest('[data-tarjeta], [data-fav-tipo]');
    var nombre = tarjeta ? (tarjeta.querySelector('h3, .riel-nombre') || {}).textContent : enlace.textContent;

    enviar(rutaEvento, {
      accion: 'abrir',
      descripcion: 'Abrió ' + (nombre || destino.host).trim() + ' (' + destino.host + ')',
      entidad: tarjeta && tarjeta.dataset.favId ? 'Acceso' : null,
      entidad_id: tarjeta && tarjeta.dataset.favId ? tarjeta.dataset.favId : null,
      url: enlace.href.slice(0, 500)
    }, true);
  });
})();
