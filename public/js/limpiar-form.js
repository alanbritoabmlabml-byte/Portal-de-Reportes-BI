/**
 * Botón "Limpiar" de los formularios de ALTA.
 *
 * Marcado esperado:  <button type="button" data-limpiar>Limpiar</button>
 * dentro del formulario que tiene que vaciar.
 *
 * No usa form.reset() a propósito: reset() devuelve el formulario a los
 * valores que trajo el HTML, y en las altas del servidor esos valores son los
 * de old() después de un error de validación. Lo que se pide aquí es un
 * formulario en blanco, así que los campos de texto se vacían a mano.
 *
 * Las casillas y las pastillas sí vuelven a su valor por defecto (Media,
 * Medio…): un formulario nuevo tampoco sale sin nada marcado.
 *
 * Solo se pone en las altas: en una edición, vaciar todo sería un pie para
 * borrar sin querer lo que ya estaba guardado.
 */

/** Vacía todos los campos del formulario. */
function limpiar(form) {
  const avisar = [];

  form.querySelectorAll("input, select, textarea").forEach((campo) => {
    // Los ocultos llevan datos de control (_form, _token, _method).
    if (campo.type === "hidden" || campo.disabled || campo.readOnly) return;

    if (campo.type === "checkbox" || campo.type === "radio") {
      campo.checked = campo.defaultChecked;
    } else if (campo.tagName === "SELECT") {
      campo.selectedIndex = 0;
      avisar.push(campo);
    } else {
      campo.value = "";
      avisar.push(campo);
    }
  });

  // El buscador de los selects y el contador de caracteres guardan su propio
  // estado: si no se les avisa, siguen mostrando lo anterior.
  avisar.forEach((campo) => campo.dispatchEvent(new Event("change", { bubbles: true })));

  // Deja el cursor donde se empieza a escribir de nuevo.
  const primero = form.querySelector("input:not([type=hidden]), select, textarea");
  primero?.focus();
}

document.addEventListener("click", (ev) => {
  const boton = ev.target.closest("[data-limpiar]");
  if (!boton) return;

  const form = boton.closest("form");
  if (form) limpiar(form);
});
