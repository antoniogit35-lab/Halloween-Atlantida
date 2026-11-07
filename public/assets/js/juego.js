// ------------------------------------------------------------
// INTERFAZ DEL JUEGO · Paso 3: panel con pestañas e interrogatorio
// Usa los datos de juego-datos.js y las funciones de juego-logica.js
// ------------------------------------------------------------

const RUTA_PERSONAJES = 'assets/img/personajes/';
const RUTA_ARMAS = 'assets/img/armas/';
const NOMBRE_TIPO = { sospechoso: 'Sospechoso', arma: 'Arma', habitacion: 'Habitación' };

let partida = crearPartida();   // estado de la partida actual
let habitacionActual = null;    // id de la habitación seleccionada

const marcadorTurnos = document.getElementById('turnos');
const marcadorIntentos = document.getElementById('intentos');
const mensajeEstado = document.getElementById('estado-juego');
const salas = document.querySelectorAll('.sala');
const escenario = document.querySelector('.escenario');
const botonPanel = document.getElementById('alternar-panel');
const pestanas = document.querySelectorAll('[role="tab"]');
const formularioPregunta = document.getElementById('form-pregunta');
const botonPreguntar = document.getElementById('boton-preguntar');
const textoHabitacion = document.getElementById('habitacion-elegida');
const cajaRespuesta = document.getElementById('respuesta');

// ---------- Utilidades ----------

function buscarElemento(tipo, id) {
  return CATEGORIAS[tipo].find((elemento) => elemento.id === id);
}

function nombreHabitacion(id) {
  return buscarElemento('habitacion', id).nombre;
}

// Crea un párrafo de texto (se usa textContent, nunca innerHTML)
function crearParrafo(texto, clase) {
  const parrafo = document.createElement('p');
  parrafo.textContent = texto;
  if (clase) {
    parrafo.className = clase;
  }
  return parrafo;
}

// ---------- Marcadores y estado ----------

function actualizarMarcadores() {
  marcadorTurnos.textContent = partida.turnosRestantes;
  marcadorIntentos.textContent = partida.intentosRestantes;
}

// El botón de preguntar se desactiva si la partida ha terminado o no quedan turnos
function actualizarBotonPreguntar() {
  botonPreguntar.disabled = partida.estado !== 'jugando' || partida.turnosRestantes <= 0;
}

// ---------- Mapa ----------

function seleccionarHabitacion(id) {
  habitacionActual = id;

  salas.forEach((sala) => {
    sala.setAttribute('aria-pressed', String(sala.dataset.habitacion === id));
  });

  textoHabitacion.textContent = nombreHabitacion(id);
  mensajeEstado.textContent = 'Estás en: ' + nombreHabitacion(id) + '.';
}

salas.forEach((sala) => {
  sala.addEventListener('click', () => seleccionarHabitacion(sala.dataset.habitacion));
});

// ---------- Panel: pestañas ----------

function activarPestana(pestanaActiva) {
  pestanas.forEach((pestana) => {
    const activa = pestana === pestanaActiva;
    pestana.setAttribute('aria-selected', String(activa));
    pestana.tabIndex = activa ? 0 : -1;
    document.getElementById(pestana.getAttribute('aria-controls')).hidden = !activa;
  });
}

pestanas.forEach((pestana, i) => {
  pestana.addEventListener('click', () => activarPestana(pestana));

  // Con el teclado, las flechas izquierda y derecha cambian de pestaña
  pestana.addEventListener('keydown', (evento) => {
    if (evento.key !== 'ArrowRight' && evento.key !== 'ArrowLeft') {
      return;
    }
    const salto = evento.key === 'ArrowRight' ? 1 : pestanas.length - 1;
    const siguiente = pestanas[(i + salto) % pestanas.length];
    activarPestana(siguiente);
    siguiente.focus();
  });
});

// ---------- Panel: mostrar u ocultar en escritorio ----------

botonPanel.addEventListener('click', () => {
  const oculto = document.body.classList.toggle('panel-oculto');
  botonPanel.setAttribute('aria-expanded', String(!oculto));
  botonPanel.textContent = oculto ? 'Mostrar panel' : 'Ocultar panel';
});

// ---------- Interrogar: opciones con miniaturas ----------

