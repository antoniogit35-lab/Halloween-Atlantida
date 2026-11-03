document.addEventListener('DOMContentLoaded', () => {
  const video = document.getElementById('puerta-video');
  const imgEstatica = document.getElementById('puerta-estatica');
  const btnEntrar = document.getElementById('btn-entrar');

  if (!btnEntrar || !video) return;

  btnEntrar.addEventListener('click', () => {
    // 1. Desactivar botón para evitar dobles clics
    btnEntrar.classList.add('desactivado');

    // 2. Iniciar el vídeo
    video.currentTime = 0;
    video.play().then(() => {
      // 3. Fundido cruzado simultáneo y suave
      video.classList.add('reproduciendo');
      if (imgEstatica) {
        imgEstatica.classList.add('desvanecer');
      }
    }).catch(error => {
      console.warn('Error al reproducir el vídeo:', error);
      window.location.href = 'recetario.php';
    });
  });

  // 4. Al terminar la reproducción, navegar
  video.addEventListener('ended', () => {
    window.location.href = 'recetario.php';
  });
});