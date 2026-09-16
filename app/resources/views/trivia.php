<?php

declare(strict_types=1);
?><!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Trivia diaria de ElectroMusicCR.">
    <title>Trivia | ElectroMusicCR</title>
    <link rel="stylesheet" href="/public/css/main.css">
    <script src="/public/js/trivia.js" defer></script>
</head>
<body>
<header class="site-header">
    <div class="container header-bar">
        <a class="brand" href="/" aria-label="Ir al inicio">
            <span class="brand-text"><strong>ElectroMusicCR</strong><small>Trivia diaria</small></span>
        </a>
        <nav class="site-nav" aria-label="Navegacion principal">
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
            <input id="nickname" name="nickname" type="text" maxlength="40" autocomplete="nickname" required>
            <label for="recovery-code" data-recovery-label>Codigo de acceso</label>
            <input id="recovery-code" name="recovery_code" type="password" maxlength="128" autocomplete="one-time-code">
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
</body>
</html>
