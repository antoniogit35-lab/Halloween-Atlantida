document.addEventListener('DOMContentLoaded', () => {
  const video = document.getElementById('puerta-video');
  const btnEntrar = document.getElementById('btn-entrar');

  if (!btnEntrar || !video) return;

  btnEntrar.addEventListener('click', () => {
    // 1. Desactivar el botón para evitar clics múltiples
    btnEntrar.classList.add('desactivado');

    // 2. Reproducir el vídeo por encima de la imagen estática
    video.classList.add('reproduciendo');
    video.currentTime = 0;

    video.play().catch(error => {
      console.warn('Error al reproducir el vídeo:', error);
      window.location.href = 'recetario.php';
    });
  });

  // 3. Al terminar la animación, navegar a la siguiente página
  video.addEventListener('ended', () => {
    window.location.href = 'recetario.php';
  });
});