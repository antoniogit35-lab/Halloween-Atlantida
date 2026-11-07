// Pruebas automáticas de la lógica. Se ejecutan escribiendo autoprueba() en la consola.
function autoprueba(partidas = 2000) {
  let errores = 0;
  const fallo = (mensaje) => {
    errores++;
    console.error('FALLO:', mensaje);
  };

  // 1. El reparto es correcto en muchas partidas
  for (let n = 0; n < partidas; n++) {
    const p = crearPartida();
    const todas = Object.values(p.manos).flat();
    const solucion = Object.entries(p.solucion).map(([tipo, id]) => carta(tipo, id));

    if (todas.length !== 15) fallo('no se reparten 15 cartas');
    if (new Set(todas).size !== 15) fallo('hay cartas repetidas');
    if (solucion.some((c) => todas.includes(c))) fallo('una carta de la solución está en una mano');

    const tamanos = Object.values(p.manos).map((m) => m.length).sort().join('');
    if (tamanos !== '222333') fallo('tamaños de mano incorrectos: ' + tamanos);
  }

  // 2. Al agotar los turnos queda una última oportunidad, y fallarla pierde
  const p = crearPartida();
  const otro = (tipo, id) => CATEGORIAS[tipo].find((e) => e.id !== id).id;
  const mal = [
    otro('sospechoso', p.solucion.sospechoso),
    otro('arma', p.solucion.arma),
    otro('habitacion', p.solucion.habitacion),
  ];
  for (let i = 0; i < TURNOS_INICIALES; i++) {
    preguntar(p, SOSPECHOSOS[0].id, 'bano', 'machete', 'chucky');
  }
  if (p.turnosRestantes !== 0 || !p.ultimaOportunidad) fallo('al acabar los turnos debería quedar la última oportunidad');
  try {
    preguntar(p, SOSPECHOSOS[0].id, 'bano', 'machete', 'chucky');
    fallo('se pudo preguntar sin turnos');
  } catch (e) { /* es lo esperado */ }
  acusar(p, ...mal);
  if (p.estado !== 'perdida') fallo('fallar la última oportunidad debería perder');

  // 3. Con turnos disponibles, hay 3 intentos
  const q = crearPartida();
  acusar(q, ...mal);
  acusar(q, ...mal);
  if (q.estado !== 'jugando' || q.intentosRestantes !== 1) fallo('tras 2 fallos debe quedar 1 intento');
  acusar(q, ...mal);
  if (q.estado !== 'perdida') fallo('gastar 3 intentos debería perder');

  // 4. Acertar gana
  const r = crearPartida();
  if (acusar(r, r.solucion.sospechoso, r.solucion.arma, r.solucion.habitacion) !== true || r.estado !== 'ganada') {
    fallo('acertar debería ganar');
  }

  console.log(errores === 0 ? 'Todas las pruebas correctas' : errores + ' fallos');
}