/* Ranking de asesinos
   - Llena las barras al cargar la página.
   - Si una fotografía no existe, muestra la inicial del personaje en su lugar.
   - Al pulsar la foto de un personaje abre su ficha en una ventana emergente. */

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

    // Ficha del personaje (ventana emergente)
    const ficha = document.getElementById('ficha');
    if (ficha && typeof ficha.showModal === 'function') {
        const fichaImg = document.getElementById('ficha-img');
        const fichaInicial = document.getElementById('ficha-inicial');
        const fichaNombre = document.getElementById('ficha-nombre');
        const fichaObra = document.getElementById('ficha-obra');
        const fichaDescripcion = document.getElementById('ficha-descripcion');

        const abrirFicha = (boton) => {
            const nombre = boton.closest('.fila')?.querySelector('.fila__nombre')?.textContent.trim() ?? '';
            const obra = boton.dataset.obra ?? '';
            const descripcion = boton.dataset.descripcion ?? '';
            const foto = boton.querySelector('img');

            fichaNombre.textContent = nombre;
            fichaObra.textContent = obra;
            fichaObra.hidden = obra === '';
            fichaDescripcion.textContent = descripcion !== ''
                ? descripcion
                : 'Todavía no hay descripción de este asesino.';

            if (foto) {
                fichaImg.src = foto.currentSrc || foto.src;
                fichaImg.hidden = false;
                fichaInicial.hidden = true;
            } else {
                fichaImg.hidden = true;
                fichaInicial.textContent = nombre.charAt(0).toUpperCase();
                fichaInicial.hidden = false;
            }

            ficha.showModal();
        };

        lista.addEventListener('click', (evento) => {
            const boton = evento.target.closest('.fila__foto');
            if (boton) {
                abrirFicha(boton);
            }
        });

        document.getElementById('ficha-cerrar')?.addEventListener('click', () => ficha.close());

        // Un clic fuera de la ficha (sobre el fondo oscuro) también la cierra.
        ficha.addEventListener('click', (evento) => {
            if (evento.target === ficha) {
                ficha.close();
            }
        });
    }

    // Dos fotogramas para que el navegador pinte primero las barras a 0 y después las anime.
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            lista.classList.add('ranking__lista--llena');
        });
    });
})();