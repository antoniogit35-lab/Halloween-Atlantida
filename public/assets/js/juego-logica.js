// ------------------------------------------------------------
// LÓGICA DEL JUEGO (sin interfaz)
// Una «carta» es un texto con el formato «tipo:id», por ejemplo
// «arma:machete» o «habitacion:bano».
// ------------------------------------------------------------

function carta(tipo, id) {
  return tipo + ':' + id;
}

function separarCarta(texto) {
  const [tipo, id] = texto.split(':');
  return { tipo, id };
}

// Comprueba que un id existe dentro de su categoría
function existe(tipo, id) {
  return CATEGORIAS[tipo].some((elemento) => elemento.id === id);
}

function comprobarId(tipo, id) {
  if (!existe(tipo, id)) {
    throw new Error('No existe ' + tipo + ': ' + id);
  }
}

function elegirAlAzar(lista) {
  return lista[Math.floor(Math.random() * lista.length)];
}

// Devuelve una copia mezclada (algoritmo de Fisher-Yates)
function mezclar(lista) {
  const copia = [...lista];
  for (let i = copia.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [copia[i], copia[j]] = [copia[j], copia[i]];
  }
  return copia;
}

// ------------------------------------------------------------
// Crea una partida nueva: elige la solución y reparte el resto
// ------------------------------------------------------------
function crearPartida() {
  // 1. Se eligen en secreto un sospechoso, un arma y una habitación
  const solucion = {
    sospechoso: elegirAlAzar(SOSPECHOSOS).id,
    arma: elegirAlAzar(ARMAS).id,
    habitacion: elegirAlAzar(HABITACIONES).id,
  };
  const cartasSolucion = Object.entries(solucion).map(([tipo, id]) => carta(tipo, id));

  // 2. Las cartas que no son solución son las que se reparten (15)
  const repartibles = [];
  for (const [tipo, lista] of Object.entries(CATEGORIAS)) {
    for (const elemento of lista) {
      const c = carta(tipo, elemento.id);
      if (!cartasSolucion.includes(c)) {
        repartibles.push(c);
      }
    }
  }

  // Seguridad: los tamaños de mano deben sumar exactamente las cartas a repartir
  const total = TAMANOS_MANO.reduce((suma, n) => suma + n, 0);
  if (total !== repartibles.length || TAMANOS_MANO.length !== SOSPECHOSOS.length) {
    throw new Error('El reparto no coincide con el número de cartas.');
  }

  // 3. Se mezclan las cartas y también los tamaños, para que no sean
  // siempre los mismos sospechosos los que tienen 3 cartas
  const baraja = mezclar(repartibles);
  const tamanos = mezclar(TAMANOS_MANO);
  const manos = {};
  let inicio = 0;
  SOSPECHOSOS.forEach((sospechoso, i) => {
    manos[sospechoso.id] = baraja.slice(inicio, inicio + tamanos[i]);
    inicio += tamanos[i];
  });

  return {
    solucion,
    manos,
    turnosRestantes: TURNOS_INICIALES,
    intentosRestantes: INTENTOS_ACUSACION,
    ultimaOportunidad: false, // pasa a true cuando se acaban los turnos
    estado: 'jugando',        // 'jugando' | 'ganada' | 'perdida'
  };
}

// ------------------------------------------------------------
// Pregunta a un sospechoso por una habitación, un arma y un sospechoso.
// Gasta un turno. Devuelve { interrogado, carta }, donde carta es la que
// enseña el interrogado (una al azar si tiene varias) o null si no tiene ninguna.
// ------------------------------------------------------------
function preguntar(partida, idInterrogado, idHabitacion, idArma, idSospechoso) {
  if (partida.estado !== 'jugando') {
    throw new Error('La partida ya ha terminado.');
  }
  if (partida.turnosRestantes <= 0) {
    throw new Error('No quedan turnos.');
  }
  if (!(idInterrogado in partida.manos)) {
    throw new Error('No existe el sospechoso interrogado.');
  }
  comprobarId('habitacion', idHabitacion);
  comprobarId('arma', idArma);
  comprobarId('sospechoso', idSospechoso);

  const preguntadas = [
    carta('habitacion', idHabitacion),
    carta('arma', idArma),
    carta('sospechoso', idSospechoso),
  ];

  partida.turnosRestantes--;

  // De las tres cartas preguntadas, ¿cuáles tiene el interrogado?
  const coincidencias = partida.manos[idInterrogado].filter((c) => preguntadas.includes(c));
  const mostrada = coincidencias.length > 0 ? elegirAlAzar(coincidencias) : null;

  // Al gastar el último turno queda una única acusación final
  if (partida.turnosRestantes === 0) {
    partida.ultimaOportunidad = true;
  }

  return { interrogado: idInterrogado, carta: mostrada };
}

// ------------------------------------------------------------
// Acusación final. No gasta turno, pero sí un intento.
// Devuelve true si acierta y false si falla (sin dar más pistas).
// ------------------------------------------------------------
function acusar(partida, idSospechoso, idArma, idHabitacion) {
  if (partida.estado !== 'jugando') {
    throw new Error('La partida ya ha terminado.');
  }
  comprobarId('sospechoso', idSospechoso);
  comprobarId('arma', idArma);
  comprobarId('habitacion', idHabitacion);

  const acierto = idSospechoso === partida.solucion.sospechoso
    && idArma === partida.solucion.arma
    && idHabitacion === partida.solucion.habitacion;

  if (acierto) {
    partida.estado = 'ganada';
    return true;
  }

  partida.intentosRestantes--;

  // Se pierde al gastar los 3 intentos o al fallar la última oportunidad
  if (partida.intentosRestantes === 0 || partida.ultimaOportunidad) {
    partida.estado = 'perdida';
  }
  return false;
}