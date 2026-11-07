// ------------------------------------------------------------
// INTERFAZ DEL JUEGO · Paso 2: mapa y selección de habitación
// Usa los datos de juego-datos.js y las funciones de juego-logica.js
// ------------------------------------------------------------

let partida = crearPartida();   // estado de la partida actual
let habitacionActual = null;    // id de la habitación seleccionada

const marcadorTurnos = document.getElementById('turnos');
const marcadorIntentos = document.getElementById('intentos');
const mensajeEstado = document.getElementById('estado-juego');
const salas = document.querySelectorAll('.sala');
const escenario = document.querySelector('.escenario');

// Pinta en la cabecera los turnos e intentos que quedan
function actualizarMarcadores() {
  marcadorTurnos.textContent = partida.turnosRestantes;
  marcadorIntentos.textContent = partida.intentosRestantes;
}

function nombreHabitacion(id) {
  return HABITACIONES.find((habitacion) => habitacion.id === id).nombre;
}

// Marca la habitación elegida y avisa en la barra de estado
function seleccionarHabitacion(id) {
  habitacionActual = id;

  salas.forEach((sala) => {
    sala.setAttribute('aria-pressed', String(sala.dataset.habitacion === id));
  });

  mensajeEstado.textContent = 'Estás en: ' + nombreHabitacion(id) + '.';
}

salas.forEach((sala) => {
  sala.addEventListener('click', () => seleccionarHabitacion(sala.dataset.habitacion));
});

// Modo ajuste de zonas: añade ?zonas a la dirección para ver los recuadros
if (new URLSearchParams(window.location.search).has('zonas')) {
  escenario.classList.add('ver-zonas');
}

actualizarMarcadores();