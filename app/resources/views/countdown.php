<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%23ff6b00' width='100' height='100'/><text x='50' y='65' font-size='60' font-weight='bold' fill='white' text-anchor='middle' font-family='Arial'>⏱</text></svg>">
    <link rel="stylesheet" href="/public/css/countdown.css">
    <link rel="stylesheet" href="/public/css/main.css">
    <script src="/public/js/main.js" defer></script>
    <title>Countdown - Martin Garrix</title>
</head>
<body>
    <header class="site-header">
        <div class="container header-bar">
            <a class="brand" href="/" aria-label="Ir al inicio">
                <img class="brand-logo" src="/assets/EMCR.png" alt="EMCR">
                <span class="brand-text">
                    <strong>ElectroMusicCR</strong>
                    <small>Musica electronica en Costa Rica</small>
                </span>
            </a>
            <button class="nav-toggle" type="button" aria-label="Abrir menú de navegación" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
            </button>
            <nav class="site-nav" id="site-nav" aria-label="Navegacion principal" data-site-nav>
                <a href="https://electromusiccr.42web.io/">Volver al inicio</a>
                
            </nav>
        </div>
    </header>
    <div class="countdown-container">
        <video class="background-video desktop-video" autoplay muted loop>
            <source src="/assets/videos/MartinGarrixPubli.mp4" type="video/mp4">
        </video>
        <video class="background-video mobile-video" autoplay muted loop>
            <source src="/assets/videos/MartingarrixMobiles.mp4" type="video/mp4">
        </video>
        <div class="dark-filter"></div>
        
        <div class="content">
            <h1 class="artist-name">MARTIN GARRIX</h1>
            <p class="tour-title">Americas Tour 2026</p>
            
            <div class="countdown-display">
                <div class="countdown-grid">
                    <div class="countdown-unit">
                        <div class="countdown-value" id="countdown-days">00</div>
                        <div class="countdown-label">Días</div>
                    </div>
                    <div class="countdown-unit">
                        <div class="countdown-value" id="countdown-hours">00</div>
                        <div class="countdown-label">Horas</div>
                    </div>
                    <div class="countdown-unit">
                        <div class="countdown-value" id="countdown-minutes">00</div>
                        <div class="countdown-label">Minutos</div>
                    </div>
                </div>
                <div class="countdown-date">
                    <p id="event-date">27 de Noviembre, 2026</p>
                    <p id="event-date">Parque Viva</p>
                </div>
            </div>
            
            <div class="event-info">
                <div class="image-section">
                    <img src="/assets/entradas.jpg" alt="Entradas Disponibles" class="event-image">
                    <p class="image-caption">7 Zonas Sold Out</p>
                </div>
                
                <div class="purchase-section">
                    <h2 class="purchase-title">Entradas SmarTicket.net</h2>
                    <a href="https://smarticket.net/evento.php?id=204&fbclid=IwVERTSAUDxotwZG9mBWV4dG4DYWVtAjEwAHNydGMGYXBwX2lkDDM1MDY4NTUzMTcyOAABHhhanSef7oTiOv1DMmfE_z1rebkPGotuIOg0u28ZLxQuHYf2vYevKJl6jhw-_aem_ebrrkcJRdmEXOms9E5Qkdg&sfnsn=wa" class="purchase-button" id="purchase-link">Comprar Entradas</a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <p>&copy; Desarrollado en Costa Rica.</p>
        <p>Esta no es una página registrada</p>
        <p>Para contacto: estivenhm1@gmail.com</p>
    </div>

    <!--
    
     Este codigo es open source
     
     Si llegaste hasta aquí, ¡gracias por revisar el código!

     Si quieres una página visual puedes contactarme a mi correo: estivenhm1@gmail.com

     Desarollador Junior Free lancer

    
    -->
    
    <script src="/public/js/countdown.js"></script>
</body>
</html>