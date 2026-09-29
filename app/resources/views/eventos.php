<?php

declare(strict_types=1);

$events ??= [];
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Agenda completa de eventos y festivales de música electrónica en Costa Rica por ElectroMusicCR.">
    <title>Eventos | ElectroMusicCR</title>
    <link rel="stylesheet" href="/public/css/main.css?v=<?php echo filemtime(__DIR__ . '/../../../public/css/main.css'); ?>">
    <link rel="stylesheet" href="/public/css/rating-widget.css">
    <script src="/public/js/main.js" defer></script>
</head>
<body>
    <header class="site-header">
        <div class="container header-bar">
            <a class="brand" href="/" aria-label="Ir al inicio">
                <img class="brand-logo" src="/assets/EMCR.png" alt="EMCR">
                <span class="brand-text">
                    <strong>ElectroMusicCR</strong>
                    <small>Música electrónica en Costa Rica</small>
                </span>
            </a>
            <button class="nav-toggle" type="button" aria-label="Abrir menú de navegación" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
            </button>
            <nav class="site-nav" id="site-nav" aria-label="Navegación principal" data-site-nav>
                <a href="/">Inicio</a>
                <a href="/eventos" class="nav-link--accent" aria-current="page">Eventos</a>
                <a href="/trivia">Trivia</a>
                <a href="/ranking">Ranking</a>
                <a href="/countdown">Cuenta regresiva</a>
            </nav>
        </div>
    </header>

    <main id="eventos-page">
        <section class="section section--alt">
            <div class="container">
                <div class="section-heading section-heading--split">
                    <div>
                        <p class="eyebrow">Agenda de Música Electrónica</p>
                        <h1>Próximos Eventos y Festivales</h1>
                        <p>Descubre los eventos confirmados que se realizarán próximamente en Costa Rica.</p>
                    </div>
                    <div>
                        <a class="button button--ghost" href="/">← Volver al inicio</a>
                    </div>
                </div>

                <div class="card-grid card-grid--two" style="margin-top: 2rem;">
                    <?php if (empty($events)): ?>
                        <div class="info-card" style="grid-column: 1 / -1; text-align: center; padding: 3rem 1.5rem;">
                            <h3>No hay eventos próximos por el momento</h3>
                            <p>Estamos coordinando las próximas fechas con la comunidad. ¡Mantente atento o únete a nuestro grupo para no perderte nada!</p>
                            <div style="margin-top: 1.5rem;">
                                <a class="button button--primary" href="https://chat.whatsapp.com/Jqx3g3nkpjT9aPOtSd9LYP" target="_blank" rel="noopener noreferrer">Unirme a WhatsApp</a>
                                <a class="button button--ghost" href="/" style="margin-left: 0.5rem;">Ir al inicio</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <?php
                                $eventImage = !empty($event['image_path']) ? $event['image_path'] : '/assets/placeholder-event.svg';
                                if (!preg_match('#^(https?://|data:|/)#i', $eventImage)) {
                                    $eventImage = '/' . $eventImage;
                                }
                            ?>
                            <article class="news-card">
                                <img src="<?= htmlspecialchars($eventImage, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($event['title'] ?? 'Evento', ENT_QUOTES, 'UTF-8') ?>"
                                     loading="lazy"
                                     onerror="if (this.src.includes('/public/images/')) { this.src = this.src.replace('/public/images/', '/images/'); } else if (this.src.includes('/images/')) { this.src = this.src.replace('/images/', '/public/images/'); } else { this.onerror=null; this.src='/assets/placeholder-event.svg'; }">
                                <div class="news-card__body">
                                    <h3><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                    <?php if (!empty($event['event_date'])): ?>
                                        <p><strong>📅 Fecha:</strong> <?= htmlspecialchars($event['event_date'], ENT_QUOTES, 'UTF-8') ?><?php if (!empty($event['event_time'])): ?> - <?= htmlspecialchars($event['event_time'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($event['location'])): ?>
                                        <p><strong>📍 Lugar:</strong> <?= htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8') ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($event['description'])): ?>
                                        <p><?= nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($event['ticket_url'])): ?>
                                        <p><a class="button button--primary" href="<?= htmlspecialchars($event['ticket_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Comprar entradas</a></p>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div style="text-align: center; margin-top: 3rem;">
                    <a class="button button--ghost" href="/">← Volver al inicio</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-bar">
            <p>&copy; <?= date('Y') ?> ElectroMusicCR. Todos los derechos reservados.</p>
        </div>
    </footer>
    <script src="/public/js/rating-widget.js" defer></script>
</body>
</html>
