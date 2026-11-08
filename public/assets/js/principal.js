document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('sobre-nosotros');
  const abrir = document.getElementById('abrir-sobre-nosotros');
  const cerrar = document.getElementById('cerrar-sobre-nosotros');

  if (!modal || !abrir || !cerrar) return;

  abrir.addEventListener('click', () => modal.showModal());
  cerrar.addEventListener('click', () => modal.close());

  // Cerrar al hacer clic fuera del contenido
  modal.addEventListener('click', (evento) => {
    if (evento.target === modal) modal.close();
  });
});

// Confirmación para salir del cementerio: ventana pequeña junto al botón
document.addEventListener('DOMContentLoaded', () => {
  const botonSalir = document.getElementById('abrir-salir');
  const confirmarSalir = document.getElementById('confirmar-salir');
  const cancelarSalir = document.getElementById('cancelar-salir');

  if (!botonSalir || !confirmarSalir || !cancelarSalir) return;

  botonSalir.addEventListener('click', () => {
    // Se coloca justo debajo del botón, alineada con su borde derecho
    const caja = botonSalir.getBoundingClientRect();
    const anchoVentana = document.documentElement.clientWidth;

    confirmarSalir.style.top = (caja.bottom + 10) + 'px';
    confirmarSalir.style.right = Math.max(12, anchoVentana - caja.right) + 'px';
    confirmarSalir.showModal();
  });

  cancelarSalir.addEventListener('click', () => confirmarSalir.close());

  // Un clic fuera de la ventana también la cierra
  confirmarSalir.addEventListener('click', (evento) => {
    if (evento.target === confirmarSalir) confirmarSalir.close();
  });
});