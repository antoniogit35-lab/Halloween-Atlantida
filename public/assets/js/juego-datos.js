// ------------------------------------------------------------
// DATOS DEL JUEGO «ASESINATO EN LA MANSIÓN DEL CÓDIGO»
// ------------------------------------------------------------

// Reglas que se pueden ajustar al probar el equilibrio
const TURNOS_INICIALES = 25;
const INTENTOS_ACUSACION = 3;

// Las 15 cartas que no son solución se reparten así entre los 6 sospechosos
// (la suma debe ser 15). El orden se mezcla en cada partida.
const TAMANOS_MANO = [3, 3, 3, 2, 2, 2];

// Los ids coinciden con los de personajes.json
const SOSPECHOSOS = [
  { id: 'jason-voorhees', nombre: 'Jason Voorhees', imagen: 'jasonvoorhees.jpg' },
  { id: 'michael-myers',  nombre: 'Michael Myers',  imagen: 'michaelmyers.jpg' },
  { id: 'chucky',         nombre: 'Chucky',         imagen: 'chucky.jpg' },
  { id: 'freddy-krueger', nombre: 'Freddy Krueger', imagen: 'freddyKrueger.jpg' },
  { id: 'pennywise',      nombre: 'Pennywise',      imagen: 'pennywise.jpg' },
  { id: 'ghostface',      nombre: 'Ghostface',      imagen: 'ghostface.jpg' },
];

const ARMAS = [
  { id: 'machete',            nombre: 'Machete',               imagen: 'machete.jpg' },
  { id: 'motosierra',         nombre: 'Motosierra',            imagen: 'motosierra.jpg' },
  { id: 'cuchillo-carnicero', nombre: 'Cuchillo de carnicero', imagen: 'cuchillo-carnicero.jpg' },
  { id: 'guante-cuchillas',   nombre: 'Guante de cuchillas',   imagen: 'guante-cuchillas.jpg' },
  { id: 'globo-rojo',         nombre: 'Globo rojo',            imagen: 'globo-rojo.jpg' },
  { id: 'cuchillo-ghostface', nombre: 'Cuchillo de Ghostface', imagen: 'cuchillo-ghostface.jpg' },
];

const HABITACIONES = [
  { id: 'biblioteca', nombre: 'Biblioteca' },
  { id: 'dormitorio', nombre: 'Dormitorio' },
  { id: 'bano',       nombre: 'Baño' },
  { id: 'salon',      nombre: 'Salón' },
  { id: 'comedor',    nombre: 'Comedor' },
  { id: 'vestibulo',  nombre: 'Vestíbulo' },
];

// Para recorrer las tres categorías con un mismo bucle
const CATEGORIAS = {
  sospechoso: SOSPECHOSOS,
  arma: ARMAS,
  habitacion: HABITACIONES,
};