// Crea una opción (botón de radio con miniatura) por cada elemento de la lista
function construirOpciones(contenedor, grupo, lista, ruta) {
  lista.forEach((elemento) => {
    const etiqueta = document.createElement('label');
    etiqueta.className = 'opcion';
    etiqueta.title = elemento.nombre;

    const radio = document.createElement('input');
    radio.type = 'radio';
    radio.name = grupo;
    radio.value = elemento.id;
    radio.setAttribute('aria-label', elemento.nombre);

    const imagen = document.createElement('img');
    imagen.src = ruta + elemento.imagen;
    imagen.alt = '';

    // Al elegir, el nombre aparece junto al título del grupo
    radio.addEventListener('change', () => {
      document.getElementById('elegido-' + grupo).textContent = elemento.nombre;
    });

    etiqueta.append(radio, imagen);
    contenedor.append(etiqueta);
  });
}

construirOpciones(document.getElementById('opciones-interrogado'), 'interrogado', SOSPECHOSOS, RUTA_PERSONAJES);
construirOpciones(document.getElementById('opciones-arma'), 'arma', ARMAS, RUTA_ARMAS);
construirOpciones(document.getElementById('opciones-sospechoso'), 'sospechoso', SOSPECHOSOS, RUTA_PERSONAJES);

// ---------- Interrogar: mostrar la respuesta ----------

function mostrarRespuesta(...nodos) {
  cajaRespuesta.replaceChildren(...nodos);
}

// Muestra la carta que enseña el interrogado, con su imagen si es un arma o un sospechoso
function crearCartaMostrada(textoCarta) {
  const { tipo, id } = separarCarta(textoCarta);
  const elemento = buscarElemento(tipo, id);

  const contenedor = document.createElement('div');
  contenedor.className = 'carta-mostrada';

  if (tipo !== 'habitacion') {
    const imagen = document.createElement('img');
    imagen.src = (tipo === 'arma' ? RUTA_ARMAS : RUTA_PERSONAJES) + elemento.imagen;
    imagen.alt = '';
    contenedor.append(imagen);
  }

  const texto = document.createElement('p');
  const etiqueta = document.createElement('small');
  etiqueta.textContent = NOMBRE_TIPO[tipo];
  texto.append(etiqueta, document.createElement('br'), elemento.nombre);
  contenedor.append(texto);

  return contenedor;
}

function mostrarResultadoPregunta(resultado, idHabitacion, idArma, idSospechoso) {
  const interrogado = buscarElemento('sospechoso', resultado.interrogado);
  const hipotesis = [
    buscarElemento('sospechoso', idSospechoso).nombre,
    buscarElemento('arma', idArma).nombre,
    nombreHabitacion(idHabitacion),
  ].join(' · ');

  const nodos = [
    crearParrafo('Preguntas a ' + interrogado.nombre + '.', 'pregunta-hecha'),
    crearParrafo('Hipótesis: ' + hipotesis, 'pregunta-hecha'),
  ];

  if (resultado.carta) {
    nodos.push(crearParrafo(interrogado.nombre + ' te enseña una carta:'));
    nodos.push(crearCartaMostrada(resultado.carta));
  } else {
    nodos.push(crearParrafo(interrogado.nombre + ' no puede ayudarte con esa hipótesis.'));
  }

  if (partida.turnosRestantes === 0) {
    nodos.push(crearParrafo('Se han acabado los turnos: solo te queda una acusación final.', 'aviso-final'));
  }

  mostrarRespuesta(...nodos);
}

// ---------- Interrogar: enviar la pregunta ----------

formularioPregunta.addEventListener('submit', (evento) => {
  evento.preventDefault();

  if (habitacionActual === null) {
    mostrarRespuesta(crearParrafo('Primero elige una habitación en el mapa.'));
    return;
  }

  const datos = new FormData(formularioPregunta);
  const interrogado = datos.get('interrogado');
  const arma = datos.get('arma');
  const sospechoso = datos.get('sospechoso');

  if (!interrogado || !arma || !sospechoso) {
    mostrarRespuesta(crearParrafo('Elige a quién interrogas, un arma y un sospechoso.'));
    return;
  }

  try {
    const resultado = preguntar(partida, interrogado, habitacionActual, arma, sospechoso);
    actualizarMarcadores();
    actualizarBotonPreguntar();
    mostrarResultadoPregunta(resultado, habitacionActual, arma, sospechoso);
  } catch (error) {
    console.error(error);
    mostrarRespuesta(crearParrafo('No se pudo hacer la pregunta.'));
  }
});

// ---------- Modo ajuste de zonas: añade ?zonas a la dirección ----------

if (new URLSearchParams(window.location.search).has('zonas')) {
  escenario.classList.add('ver-zonas');
}

actualizarMarcadores();
actualizarBotonPreguntar();