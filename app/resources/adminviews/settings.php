<?php

declare(strict_types=1);

$user ??= [];
$csrfToken ??= '';
$username = trim((string) ($user['username'] ?? ''));
?><!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <title>Ajustes | ElectroMusicCR</title>
    <link rel="stylesheet" href="/public/css/main.css">
    <script src="/public/js/admin.js" defer></script>
</head>
<body class="admin-body" data-admin-app>
    <div class="admin-layout" data-admin-shell>
        <aside class="admin-sidebar" aria-label="Menu administrativo">
            <a class="admin-brand" href="/">ElectroMusicCR <span>ADMIN</span></a>
            <div class="admin-user-card">
                <strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></strong>
                <small><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></small>
            </div>
            <nav class="admin-nav">
                <button type="button" data-admin-panel="home" class="is-active">Resumen</button>
                <button type="button" data-admin-panel="news">Novedades</button>
                <button type="button" data-admin-panel="events">Eventos</button>
                <button type="button" data-admin-panel="trivia">Trivia</button>
                <button type="button" data-admin-panel="profile">Mi perfil</button>
            </nav>
            <form method="post" action="/admin/logout" class="admin-logout">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit">Cerrar sesión</button>
            </form>
        </aside>
        <main class="admin-main">
            <div class="admin-main__heading">
                <p class="eyebrow">Panel de control</p>
                <h1 data-admin-title>Resumen</h1>
                <p class="admin-status" data-admin-status role="status"></p>
            </div>
            <section class="admin-content" data-admin-content aria-live="polite">
                <p>Cargando el panel administrativo...</p>
            </section>
        </main>
    </div>
</body>
</html>