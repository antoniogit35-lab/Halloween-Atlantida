# Halloween Atlántida

Proyecto web de temática Halloween para Atlántida, orientado a una actividad o concurso.

El proyecto está en desarrollo. Actualmente cuenta con un recetario funcional llamado «Recetas malditas».

## Estado actual

El recetario permite:

- Consultar un catálogo de 30 recetas.
- Buscar recetas por nombre, sin distinguir mayúsculas ni tildes.
- Consultar fotografías, dificultad y tiempo en las tarjetas.
- Abrir una ficha con fotografía, dificultad, tiempo, personas, ingredientes y preparación.
- Mostrar un marcador provisional cuando una fotografía no está disponible.

Los datos se mantienen en un CSV que PHP lee al generar la página. No hay que modificar una copia de las recetas dentro del HTML o del JavaScript.

El recetario actual no utiliza base de datos.

## Tecnologías

- PHP para leer el CSV y generar los datos de la página.
- HTML y CSS para la estructura y presentación.
- JavaScript para la búsqueda, las tarjetas y la ficha de cada receta.
- Apache mediante XAMPP para el entorno local.
- Git y GitHub para el control de versiones.

## Estructura principal

```text
Halloween-Atlantida/
├── app/
│   └── recetas.php              # Lectura y validación del CSV
├── docs/                        # Documentación del proyecto
├── public/                      # Raíz pública del servidor
│   ├── recetario.php
│   └── assets/
│       ├── css/
│       │   └── recetario.css
│       ├── img/
│       │   ├── background.jpg
│       │   └── recetas/         # Fotografías JPG utilizadas en la web
│       └── js/
│           └── recetario.js
├── recursos/
│   └── datos/
│       └── recetas.csv          # Fuente de datos del recetario
└── README.md
```

Solo `public/` debe configurarse como raíz pública de Apache. Las carpetas `app/` y `recursos/` quedan fuera de ella.

## Requisitos del entorno local

- Git.
- XAMPP con Apache y PHP; se recomienda PHP 8.x.
- Un navegador actualizado.

Para trabajar en el recetario solo es necesario arrancar Apache. No hace falta iniciar el módulo MySQL de XAMPP.

## Preparación del proyecto

### 1. Clonar el repositorio

Desde la carpeta en la que se quiera guardar el proyecto:

```powershell
git clone --branch chore/estructura-proyecto [https://github.com/antoniogit35-lab/Halloween-Atlantida.git](https://github.com/antoniogit35-lab/Halloween-Atlantida.git)
cd Halloween-Atlantida
```

La rama de trabajo utilizada durante esta reorganización es `chore/estructura-proyecto`.

Si el repositorio ya está clonado, primero comprueba que no tienes cambios locales pendientes y después actualiza:

```powershell
git fetch origin
git switch chore/estructura-proyecto
git pull
```

### 2. Configurar Apache

Los siguientes ejemplos suponen que XAMPP está instalado en `C:/xampp`. Adapta las rutas si tu instalación utiliza otra ubicación.

Edita:

```text
C:\xampp\apache\conf\extra\httpd-vhosts.conf
```

Añade el VirtualHost del proyecto, sustituyendo `C:/ruta/Halloween-Atlantida` por la ubicación real del repositorio:

```apache
<VirtualHost *:80>
    ServerName halloween-atlantida.test
    DocumentRoot "C:/ruta/Halloween-Atlantida/public"

    <Directory "C:/ruta/Halloween-Atlantida/public">
        Options -Indexes
        AllowOverride All
        Require local
    </Directory>
</VirtualHost>
```

Conserva los VirtualHosts existentes. Si quieres mantener `localhost` apuntando a la página habitual de XAMPP y aún no tienes un bloque para él, coloca este antes del VirtualHost del proyecto:

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot "C:/xampp/htdocs"

    <Directory "C:/xampp/htdocs">
        Require local
    </Directory>
