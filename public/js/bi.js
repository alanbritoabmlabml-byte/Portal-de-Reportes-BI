/**
 * Página de un área de BI.
 *
 * - Cada tablero carga su iframe solo al abrirse (data-src → src).
 * - En escritorio el primero se abre solo; en móvil todos empiezan plegados.
 * - Pantalla completa, recarga, índice lateral que sigue al scroll y salto
 *   a otra área del mismo departamento.
 */
(function () {
  'use strict';

  var ancho = window.matchMedia('(min-width: 981px)').matches;

  function abrir(rep, si) {
    var marco = rep.querySelector('.reporte-marco');
    var boton = rep.querySelector('[data-abrir]');
    var iframe = marco.querySelector('iframe');
    if (si && iframe.dataset.src && !iframe.src) {
      iframe.addEventListener('load', function () { marco.classList.add('reporte-marco--listo'); }, { once: true });
      iframe.src = iframe.dataset.src;
    }
    marco.hidden = !si;
    rep.classList.toggle('reporte--abierto', si);
    if (boton) {
      boton.setAttribute('aria-expanded', String(si));
      boton.firstChild.textContent = si ? 'Ocultar ' : 'Ver tablero ';
    }
  }

  var reportes = Array.prototype.slice.call(document.querySelectorAll('.reporte'));

  reportes.forEach(function (rep) {
    var marco = rep.querySelector('.reporte-marco');
    var iframe = marco.querySelector('iframe');

    rep.querySelector('[data-abrir]').addEventListener('click', function () {
      abrir(rep, marco.hidden);
    });

    rep.querySelector('[data-recargar]').addEventListener('click', function () {
      abrir(rep, true);
      marco.classList.remove('reporte-marco--listo');
      iframe.addEventListener('load', function () { marco.classList.add('reporte-marco--listo'); }, { once: true });
      iframe.src = iframe.dataset.src;
    });

    rep.querySelector('[data-pantalla-completa]').addEventListener('click', function () {
      abrir(rep, true);
      var pedir = marco.requestFullscreen || marco.webkitRequestFullscreen;
      if (pedir) pedir.call(marco);
      else if (iframe.src) window.open(iframe.src, '_blank', 'noopener');
    });

    // Con hash en la URL (#reporte-12) o en escritorio el primero, se abre solo
    var porHash = window.location.hash && rep.id === window.location.hash.slice(1);
    if (porHash || (ancho && rep.hasAttribute('data-abierto'))) abrir(rep, true);
  });

  // ---------- Índice lateral: el enlace del reporte visible se resalta ----------
  var enlaces = document.querySelectorAll('[data-indice]');
  if (enlaces.length && 'IntersectionObserver' in window) {
    var visibles = {};
    var obs = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (en) { visibles[en.target.dataset.reporte] = en.isIntersecting ? en.intersectionRatio : 0; });
      var mejor = null, ratio = 0;
      Object.keys(visibles).forEach(function (k) { if (visibles[k] > ratio) { ratio = visibles[k]; mejor = k; } });
      enlaces.forEach(function (a) { a.classList.toggle('activo', a.dataset.indice === mejor); });
    }, { threshold: [0, .25, .5, .75, 1], rootMargin: '-80px 0px -40% 0px' });
    reportes.forEach(function (r) { obs.observe(r); });

    enlaces.forEach(function (a) {
      a.addEventListener('click', function () {
        var rep = document.getElementById(a.getAttribute('href').slice(1));
        if (rep) abrir(rep, true);
      });
    });
  }

  // ---------- Saltar a otra área ----------
  var saltar = document.querySelector('[data-saltar-area]');
  if (saltar) {
    saltar.addEventListener('change', function () {
      if (saltar.value) window.location.href = saltar.value;
    });
  }
})();
