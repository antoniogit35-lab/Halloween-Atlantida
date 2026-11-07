<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrada al Cementerio Encantado</title>
    <link rel="stylesheet" href="assets/css/inicio.css">
</head>
<body>
    <main class="escena">
        <div class="lienzo-interactivo">
            <!-- Imagen estática de la escena -->
            <img 
                id="puerta-estatica" 
                class="capa-visual puerta-estatica" 
                src="assets/img/fondos/entrada-cementerio.jpg" 
                alt="Entrada al cementerio con la verja cerrada"
                width="1672"
                height="941"
                fetchpriority="high"
            >

            <!-- Vídeo de la animación -->
            <video 
                id="puerta-video" 
                class="capa-visual puerta-video" 
                playsinline 
                preload="auto"
                muted
            >
                <source src="assets/video/entrada-cementerio.mp4" type="video/mp4">
            </video>

            <!-- Zona de clic exactamente sobre el cartel de la ilustración -->
            <button 
                id="btn-entrar" 
                class="zona-cartel" 
                type="button" 
                aria-label="Entrar al cementerio"
                title="Entrar al cementerio"
            ></button>
        </div>
    </main>

    <script src="assets/js/inicio.js"></script>
</body>
</html>