</VirtualHost>
```

Comprueba que `C:\xampp\apache\conf\httpd.conf` contiene esta línea sin comentar:

```apache
Include conf/extra/httpd-vhosts.conf
```

### 3. Configurar el nombre local

Abre como administrador el archivo:

```text
C:\Windows\System32\drivers\etc\hosts
```

Añade, sin borrar las entradas existentes:

```text
127.0.0.1 halloween-atlantida.test
```

Guarda el archivo sin añadir una extensión `.txt`.

### 4. Validar y arrancar Apache

En PowerShell:

```powershell
& "C:\xampp\apache\bin\httpd.exe" -t
```

Si aparece `Syntax OK`, inicia o reinicia Apache desde el panel de XAMPP.

Abre:

```text
http://halloween-atlantida.test/recetario.php
```

La página necesita que PHP se ejecute en el servidor: no debe abrirse haciendo doble clic sobre `recetario.php`.

La configuración de Apache y el archivo `hosts` son locales a cada ordenador. No se suben al repositorio.

## Edición de las recetas

La fuente de datos es:

```text
recursos/datos/recetas.csv
```

El archivo utiliza UTF-8 y las siguientes columnas, en este orden:

```text
nombre,foto,tiempo,dificultad,ingredientes,preparación,personas
```

Al editarlo:

- Conserva los nombres y el orden de las columnas.
- Mantén la codificación UTF-8.
- Utiliza un editor que respete el formato CSV, incluidos los campos entre comillas.
- No sustituyas los separadores internos de ingredientes y preparación sin adaptar también el JavaScript.

Actualmente, ingredientes y preparación utilizan caracteres de ancho cero U+200B como separadores. El JavaScript también admite saltos de línea para dividir estos apartados.

PHP lee el CSV al solicitar la página y genera el bloque JSON que consume JavaScript. Después de guardar cambios en el CSV, recarga la página para consultarlos.

## Fotografías de las recetas

Las imágenes utilizadas por la página se guardan en:

```text
public/assets/img/recetas/
```

La columna `foto` del CSV contiene únicamente el nombre del archivo:

```text
receta_01.jpg
```

El JavaScript construye su ruta pública:

```text
assets/img/recetas/receta_01.jpg
```

El nombre y la extensión deben coincidir con los del archivo, respetando también las mayúsculas y minúsculas.

### Ajustes utilizados para la conversión

- Formato JPG.
- Calidad 80.
- Lado más largo de 1200 píxeles como máximo.
- Proporciones originales conservadas.
- Sin ampliar imágenes pequeñas.
- Sin recorte automático.

Estos ajustes son una convención del proyecto, no una restricción del código.

Las fotografías finales del recetario están en
`public/assets/img/recetas/`. Los WebP utilizados durante la conversión
se han retirado del árbol de trabajo.

Las imágenes de personajes están en `public/assets/img/personajes/`.
Se utilizarán en la selección del asesino del login, el ranking y,
para seis personajes, el Cluedo.

Las imágenes de armas y objetos asociados están en
`public/assets/img/cursores/`, pendientes de su adaptación para el
cursor de cada asesino.

Cambiar la extensión de un archivo no convierte su formato: utiliza una herramienta de conversión.

## Fondo del recetario

El fondo está en:

```text
public/assets/img/background.jpg
```

La hoja `public/assets/css/recetario.css` lo referencia mediante:

```css
url("../img/background.jpg")
```

## Comprobaciones antes de guardar cambios

### Sintaxis PHP

Desde la raíz del repositorio:

```powershell
& "C:\xampp\php\php.exe" -l "app/recetas.php"
& "C:\xampp\php\php.exe" -l "public/recetario.php"
```

### Revisión manual

- La página muestra las recetas.
- El buscador funciona con y sin tildes.
- Una búsqueda sin coincidencias muestra el estado vacío.
- Las fotografías aparecen en las tarjetas y en las fichas.
- Las fichas muestran ingredientes, preparación y personas.
- El modal se abre y se cierra correctamente.
- El contenido sigue siendo utilizable en una ventana estrecha.

### Control de versiones

Antes de crear un commit:

```powershell
git status
git diff
```

Prepara únicamente los archivos correspondientes al cambio y revisa el contenido preparado:

```powershell
git diff --cached
```

No subas contraseñas, credenciales, archivos temporales ni configuraciones personales del equipo.

## Alcance de esta documentación

Este README describe el entorno local y el recetario actualmente implementado.

Las futuras secciones, reglas del concurso y posibles necesidades de base de datos se documentarán conforme se incorporen al proyecto.