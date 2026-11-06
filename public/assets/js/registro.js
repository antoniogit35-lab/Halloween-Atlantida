(() => {
  'use strict';

  const RUTA_IMAGENES = 'assets/img/personajes/';
  const TEXTO_INICIAL = 'Elige a tu asesino';
  const $ = (id) => document.getElementById(id);

  const campoAsesino = $('asesino');
  const botonAbrir = $('abrir-asesinos');
  const botonCerrar = $('cerrar-asesinos');
  const modal = $('modal-asesinos');
  const rejilla = $('rejilla-asesinos');
  const foto = $('foto-asesino');
  const interrogante = $('foto-interrogante');
  const nombre = $('nombre-asesino');

  let asesinos = [];

  async function cargarAsesinos() {
    const respuesta = await fetch('data/personajes.json');
    if (!respuesta.ok) {
      throw new Error('No se pudo cargar personajes.json');
    }
    return respuesta.json();
  }

  function mostrarAsesino(asesino) {
    if (!asesino) {
      foto.hidden = true;
      foto.removeAttribute('src');
      interrogante.hidden = false;
      nombre.textContent = TEXTO_INICIAL;
      return;
    }

    foto.src = RUTA_IMAGENES + asesino.imagen;
    foto.alt = '';
    foto.hidden = false;
    interrogante.hidden = true;
    nombre.textContent = asesino.nombre;
  }

  function marcarSeleccionado() {
    rejilla.querySelectorAll('button').forEach((boton) => {
      boton.setAttribute('aria-pressed', String(boton.dataset.id === campoAsesino.value));
    });
  }

  function elegir(asesino) {
    campoAsesino.value = asesino.id;
    mostrarAsesino(asesino);
    marcarSeleccionado();
    modal.close();
    botonAbrir.focus();
  }

  // Construye la cuadrícula sin usar innerHTML
  function construirRejilla() {
    rejilla.textContent = '';

    asesinos.forEach((asesino) => {
      const item = document.createElement('li');
      const boton = document.createElement('button');
      const imagen = document.createElement('img');
      const texto = document.createElement('span');

      boton.type = 'button';
      boton.dataset.id = asesino.id;
      imagen.src = RUTA_IMAGENES + asesino.imagen;
      imagen.alt = '';
      imagen.loading = 'lazy';
      texto.textContent = asesino.nombre;

      boton.append(imagen, texto);
      boton.addEventListener('click', () => elegir(asesino));

      item.append(boton);
      rejilla.append(item);
    });

    marcarSeleccionado();
  }

  // Los tres campos de la fecha pasan solos al siguiente cuando se completan
  function configurarFecha() {
    const partes = ['dia', 'mes', 'anio'].map($);

    partes.forEach((campo, i) => {
      campo.addEventListener('input', () => {
        campo.value = campo.value.replace(/\D/g, '');
        if (campo.value.length >= campo.maxLength && partes[i + 1]) {
          partes[i + 1].focus();
        }
      });
    });
  }

  botonAbrir.addEventListener('click', () => modal.showModal());
  botonCerrar.addEventListener('click', () => modal.close());
  modal.addEventListener('click', (evento) => {
    if (evento.target === modal) {
      modal.close();
    }
  });

  async function iniciar() {
    configurarFecha();

    try {
      asesinos = await cargarAsesinos();
    } catch (error) {
      console.error(error);
      nombre.textContent = 'No se pudieron cargar los asesinos';
      return;
    }

    construirRejilla();

    // Si el formulario volvió con un error, se recupera el asesino elegido
    mostrarAsesino(asesinos.find((a) => a.id === campoAsesino.value) || null);
  }

  iniciar();
})();