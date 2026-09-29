<?php

declare(strict_types=1);
?><!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Trivia diaria de ElectroMusicCR.">
    <title>Trivia | ElectroMusicCR</title>
    <link rel="stylesheet" href="/public/css/main.css?v=<?php echo filemtime(__DIR__ . '/../../../public/css/main.css'); ?>">
    <link rel="stylesheet" href="/public/css/rating-widget.css">
    <script src="/public/js/main.js" defer></script>
    <script src="/public/js/trivia.js?v=<?php echo filemtime(__DIR__ . '/../../../public/js/trivia.js'); ?>" defer></script>
</head>
<body>
<header class="site-header">
    <div class="container header-bar">
        <a class="brand" href="/" aria-label="Ir al inicio">
            <span class="brand-text"><strong>ElectroMusicCR</strong><small>Trivia diaria</small></span>
        </a>
        <button class="nav-toggle" type="button" aria-label="Abrir menú de navegación" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
            <span class="nav-toggle__bar"></span>
            <span class="nav-toggle__bar"></span>
            <span class="nav-toggle__bar"></span>
        </button>
        <nav class="site-nav" id="site-nav" aria-label="Navegacion principal" data-site-nav>
            <a href="/">Inicio</a>
            <a class="nav-link--accent" href="/trivia" aria-current="page">Trivia</a>
            <a href="/ranking">Ranking</a>
        </nav>
    </div>
</header>
<main class="section" id="trivia-page">
    <div class="container">
        <div class="section-heading">
            <p class="eyebrow">Reto diario</p>
            <h1>Trivia ElectroMusicCR</h1>
            <p class="lead">Responde desde tu identidad de jugador y conserva tu codigo de acceso.</p>
        </div>
        <section class="trivia-panel" data-trivia-app aria-live="polite">
            <div data-trivia-status class="status-message" role="status">Cargando trivia...</div>
            <div class="trivia-intro" data-trivia-intro hidden>
                <img data-trivia-image alt="">
                <div>
                    <h2 data-trivia-title></h2>
                    <p data-trivia-description></p>
                </div>
            </div>
            <div data-trivia-result class="trivia-result" hidden></div>
            <form data-trivia-form class="question-list" hidden></form>
        </section>
    </div>
</main>
<dialog class="app-modal" data-identity-modal aria-labelledby="identity-modal-title">
    <div class="app-modal__content">
        <h2 id="identity-modal-title">Identificate para participar</h2>
        <p data-identity-description>Selecciona como deseas identificarte para enviar tus respuestas.</p>
        <div class="identity-choice" data-identity-choice>
            <button class="button button--primary" type="button" data-register-player>Registrar nickname</button>
            <button class="button button--ghost" type="button" data-login-player>Ingresar con mi nickname</button>
        </div>
        <form data-player-form novalidate hidden>
            <label for="nickname">Nickname</label>
            <input id="nickname" name="nickname" type="text" maxlength="40" autocomplete="nickname" placeholder="Tu apodo de jugador" required>
            <label for="recovery-code" data-recovery-label>Código de acceso (6 dígitos)</label>
            <input id="recovery-code" name="recovery_code" type="password" inputmode="numeric" pattern="\d{6}" maxlength="6" autocomplete="one-time-code" placeholder="6 dígitos numéricos" required>
            <small style="display:block; margin-top:-0.5rem; margin-bottom:1rem; color:var(--color-muted, #94a3b8); font-size:0.85rem;" data-code-hint>Crea un código numérico de 6 dígitos para ingresar siempre con este nickname.</small>
            <div class="app-modal__actions">
                <button class="button button--ghost" type="button" data-back-identity>Volver</button>
                <button class="button button--primary" type="submit">Continuar</button>
            </div>
        </form>
        <p data-player-message class="form-message" role="alert"></p>
    </div>
</dialog>
<dialog class="app-modal" data-code-modal aria-labelledby="code-modal-title">
    <div class="app-modal__content">
        <h2 id="code-modal-title">Guarda tu codigo</h2>
        <p>Este codigo se muestra una sola vez y no existe recuperacion.</p>
        <output data-recovery-code></output>
        <button class="button button--primary" type="button" data-confirm-code>Ya lo guarde</button>
    </div>
</dialog>
<dialog class="app-modal" data-message-modal aria-labelledby="message-modal-title">
    <div class="app-modal__content">
        <h2 id="message-modal-title" data-modal-title>Mensaje</h2>
        <p data-modal-message></p>
        <div class="modal-feedback" data-modal-feedback></div>
        <button class="button button--primary" type="button" data-close-message>Continuar</button>
    </div>
</dialog>
<dialog class="app-modal app-modal--curious" data-curious-modal aria-labelledby="curious-modal-title">
    <div class="app-modal__content curious-modal__content">
        <header class="curious-modal__header">
            <h2 id="curious-modal-title" data-curious-title>Resultado de la Trivia</h2>
            <p data-curious-score class="curious-modal__score"></p>
        </header>
        <div class="curious-slides" data-curious-slides></div>
        <footer class="curious-modal__footer">
            <div class="curious-nav">
                <button class="button button--ghost" type="button" data-curious-prev>← Anterior</button>
                <span class="curious-nav__counter" data-curious-counter>1 / 3</span>
                <button class="button button--ghost" type="button" data-curious-next>Siguiente →</button>
            </div>
            <div class="curious-modal__actions">
                <button class="button button--primary" type="button" data-close-curious>Continuar</button>
            </div>
        </footer>
    </div>
</dialog>
<script src="/public/js/rating-widget.js" defer></script>
</body>
</html>
