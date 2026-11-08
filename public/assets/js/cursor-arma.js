// ------------------------------------------------------------
// ARMA PEGADA AL CURSOR
// - El arma que elige el usuario a mano se guarda en el navegador (localStorage).
// - Si no ha elegido ninguna, se usa la del asesino del usuario con sesión iniciada.
// - Solo se muestra con ratón: en pantallas táctiles no hay cursor.
// Otros scripts pueden usar window.CursorArma (por ejemplo, ranking.js).
// ------------------------------------------------------------
(() => {
  'use strict';

  const RUTA_ARMAS = 'assets/img/cursores/';
  const RUTA_DATOS = 'data/cursores.json';
  const CLAVE_ELEGIDA = 'armaElegida'; // id de un personaje, o 'ninguna'
  const CLAVE_SESION = 'armaSesion';   // id del asesino del usuario con sesión
  const TAMANO = 44;                   // píxeles con los que se dibuja el arma
  const DESPLAZAMIENTO = 14;           // separación respecto a la punta del cursor

  let armas = {};          // { idPersonaje: 'Archivo.webp' }, se carga de cursores.json
  let imagen = null;       // la <img> que sigue al ratón
  let hayArma = false;     // true si ahora mismo hay un arma que mostrar
  let posicion = { x: -200, y: -200 }; // fuera de pantalla hasta el primer movimiento
  let fotogramaPendiente = false;

  // ---------- Guardado en el navegador ----------
  // Se envuelve en try/catch porque localStorage puede estar bloqueado (modo privado, etc.)

  function leer(clave) {
    try {
      return window.localStorage.getItem(clave);
    } catch (error) {
      return null;
    }
  }

  function guardar(clave, valor) {
    try {
      window.localStorage.setItem(clave, valor);
    } catch (error) {
      /* sin almacenamiento: el arma solo durará mientras no se recargue la página */
    }
  }

  function borrar(clave) {
    try {
      window.localStorage.removeItem(clave);
    } catch (error) {
      /* nada que hacer */
    }
  }

  // ---------- Qué arma toca ----------

  // Devuelve el id del personaje cuya arma se lleva, o null si no hay ninguna
  function idActual() {
    const elegida = leer(CLAVE_ELEGIDA);
    if (elegida === 'ninguna') {
      return null;
    }
    return elegida || leer(CLAVE_SESION) || null;
  }

  function ruta(id) {
    return armas[id] ? RUTA_ARMAS + armas[id] : null;
  }

  // principal.php pone data-asesino en el <body>: con valor si hay sesión, vacío si no la hay
  function sincronizarSesion() {
    if (!document.body.hasAttribute('data-asesino')) {
      return; // esta página no sabe nada de la sesión
    }
    const asesino = document.body.dataset.asesino;
    if (asesino) {
      guardar(CLAVE_SESION, asesino);
    } else {
      borrar(CLAVE_SESION);
    }
  }

  // ---------- La imagen que sigue al ratón ----------

  function crearImagen() {
    imagen = document.createElement('img');
    imagen.alt = '';
    imagen.setAttribute('aria-hidden', 'true');

    // Con popover, la imagen vive en la «capa superior» del navegador y se ve
    // también por encima de las ventanas emergentes (dialog).
    const admitePopover = typeof imagen.showPopover === 'function';
    if (admitePopover) {
      imagen.setAttribute('popover', 'manual');
    }

    Object.assign(imagen.style, {
      inset: 'auto',
      left: '0',
      top: '0',
      margin: '0',
      padding: '0',
      border: '0',
      background: 'transparent',
      overflow: 'visible',
      position: 'fixed',
      width: TAMANO + 'px',
      height: TAMANO + 'px',
      objectFit: 'contain',
      pointerEvents: 'none', // nunca debe tapar un clic
      zIndex: '2147483647',
      visibility: 'hidden',
      willChange: 'transform',
    });

    document.body.append(imagen);

    if (admitePopover) {
      try {
        imagen.showPopover();
      } catch (error) {
        /* si falla, queda como un elemento fijo normal */
      }
    }
  }

  // Cuando se abre una ventana emergente, la imagen se vuelve a mostrar para
  // quedar por encima de ella (lo último que entra en la capa superior es lo que más arriba queda)
  function subirSobreVentanas() {
    if (imagen && typeof imagen.showPopover === 'function' && imagen.matches(':popover-open')) {
      try {
        imagen.hidePopover();
        imagen.showPopover();
      } catch (error) {
        /* ignorar */
      }
    }
  }

  function colocarImagen() {
    fotogramaPendiente = false;
    if (!imagen) {
      return;
    }
    imagen.style.transform = 'translate('
      + (posicion.x + DESPLAZAMIENTO) + 'px, '
      + (posicion.y + DESPLAZAMIENTO) + 'px)';
  }

  // El movimiento se aplica como mucho una vez por fotograma, para que sea fluido
  function programarMovimiento() {
    if (!fotogramaPendiente) {
      fotogramaPendiente = true;
      window.requestAnimationFrame(colocarImagen);
    }
  }

  // Pone o quita el arma según lo que haya guardado
  function aplicar() {
    const conRaton = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    const id = idActual();
    const archivo = conRaton && id ? ruta(id) : null;

    hayArma = archivo !== null;

    if (!hayArma) {
      if (imagen) {
        imagen.style.visibility = 'hidden';
      }
      return;
    }

    if (!imagen) {
      crearImagen();
    }
    imagen.src = archivo;
    imagen.style.visibility = 'visible';
    colocarImagen();
  }

  function avisarDelCambio() {
    document.dispatchEvent(new CustomEvent('arma-cambiada'));
  }

  // ---------- Eventos ----------

  document.addEventListener('pointermove', (evento) => {
    if (evento.pointerType !== 'mouse') {
      return;
    }
    posicion = { x: evento.clientX, y: evento.clientY };
    if (hayArma && imagen) {
      imagen.style.visibility = 'visible';
      programarMovimiento();
    }
  });

  // Si el ratón sale de la ventana, el arma se esconde hasta que vuelva
  document.documentElement.addEventListener('mouseleave', () => {
    if (imagen) {
      imagen.style.visibility = 'hidden';
    }
  });

  // Si se cambia el arma en otra pestaña, esta se actualiza
  window.addEventListener('storage', aplicar);

  // ---------- Uso desde otros scripts ----------

  window.CursorArma = {
    ruta,                 // CursorArma.ruta('chucky') → url de su arma, o null
    actual: idActual,     // CursorArma.actual() → id del personaje cuya arma se lleva, o null
    elegir(id) {
      guardar(CLAVE_ELEGIDA, id);
      aplicar();
      avisarDelCambio();
    },
    quitar() {
      guardar(CLAVE_ELEGIDA, 'ninguna');
      aplicar();
      avisarDelCambio();
    },
  };

  // ---------- Inicio ----------

  async function iniciar() {
    sincronizarSesion();

    try {
      const respuesta = await fetch(RUTA_DATOS);
      if (respuesta.ok) {
        armas = await respuesta.json();
      }
    } catch (error) {
      console.error('No se pudo cargar ' + RUTA_DATOS, error);
    }

    aplicar();

    new MutationObserver(subirSobreVentanas).observe(document.body, {
      subtree: true,
      attributes: true,
      attributeFilter: ['open'],
    });
  }

  iniciar();
})();