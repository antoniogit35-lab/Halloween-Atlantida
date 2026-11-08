# Cluedo «Asesinato en la mansión del código»: cómo funciona el código

Documento técnico para entender, explicar y defender el juego. No describe el código línea a línea, sino cómo está organizado, qué hace cada pieza y por qué se ha hecho así.

## Índice

1. [Qué hace el juego](#1-qué-hace-el-juego)
2. [Archivos y organización](#2-archivos-y-organización)
3. [Modelo de datos](#3-modelo-de-datos)
4. [La lógica: `juego-logica.js`](#4-la-lógica-juego-logicajs)
5. [La interfaz: `juego.js`](#5-la-interfaz-juegojs)
6. [HTML y CSS](#6-html-y-css)
7. [Decisiones de diseño](#7-decisiones-de-diseño)
8. [Cómo probarlo y depurarlo](#8-cómo-probarlo-y-depurarlo)
9. [Limitaciones y mejoras posibles](#9-limitaciones-y-mejoras-posibles)
10. [Preguntas que pueden hacernos](#10-preguntas-que-pueden-hacernos)
11. [Resumen de funciones](#11-resumen-de-funciones)

## 1. Qué hace el juego

Es un Cluedo individual simplificado, contra el ordenador y sin servidor: todo ocurre en el navegador con JavaScript.

- Hay **6 sospechosos**, **6 armas** y **6 habitaciones**.
- Al empezar se elige al azar **una solución**: un sospechoso, un arma y una habitación.
- Las otras **15 cartas** se reparten entre los 6 sospechosos (tres de ellos reciben 3 cartas y los otros tres, 2).
- El jugador elige una habitación del plano y **interroga** a un sospechoso con una hipótesis (habitación, arma, sospechoso). Si el interrogado tiene alguna de esas tres cartas, **enseña una al azar**; si no, no puede ayudar.
- Cada pregunta gasta **un turno** (25 en total). **Acusar** no gasta turno, pero cada fallo gasta uno de los **3 intentos**.
- Al agotar los turnos, queda **una última acusación**.
- Se gana acertando los tres datos y se pierde al gastar los intentos o al fallar la última acusación.

La clave del juego es la deducción: toda carta que se enseña queda descartada como solución, y el cuaderno (manual) sirve para llevar la cuenta.

## 2. Archivos y organización

| Archivo | Responsabilidad |
|---|---|
| `public/juego.php` | Estructura HTML de la página: cabecera, plano con las seis zonas, panel con pestañas y las ventanas (acusación, final, información) |
| `public/assets/css/juego.css` | Aspecto: disposición, plano con zonas pulsables, panel, ventanas y versión para móvil |
| `public/assets/js/juego-datos.js` | **Datos** y reglas ajustables: constantes, sospechosos, armas y habitaciones |
| `public/assets/js/juego-logica.js` | **Reglas del juego**: crear partida, preguntar y acusar. No toca la página |
| `public/assets/js/juego.js` | **Interfaz**: conecta la lógica con la pantalla (clics, formularios, ventanas) |

Los scripts se cargan con `defer` y **en este orden**, porque cada uno usa lo definido por el anterior:

```text
cursor-arma.js  →  juego-datos.js  →  juego-logica.js  →  juego.js
```

No se usan módulos (`import`/`export`): son scripts clásicos que comparten el ámbito global. Por eso `juego-logica.js` puede usar `CATEGORIAS` o `TURNOS_INICIALES` sin importarlos, y por eso el orden importa. (`cursor-arma.js` es independiente: solo pinta el arma junto al cursor.)

### Separación en tres capas

```text
 DATOS   ──►   LÓGICA   ──►   INTERFAZ
 qué hay       qué reglas      qué se ve
 (constantes)  (funciones)     (DOM y eventos)
```

- La **lógica** no sabe nada de botones ni de HTML: recibe y devuelve datos. Esto permite probarla desde la consola sin interfaz.
- La **interfaz** no decide quién gana: pregunta a la lógica y pinta lo que esta responde.
- Los **datos** se pueden cambiar (turnos, nombres, imágenes) sin tocar el resto.

## 3. Modelo de datos

### Constantes (en `juego-datos.js`)

```js
const TURNOS_INICIALES = 25;
const INTENTOS_ACUSACION = 3;
const TAMANOS_MANO = [3, 3, 3, 2, 2, 2];   // 15 cartas repartidas entre 6 sospechosos
```

Los tamaños de mano suman 15, que son las cartas que quedan después de apartar la solución (18 cartas en total, menos 3). No se pueden repartir en partes iguales (15 entre 6 no es exacto), por eso unos reciben 3 y otros 2.

### Categorías

Cada sospechoso, arma o habitación es un objeto con un `id` interno, un `nombre` visible y, salvo las habitaciones, una `imagen`:

```js
{ id: 'jason-voorhees', nombre: 'Jason Voorhees', imagen: 'jasonvoorhees.jpg' }
```

Las tres listas se agrupan en un objeto para poder recorrerlas con el mismo bucle:

```js
const CATEGORIAS = { sospechoso: SOSPECHOSOS, arma: ARMAS, habitacion: HABITACIONES };
```

### La carta

Una carta es un **texto con el formato `tipo:id`**, por ejemplo `arma:machete` o `habitacion:bano`. Es un formato sencillo que sirve para comparar (`===`, `includes`) y para volver a separar en tipo e id con `separarCarta()`.

### El objeto `partida`

Toda la información de una partida vive en un único objeto, que crea `crearPartida()`:

```js
{
  solucion: { sospechoso: 'chucky', arma: 'machete', habitacion: 'bano' },
  manos: {
    'jason-voorhees': ['arma:globo-rojo', 'habitacion:salon'],
    'chucky':         ['sospechoso:ghostface', 'arma:motosierra', 'habitacion:comedor'],
    // … hasta los 6 sospechosos
  },
  turnosRestantes: 25,
  intentosRestantes: 3,
  ultimaOportunidad: false,   // pasa a true cuando se acaban los turnos
  estado: 'jugando'           // 'jugando' | 'ganada' | 'perdida'
}
```

La interfaz solo lee este objeto y llama a las funciones de la lógica para modificarlo.

## 4. La lógica: `juego-logica.js`

### `crearPartida()`

1. **Elige la solución** con `elegirAlAzar()` en cada categoría.
2. **Construye las 15 cartas repartibles**: recorre las tres categorías y descarta las tres cartas de la solución.
3. **Comprueba** que los tamaños de mano suman exactamente las cartas a repartir y que hay una mano por sospechoso. Si no, lanza un error. Es una red de seguridad por si alguien cambia los datos y rompe el reparto.
4. **Mezcla** las 15 cartas y también el orden de `TAMANOS_MANO` (así no son siempre los mismos quienes tienen 3).
5. **Reparte** cortando la baraja con `slice` según el tamaño de cada mano.
6. Devuelve el objeto `partida` con los contadores en su valor inicial.

`mezclar()` usa el algoritmo de **Fisher-Yates**: recorre la lista desde el final intercambiando cada elemento con uno al azar de los anteriores. Da una mezcla uniforme y no modifica la lista original (trabaja con una copia).

### `preguntar(partida, idInterrogado, idHabitacion, idArma, idSospechoso)`

1. **Valida**: partida en curso, quedan turnos y todos los ids existen. Si algo falla, lanza un error.
2. **Resta un turno.**
3. Construye las **tres cartas de la hipótesis**.
4. Mira cuáles de esas tres están en la mano del interrogado (`filter` + `includes`).
5. Si hay coincidencias, **elige una al azar**; si no, devuelve `null`.
6. Si ya no quedan turnos, marca `ultimaOportunidad = true`.
7. Devuelve `{ interrogado, carta }`.

Detalles de diseño:
- El interrogado **nunca miente** y enseña **solo una carta**, aunque tenga varias: así se parece al Cluedo original y obliga a deducir.
- Si no puede mostrar nada, no demuestra que la hipótesis sea correcta: puede que otro sospechoso tenga esas cartas.
- Repetir la misma pregunta puede dar otra carta distinta, pero cuesta un turno.

### `acusar(partida, idSospechoso, idArma, idHabitacion)`

1. **Valida** igual que `preguntar` (menos los turnos: acusar no los gasta).
2. Compara los tres datos con la solución.
3. Si acierta: `estado = 'ganada'` y devuelve `true`.
4. Si falla: resta un intento. La partida pasa a `'perdida'` si ya no quedan intentos **o** si era la última oportunidad (`ultimaOportunidad`). Devuelve `false` sin dar ninguna pista de qué ha acertado.

### Funciones auxiliares

- `carta(tipo, id)` y `separarCarta(texto)`: crean y descomponen el formato `tipo:id`.
- `existe(tipo, id)` y `comprobarId(tipo, id)`: comprueban que un id es válido y lanzan un error si no.
- `elegirAlAzar(lista)`: devuelve un elemento al azar.

### Por qué lanzar errores (`throw`)

La interfaz ya impide la mayoría de usos incorrectos (desactiva el botón sin turnos, exige elegir los tres datos…), pero la lógica **se protege a sí misma**: si en el futuro otra parte del código la llamara mal, fallaría de forma visible en lugar de dar resultados absurdos. La interfaz envuelve las llamadas en `try/catch` y muestra un mensaje genérico.

## 5. La interfaz: `juego.js`

El archivo está organizado en bloques con comentarios, en el orden en que ocurre la partida.

### Estado y referencias

Al principio se crea la partida (`let partida = crearPartida()`), se guarda la habitación elegida (`habitacionActual`) y se localizan con `getElementById` los elementos de la página que se van a usar.

### Mapa

Las seis zonas del plano son botones de `juego.php` con un `data-habitacion` igual al id de la habitación. Al pulsar uno, `seleccionarHabitacion()` guarda el id, marca el botón con `aria-pressed="true"` (el CSS lo pinta de rojo) y actualiza el texto del panel y de la barra de estado.

### Panel con pestañas

`activarPestana()` muestra la sección que toca y oculta la otra con el atributo `hidden`. Usa los atributos ARIA `role="tab"`, `aria-selected` y `aria-controls`, y responde a las flechas izquierda y derecha del teclado.

### Opciones con miniatura

`construirOpciones()` y `construirOpcionesTexto()` crean con JavaScript los botones de elección (de tipo radio) a partir de las listas de datos, en lugar de escribirlos a mano. Así, si se añade un sospechoso, aparece solo en todos los sitios. Se usan los **radios nativos** del navegador (con la imagen encima y el control invisible) porque ya dan gratis el comportamiento de «solo uno a la vez», el teclado y la lectura por pantalla.

### Interrogar

Al enviar el formulario (`submit`) se evita la recarga con `preventDefault()`, se leen los valores con `FormData`, se comprueba que haya habitación y que estén elegidos los tres datos, y se llama a `preguntar()`. Con el resultado se actualizan los contadores y se muestra la respuesta (`mostrarResultadoPregunta`, `crearCartaMostrada`). Los contadores se pintan en `actualizarMarcadores()` y el botón se desactiva en `actualizarBotonPreguntar()` cuando no quedan turnos.

### Cuaderno

Las listas para tachar son casillas (`checkbox`). El **tachado lo hace el CSS** con el selector `input:checked + span`, sin una línea de JavaScript para el efecto. El cuaderno es **manual a propósito**: el juego no marca nada por el jugador, que debe razonar y anotar.

### Acusación

El botón «Acusar» abre una ventana `<dialog>` con `showModal()`. Al enviar, se llama a `acusar()`. Si la partida sigue, se muestra «Acusación incorrecta» y se actualiza el aviso de intentos. Si ha terminado (ganada o perdida), se cierra la ventana y se llama a `mostrarFinal()`.

### Pantalla final

`mostrarFinal()` elige el GIF según el sospechoso de la solución y el resultado, siguiendo el patrón `id-victoria.gif` o `id-derrota.gif`, y revela la solución. La ventana no se cierra con Escape (se cancela el evento `cancel`) para obligar a elegir entre jugar de nuevo o volver.

### Reinicio

`reiniciarPartida()` crea una partida nueva y deja la pantalla como al principio: restablece los formularios, borra la respuesta, las notas y las casillas, vuelve a la pestaña «Interrogar» y cierra la ventana.

### Botón de información

La «i» abre una ventana con las reglas. Los números de turnos e intentos se rellenan desde las constantes, así que siempre coinciden con las reglas reales.

### Modo ajuste

Añadiendo `?zonas` a la dirección se muestran las zonas pulsables del plano con un borde azul. Es una ayuda para ajustar sus posiciones.

## 6. HTML y CSS

### El plano con zonas pulsables

El plano es una imagen de proporción fija (1672 × 941). Las seis zonas son botones con posición absoluta cuyos valores son **porcentajes de la imagen**, definidos con variables CSS:

```css
.sala[data-habitacion="biblioteca"] { --x: 14.4%; --y: 4.8%; --ancho: 24.8%; --alto: 34%; }
```

Como los valores son porcentajes, las zonas **siguen a la imagen** aunque cambie de tamaño. El contenedor mantiene la proporción con `aspect-ratio` y `container-type: inline-size`, lo que permite usar la unidad `cqw` (un 1 % del ancho del contenedor) para que textos y etiquetas escalen con el plano.

### Distribución

En escritorio, el área principal es una rejilla (`display: grid`) con dos columnas: el plano y el panel de 24 rem. El botón «Ocultar panel» añade una clase al `<body>` que quita la columna. En pantallas verticales (móvil), un `@media (max-aspect-ratio: 1/1)` apila el plano arriba y el panel debajo, con scroll.

### Ventanas

Las tres ventanas (acusación, final e información) son elementos `<dialog>` nativos: `showModal()` las abre en la capa superior con fondo oscurecido, gestiona el foco y se cierran con Escape (salvo la final).

## 7. Decisiones de diseño

| Decisión | Motivo |
|---|---|
| Lógica separada de la interfaz | Se puede probar desde la consola y entender cada parte por separado |
| Datos en un archivo aparte | Ajustar turnos, nombres o imágenes sin tocar el código |
| Cartas como texto `tipo:id` | Formato simple para comparar y guardar |
| Una sola carta por respuesta, elegida al azar | Fiel al Cluedo original; obliga a deducir |
| Cuaderno manual | Más detectivesco y mucho más sencillo de programar y explicar |
| Acusar no gasta turno, pero sí intentos | Separa investigar de arriesgarse a acusar |
| Una última acusación al acabar los turnos | Evita que la partida termine sin poder dar la conclusión |
| `textContent` y `createElement` en lugar de `innerHTML` | Evita inyectar HTML por accidente |
| Radios y casillas nativos | Accesibilidad y teclado gratis |
| Sin ranking de jugadores | La solución vive en el navegador (ver apartado 9) |

## 8. Cómo probarlo y depurarlo

### En la consola del navegador (F12)

Con la página del juego abierta, las funciones y la partida son globales:

```js
partida                 // estado completo de la partida
partida.solucion        // la solución (solo para depurar)
partida.manos           // las cartas de cada sospechoso
preguntar(partida, 'chucky', 'bano', 'machete', 'ghostface')   // una pregunta a mano
partida.turnosRestantes // debe haber bajado en uno
```

Si se hacen preguntas desde la consola, el panel no se actualiza hasta la siguiente acción de la interfaz.

### Comprobación del reparto

Este fragmento pegado en la consola simula 2.000 partidas y avisa solo si algún reparto es incorrecto:

```js
for (let n = 0; n < 2000; n++) {
  const p = crearPartida();
  const todas = Object.values(p.manos).flat();
  const solucion = Object.entries(p.solucion).map(([tipo, id]) => carta(tipo, id));

  console.assert(todas.length === 15, 'Deben repartirse 15 cartas');
  console.assert(new Set(todas).size === 15, 'No puede haber cartas repetidas');
  console.assert(!solucion.some((c) => todas.includes(c)), 'La solución no se reparte');
  console.assert(
    Object.values(p.manos).map((m) => m.length).sort().join('') === '222333',
    'Las manos deben ser 3-3-3-2-2-2'
  );
}
console.log('Reparto correcto');
```

Durante el desarrollo se comprobó además el final de partida: al agotar los turnos queda `ultimaOportunidad`, fallar esa acusación pierde, tres fallos pierden y acertar gana.

### Ajustar las zonas del plano

Abrir `juego.php?zonas` y modificar las cuatro cifras de la zona que haga falta en `juego.css`.

## 9. Limitaciones y mejoras posibles

- **La solución está en el navegador.** Cualquiera puede verla desde la consola (`partida.solucion`). Para un juego de clase no importa, pero es la razón de que no haya ranking de jugadores: un ranking sin trampas exigiría que el servidor eligiera la solución y comprobara las acusaciones.
- **La partida no se guarda.** Recargar la página empieza otra. Se podría guardar el estado en `localStorage`.
- **Equilibrio de los turnos.** Con una sola carta por pregunta y 25 turnos, el juego es exigente. En una simulación sencilla de un jugador que pregunta al azar, sin deducir, la probabilidad de poder acertar es baja, por lo que se dejó `TURNOS_INICIALES` como constante fácil de cambiar tras las partidas de prueba.
- **Sin inteligencia artificial en los sospechosos.** Solo responden con las cartas que tienen. No se mueven ni compiten.
- **Un solo mapa y reglas fijas.** Añadir más habitaciones exigiría cambiar `TAMANOS_MANO` para que el reparto siga cuadrando.

## 10. Preguntas que pueden hacernos

**¿Cómo se asegura que el reparto es correcto?**
`crearPartida()` comprueba que los tamaños de mano suman las cartas a repartir y lanza un error si no. Además, cada reparto aparta primero la solución, de modo que ninguna carta solución puede acabar en una mano.

**¿Por qué una carta es un texto y no un objeto?**
Porque solo necesitamos identificarla y compararla. Un texto `tipo:id` se compara con `===` y se busca con `includes`, y se puede volver a separar con `separarCarta()`.

**¿Qué pasa si se cambia el número de sospechosos, armas o habitaciones?**
Hay que ajustar `TAMANOS_MANO` para que sume (total de cartas − 3). Si no, `crearPartida()` avisa con un error.

**¿Por qué el cuaderno no se rellena solo?**
Es una decisión de diseño: el objetivo es que el jugador razone, y además mantiene el código más simple.

**¿Qué diferencia hay entre turnos e intentos?**
Un turno se gasta al *preguntar*. Un intento se gasta al *acusar mal*. Se pierde al gastar todos los intentos o al fallar la última acusación tras quedarse sin turnos.

**¿Cómo se comunican la lógica y la interfaz?**
La interfaz llama a `preguntar` y `acusar` pasándoles el objeto `partida`; estas funciones lo modifican y devuelven el resultado, y la interfaz pinta lo que cambió. La lógica nunca toca el DOM.

**¿Por qué no hay módulos de JavaScript?**
Por sencillez: son scripts clásicos que comparten el ámbito global y se cargan en orden. Sería una mejora usar `import`/`export` o agruparlos en una única clase.

**¿Cómo se evita inyectar código en la página?**
Los textos se insertan con `textContent` y los elementos se crean con `createElement`, nunca se compone HTML a partir de cadenas.

**¿Se puede hacer trampa?**
Sí, mirando `partida.solucion` en la consola. Por eso no hay ranking.

## 11. Resumen de funciones

### `juego-logica.js`

| Función | Qué hace |
|---|---|
| `carta(tipo, id)` | Crea el texto `tipo:id` |
| `separarCarta(texto)` | Devuelve `{ tipo, id }` a partir de un texto de carta |
| `existe(tipo, id)` / `comprobarId(tipo, id)` | Comprueba (o exige) que un id sea válido |
| `elegirAlAzar(lista)` | Elemento aleatorio de una lista |
| `mezclar(lista)` | Copia mezclada de una lista (Fisher-Yates) |
| `crearPartida()` | Elige la solución, reparte las cartas y devuelve la partida |
| `preguntar(...)` | Gasta un turno y devuelve la carta que enseña el interrogado, o `null` |
| `acusar(...)` | Comprueba la acusación, gasta un intento si falla y actualiza el estado |

### `juego.js`

| Función | Qué hace |
|---|---|
| `buscarElemento` / `nombreHabitacion` | Buscan un elemento o un nombre a partir de un id |
| `crearParrafo` | Crea un párrafo con `textContent` |
| `actualizarMarcadores` / `actualizarBotonPreguntar` | Pintan contadores y activan o desactivan el botón |
| `seleccionarHabitacion` | Marca la habitación pulsada en el plano |
| `activarPestana` | Cambia entre «Interrogar» y «Cuaderno» |
| `construirOpciones` / `construirOpcionesTexto` | Crean las opciones elegibles a partir de los datos |
| `mostrarRespuesta` / `crearCartaMostrada` / `mostrarResultadoPregunta` | Muestran lo que responde el interrogado |
| `construirListaTachar` | Crea las casillas del cuaderno |
| `textoAvisoAcusacion` / `abrirAcusacion` | Preparan y abren la ventana de acusación |
| `mostrarFinal` | Muestra la pantalla de victoria o derrota con su GIF |
| `reiniciarPartida` | Empieza de nuevo, con todo restablecido |
