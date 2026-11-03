/* Ranking de asesinos
   - Llena las barras al cargar la página.
   - Si una fotografía no existe, muestra la inicial del personaje en su lugar. */

(() => {
    'use strict';

    const lista = document.getElementById('ranking-lista');
    if (!lista) {
        return;
    }

    const sustituirFoto = (img) => {
        const fila = img.closest('.fila');
        const nombre = fila?.querySelector('.fila__nombre')?.textContent.trim() ?? '?';
        const inicial = document.createElement('span');
        inicial.className = 'fila__inicial';
        inicial.textContent = nombre.charAt(0).toUpperCase();
        img.replaceWith(inicial);
    };

    // Imágenes que ya han fallado antes de que se ejecute este script.
    lista.querySelectorAll('.fila__foto img').forEach((img) => {
        if (img.complete && img.naturalWidth === 0) {
            sustituirFoto(img);
        }
    });

    // Imágenes que fallan después (el evento error no se propaga, por eso se captura).
    lista.addEventListener('error', (evento) => {
        if (evento.target instanceof HTMLImageElement) {
            sustituirFoto(evento.target);
        }
    }, true);

    // Dos fotogramas para que el navegador pinte primero las barras a 0 y después las anime.
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            lista.classList.add('ranking__lista--llena');
        });
    });
})();