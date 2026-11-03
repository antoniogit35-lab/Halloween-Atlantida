// La copia integrada del CSV permite abrir la prueba sin servidor.
const recetas = JSON.parse(document.getElementById('datos-iniciales').textContent);
const grid = document.getElementById('grid');
const buscador = document.getElementById('buscador');
const ficha = document.getElementById('ficha');
let origen;
const normalizar = texto => String(texto || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
function elemento(tag, texto, clase) {
  const e = document.createElement(tag);
  if (texto !== undefined) e.textContent = texto;
  if (clase) e.className = clase;
  return e;
}
function foto(receta) {
  const bloque = elemento('div', undefined, 'foto placeholder');
  bloque.innerHTML = '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 12h18q6 0 6 5q0-5 6-5h18v40H38q-6 0-6 4q0-4-6-4H8zM32 17v39M14 22h11M14 29h11M39 22h11M39 29h11"/></svg>';
  bloque.append(elemento('small','Imagen pendiente'));
  // Solo se intenta cargar una imagen cuando haya un nombre de archivo.
  if (receta.foto) {
    const imagen = new Image();
    imagen.alt = receta.nombre;
    imagen.className = 'foto';
    imagen.onload = () => { bloque.replaceChildren(imagen); bloque.classList.remove('placeholder'); };
    imagen.src = 'assets/img/recetas/' + encodeURIComponent(receta.foto);
  }
  return bloque;
}
function partes(texto) {
  // El CSV separa los apartados con caracteres de ancho cero.
  return String(texto || '').split(/[\u200b\r\n]+/).map(s => s.trim()).filter(Boolean);
}
function abrir(receta, boton) {
  origen = boton;
  document.getElementById('nombre-modal').textContent = receta.nombre;
  document.getElementById('foto-modal').replaceChildren(foto(receta));
  const dl = document.getElementById('datos-modal');
  dl.replaceChildren();
  for (const [etiqueta,valor] of [['Dificultad',receta.dificultad],['Tiempo',receta.tiempo],['Personas',receta.personas]]) {
    const fila = elemento('div');
    fila.append(elemento('dt',etiqueta),elemento('dd',valor || 'No indicado'));
    dl.append(fila);
  }
  for (const [id,texto] of [['ingredientes',receta.ingredientes],['preparacion',receta['preparación']]]) {
    document.getElementById(id).replaceChildren(...partes(texto).map(p => elemento('li',p)));
  }
  ficha.showModal();
  ficha.scrollTop = 0;
  document.body.style.overflow = 'hidden';
  document.getElementById('cerrar').focus();
}
function renderizar() {
  const consulta = normalizar(buscador.value);
  const visibles = recetas.filter(r => normalizar(r.nombre).includes(consulta));
  document.getElementById('contador').textContent = consulta
    ? `${visibles.length} de ${recetas.length} recetas`
    : `${recetas.length} recetas en el recetario`;
  document.getElementById('vacio').hidden = visibles.length !== 0;
  const fragmento = document.createDocumentFragment();
  for (const receta of visibles) {
    const tarjeta = elemento('article',undefined,'tarjeta');
    tarjeta.append(elemento('h3',receta.nombre),foto(receta));
    const metas = elemento('div',undefined,'metas');
    for (const [etiqueta,valor] of [['Dificultad',receta.dificultad],['Tiempo',receta.tiempo]]) {
      const dato = elemento('div');
      dato.append(elemento('span',etiqueta,'etiqueta'),elemento('span',valor,'valor'));
      metas.append(dato);
    }
    const boton = elemento('button','Ver receta','ver');
    boton.type = 'button';
    boton.setAttribute('aria-label',`Ver receta: ${receta.nombre}`);
    const flecha = elemento('span','→'); flecha.setAttribute('aria-hidden','true');
    boton.append(flecha);
    boton.addEventListener('click',() => abrir(receta,boton));
    tarjeta.append(metas,boton);
    fragmento.append(tarjeta);
  }
  grid.replaceChildren(fragmento);
}
document.getElementById('cerrar').addEventListener('click',() => ficha.close());
ficha.addEventListener('click',e => {
  const r = ficha.getBoundingClientRect();
  if (e.target === ficha && (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom)) ficha.close();
});
ficha.addEventListener('close',() => { document.body.style.overflow = ''; origen?.focus(); });
buscador.addEventListener('input',renderizar);
renderizar();
