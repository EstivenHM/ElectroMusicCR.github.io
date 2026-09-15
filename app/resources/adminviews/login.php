<?php

declare(strict_types=1);

$csrfToken ??= '';
$error ??= null;
?><!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso administrativo | ElectroMusicCR</title>
    <link rel="stylesheet" href="/public/css/main.css">
</head>
<body class="admin-body">
<main class="admin-auth">
    <section class="admin-auth__panel" aria-labelledby="login-title">
        <p class="eyebrow">ElectroMusicCR</p>
        <h1 id="login-title">Acceso administrativo</h1>
        <p>Inicia sesión con una cuenta autorizada para administrar el contenido.</p>
        <?php if (!empty($error)): ?>
            <p class="form-message form-message--error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" action="/admin/login" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <label for="admin-username">Usuario</label>
            <input id="admin-username" name="username" type="text" autocomplete="username" required>
            <label for="admin-password">Contraseña</label>
            <input id="admin-password" name="password" type="password" autocomplete="current-password" required>
            <button class="button button--primary" type="submit">Iniciar sesión</button>
        </form>
    </section>
</main>
</body>
</html>