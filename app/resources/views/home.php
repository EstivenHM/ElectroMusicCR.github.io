<?php

declare(strict_types=1);

$news ??= [];
$events ??= [];
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ElectroMusicCR: comunidad y grupo dedicado a la musica electronica en Costa Rica.">
    <title>ElectroMusicCR | Comunidad de musica electronica</title>
    <link rel="stylesheet" href="/public/css/main.css">
    <script src="/public/js/main.js" defer></script>
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
                <a href="#inicio">Inicio</a>
                <a href="#noticias">Noticias</a>
                <a href="#eventos">Eventos</a>
                <a href="#sobre">Sobre nosotros</a>
                <a href="#contacto">Contacto</a>
                <a class="nav-link--accent" href="/trivia">Trivia</a>
                <a href="/ranking">Ranking</a>
            </nav>
        </div>
    </header>

    <main id="inicio">
        <section id="noticias" class="section section--alt">
            <div class="container section-heading section-heading--split">
                <div>
                    <p class="eyebrow">Noticias</p>
                    <h2>Novedades en el mundo de la musica electronica.</h2>
                </div>

            </div>
            <div class="container card-grid card-grid--two">
                <?php if (empty($news)): ?>
                    <p>No hay novedades publicadas por el momento.</p>
                <?php else: ?>
                    <?php foreach ($news as $item): ?>
                        <?php
                            $newsImage = !empty($item['image_path']) ? $item['image_path'] : '/assets/placeholder-news.svg';
                            if (!preg_match('#^(https?://|data:|/)#i', $newsImage)) {
                                $newsImage = '/' . $newsImage;
                            }
                        ?>
                        <article class="news-card">
                            <img src="<?= htmlspecialchars($newsImage, ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($item['title'] ?? 'Novedad', ENT_QUOTES, 'UTF-8') ?>"
                                 loading="lazy"
                                 onerror="if (this.src.includes('/public/images/')) { this.src = this.src.replace('/public/images/', '/images/'); } else if (this.src.includes('/images/')) { this.src = this.src.replace('/images/', '/public/images/'); } else { this.onerror=null; this.src='/assets/placeholder-news.svg'; }">
                            <div class="news-card__body">
                                <h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p><?= nl2br(htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section id="agenda" class="section">
            <div class="container split-layout">
                <div>
                    <p class="eyebrow">Eventos de la comunidad</p>
                    <h2>En esta seccion encontraras una lista de eventos.</h2>
                    <p>Aqui encontraras eventos que se realizaran en las diferentes partes del pais.<br>
                    Estos eventos son publicados y gestionados por la comunidad de ElectroMusicCR.</p>
                </div>
            </div>
        </section>

        <section id="eventos" class="section section--alt">
            <div class="container section-heading section-heading--split">
                <div>
                    <p class="eyebrow">Proximos eventos</p>
                    <h2>Eventos que se realizaran proximamente.</h2>
                </div>

            </div>
            <div class="container card-grid card-grid--two">
                <?php if (empty($events)): ?>
                    <p>No hay eventos publicados por el momento.</p>
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
                                    <p><strong>Fecha:</strong> <?= htmlspecialchars($event['event_date'], ENT_QUOTES, 'UTF-8') ?><?php if (!empty($event['event_time'])): ?> - <?= htmlspecialchars($event['event_time'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?></p>
                                <?php endif; ?>
                                <?php if (!empty($event['location'])): ?>
                                    <p><strong>Lugar:</strong> <?= htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8') ?></p>
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
        </section>

        <section id="contacto" class="section section--alt">
            <div class="container contact-card">
                <div>
                    <p class="eyebrow">Contacto</p>
                    <h2>Unete a nuestro grupo de WhatsApp.</h2>
                    <p>Enterate de las ultimas noticias, eventos, trivias y debate sobre musica electronica con la comunidad.</p>
                </div>
                <div class="contact-actions">
                    <a class="button button--primary" href="https://chat.whatsapp.com/Jqx3g3nkpjT9aPOtSd9LYP" target="_blank" rel="noopener noreferrer">Unirme</a>
                </div>
            </div>
        </section>

        <section id="sobre" class="section section--alt">
            <div class="container section-heading section-heading--split">
                <div>
                    <p class="eyebrow">Quienes somos</p>
                    <h2>Conoce a la comunidad de ElectroMusicCR</h2>
                </div>

            </div>
            <div class="container card-grid">
                <article class="info-card">
                    <h3>Comunidad activa</h3>
                    <p>Somos un grupo dedicado a impulsar la pasion por la musica electronica en Costa Rica.</p>
                </article>
                <article class="info-card">
                    <h3>Sobre este Sitio</h3>
                    <p>Esta plataforma permite compartir informacion sobre la comunidad, proximos eventos y trivias interactivas.</p>
                </article>
                <article class="info-card">
                    <h3>Objetivo</h3>
                    <p>Conectar a los fanaticos de la musica electronica en Costa Rica con informacion clara, eventos y actividades recreativas.</p>
                </article>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <p>ElectroMusicCR - Sitio oficial para la comunidad. Todos los derechos reservados.</p>
            <p>Beta 1.0</p>
            <a href="#inicio">Volver al inicio</a>
        </div>
    </footer>
</body>
</html>
