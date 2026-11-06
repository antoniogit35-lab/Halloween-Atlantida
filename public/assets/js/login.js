(() => {
  'use strict';

  const RUTA_IMAGENES = 'assets/img/personajes/';
  const $ = (id) => document.getElementById(id);

  const campoUsuario = $('usuario');
  const foto = $('foto-asesino');
  const interrogante = $('foto-interrogante');
  const botonAbrir = $('abrir-asesinos');
  const botonCerrar = $('cerrar-asesinos');
  const modal = $('modal-asesinos');
  const rejilla = $('rejilla-asesinos');
  const fichaNombre = $('ficha-nombre');
  const fichaObra = $('ficha-obra');
  const fichaDescripcion = $('ficha-descripcion');
  const epitafio = $('epitafio');
  const fechas = $('fechas');

  let asesinos = [];
  let frases = [];
  let consultaActual = 0; // evita que una respuesta antigua pise a una más reciente

  async function cargarJSON(url) {
    const respuesta = await fetch(url);
    if (!respuesta.ok) {
      throw new Error('No se pudo cargar ' + url);
    }
    return respuesta.json();
  }

  function mostrarEpitafio() {
    if (frases.length === 0) {
      epitafio.textContent = '';
      return;
    }
    epitafio.textContent = frases[Math.floor(Math.random() * frases.length)];
  }

  // Muestra un asesino en el círculo de la lápida y en la ficha del cartel
  function mostrarAsesino(asesino) {
    if (!asesino) {
      foto.hidden = true;
      foto.removeAttribute('src');
      interrogante.hidden = false;
      fichaNombre.textContent = 'Elige a tu asesino';
      fichaObra.textContent = '';
      fichaDescripcion.textContent = 'Pulsa el círculo de la lápida para ver a todos los asesinos.';
      return;
    }

    foto.src = RUTA_IMAGENES + asesino.imagen;
    foto.alt = asesino.nombre;
    foto.hidden = false;
    interrogante.hidden = true;
    fichaNombre.textContent = asesino.nombre;
    fichaObra.textContent = asesino.obra;
    fichaDescripcion.textContent = asesino.descripcion;
  }

  function formatearFecha(iso) {
    const [anio, mes, dia] = iso.split('-');
    return dia + '/' + mes + '/' + anio;
  }

  function limpiarDatosUsuario() {
    mostrarAsesino(null);
    fechas.textContent = '';
  }

  // Pide al servidor los datos de la lápida del usuario escrito
  async function consultarUsuario() {
    const nombre = campoUsuario.value.trim();
    const consulta = ++consultaActual;

    mostrarEpitafio();

    if (nombre === '') {
      limpiarDatosUsuario();
      return;
    }

    try {
      const respuesta = await fetch('api/lapida.php?usuario=' + encodeURIComponent(nombre));
      if (!respuesta.ok) {
        throw new Error('Respuesta no válida');
      }
      const datos = await respuesta.json();

      if (consulta !== consultaActual) {
        return;
      }

      if (!datos.existe) {
        limpiarDatosUsuario();
        return;
      }

      mostrarAsesino(asesinos.find((a) => a.id === datos.asesino) || null);
      fechas.textContent = formatearFecha(datos.nacimiento) + ' — ' + formatearFecha(datos.alta);
    } catch (error) {
      console.error(error);
      if (consulta === consultaActual) {
        limpiarDatosUsuario();
      }
    }
  }

  // Construye la cuadrícula del modal sin usar innerHTML
  function construirRejilla() {
    rejilla.textContent = '';

    asesinos.forEach((asesino) => {
      const item = document.createElement('li');
      const boton = document.createElement('button');
      const imagen = document.createElement('img');
      const nombre = document.createElement('span');

      boton.type = 'button';
      imagen.src = RUTA_IMAGENES + asesino.imagen;
      imagen.alt = '';
      imagen.loading = 'lazy';
      nombre.textContent = asesino.nombre;

      boton.append(imagen, nombre);
      boton.addEventListener('click', () => {
        mostrarAsesino(asesino); // en el login solo es una vista previa
        modal.close();
      });

      item.append(boton);
      rejilla.append(item);
    });
  }

  botonAbrir.addEventListener('click', () => modal.showModal());
  botonCerrar.addEventListener('click', () => modal.close());
  modal.addEventListener('click', (evento) => {
    if (evento.target === modal) {
      modal.close();
    }
  });

  campoUsuario.addEventListener('change', consultarUsuario);

  async function iniciar() {
    try {
      [asesinos, frases] = await Promise.all([
        cargarJSON('data/personajes.json'),
        cargarJSON('data/frases.json'),
      ]);
    } catch (error) {
      console.error(error);
    }

    construirRejilla();
    mostrarEpitafio();
    mostrarAsesino(null);

    // Si el formulario volvió con un nombre tras un error, se muestran sus datos
    if (campoUsuario.value.trim() !== '') {
      consultarUsuario();
    }
  }

  iniciar();
})();