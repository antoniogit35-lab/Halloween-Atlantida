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