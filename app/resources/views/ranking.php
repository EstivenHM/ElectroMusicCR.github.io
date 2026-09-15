<?php

declare(strict_types=1);
?><!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Ranking global de ElectroMusicCR.">
    <title>Ranking | ElectroMusicCR</title>
    <link rel="stylesheet" href="/public/css/main.css">
    <script src="/public/js/ranking.js" defer></script>
</head>
<body>
<header class="site-header">
    <div class="container header-bar">
        <a class="brand" href="/" aria-label="Ir al inicio">
            <span class="brand-text"><strong>ElectroMusicCR</strong><small>Ranking de trivia</small></span>
        </a>
        <nav class="site-nav" aria-label="Navegacion principal">
            <a href="/trivia">Trivia</a>
            <a class="nav-link--accent" href="/ranking" aria-current="page">Ranking</a>
        </nav>
    </div>
</header>
<main class="section" id="ranking-page">
    <div class="container">
        <div class="section-heading">
            <p class="eyebrow">Clasificacion global</p>
            <h1>Ranking de la comunidad</h1>
            <p class="lead">Puntos acumulados en envios validos de las trivias.</p>
        </div>
        <section class="ranking-panel" aria-labelledby="ranking-title">
            <h2 id="ranking-title" class="visually-hidden">Tabla de posiciones</h2>
            <p class="status-message" data-ranking-status role="status">Cargando ranking...</p>
            <div class="table-scroll">
                <table class="ranking-table">
                    <thead><tr><th scope="col">Posicion</th><th scope="col">Nick</th><th scope="col">Puntos</th></tr></thead>
                    <tbody data-ranking-rows></tbody>
                </table>
            </div>
            <div class="pagination" data-ranking-pagination hidden>
                <button class="button button--ghost" type="button" data-ranking-prev>Anterior</button>
                <span data-ranking-page aria-live="polite"></span>
                <button class="button button--ghost" type="button" data-ranking-next>Siguiente</button>
            </div>
        </section>
    </div>
</main>
</body>
</html>
