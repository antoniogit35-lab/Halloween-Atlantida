/**
 * Contacta con Jack: validación del formulario en el navegador.
 * Comprueba asunto, email y mensaje, y muestra cada error junto a su campo.
 * Si todo es correcto deja que el formulario se envíe con normalidad (POST).
 */
document.addEventListener('DOMContentLoaded', () => {
  const formulario = document.getElementById('formulario-contacto');
  if (!formulario) return;

  const resumen = document.getElementById('resumen-errores');
  const contador = document.getElementById('contador-mensaje');
  const MIN_MENSAJE = 10;

  // Reglas de cada campo: devuelven el texto del error o '' si el valor es correcto.
  const reglas = {
    asunto(valor) {
      if (valor === '') return 'Escribe un asunto.';
      return '';
    },
    email(valor) {
      if (valor === '') return 'Escribe tu email.';
      // Formato básico: algo@dominio.ext
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(valor)) {
        return 'El email no es válido. Escríbelo así: nombre@correo.com.';
      }
      return '';
    },
    mensaje(valor) {
      if (valor === '') return 'Escribe tu mensaje.';
      if (valor.length < MIN_MENSAJE) {
        return `El mensaje es muy corto. Escribe al menos ${MIN_MENSAJE} caracteres.`;
      }
      return '';
    },
  };

  const campos = Object.keys(reglas).map((nombre) => ({
    nombre,
    control: formulario.elements[nombre],
    error: document.getElementById(`error-${nombre}`),
  }));

  // Valida un campo y actualiza su aspecto. Devuelve true si es correcto.
  function validar({ nombre, control, error }) {
    const mensaje = reglas[nombre](control.value.trim());
    error.textContent = mensaje;
    error.hidden = mensaje === '';
    control.setAttribute('aria-invalid', mensaje === '' ? 'false' : 'true');
    return mensaje === '';
  }

  function actualizarContador() {
    const area = formulario.elements.mensaje;
    const max = Number(contador.dataset.max);
    contador.textContent = `${area.value.length} / ${max}`;
    contador.classList.toggle('campo__contador--limite', area.value.length >= max);
  }

  // Al salir de un campo se valida; si ya tenía error, se corrige mientras se escribe.
  campos.forEach((campo) => {
    campo.control.addEventListener('blur', () => {
      if (campo.control.value.trim() !== '' || campo.control.getAttribute('aria-invalid') === 'true') {
        validar(campo);
      }
    });
    campo.control.addEventListener('input', () => {
      if (campo.control.getAttribute('aria-invalid') === 'true') validar(campo);
    });
  });

  formulario.elements.mensaje.addEventListener('input', actualizarContador);
  actualizarContador();

  formulario.addEventListener('submit', (evento) => {
    const incorrectos = campos.filter((campo) => !validar(campo));

    if (incorrectos.length === 0) {
      resumen.hidden = true;
      return; // todo bien: el formulario se envía
    }

    evento.preventDefault();
    resumen.textContent = incorrectos.length === 1
      ? 'Hay 1 campo que corregir antes de enviar.'
      : `Hay ${incorrectos.length} campos que corregir antes de enviar.`;
    resumen.hidden = false;
    incorrectos[0].control.focus();
  });
});