<?php

declare(strict_types=1);
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Mantenimiento | ElectroMusicCR</title>
    <link rel="stylesheet" href="/public/css/main.css">
    <style>
        .maintenance-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: radial-gradient(circle at 50% 20%, rgba(139, 92, 246, 0.15), rgba(15, 23, 42, 0.95) 75%), #0b0f19;
            color: #f8fafc;
            text-align: center;
            font-family: inherit;
        }
        .maintenance-card {
            max-width: 580px;
            width: 100%;
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.25rem;
            padding: 3rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 30px rgba(139, 92, 246, 0.2);
            animation: fadeIn 0.6s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .maintenance-logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin: 0 auto 1.5rem;
            filter: drop-shadow(0 0 15px rgba(139, 92, 246, 0.5));
        }
        .maintenance-badge {
            display: inline-block;
            background: rgba(139, 92, 246, 0.2);
            color: #c4b5fd;
            border: 1px solid rgba(139, 92, 246, 0.4);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.25rem;
        }
        .maintenance-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }
        .maintenance-text {
            color: #94a3b8;
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .maintenance-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: center;
        }
        @media (min-width: 480px) {
            .maintenance-actions {
                flex-direction: row;
                justify-content: center;
            }
        }
        .pulse-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            margin-right: 6px;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }
        .maintenance-footer {
            margin-top: 2rem;
            font-size: 0.85rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="maintenance-wrap">
        <div class="maintenance-card">
            <img class="maintenance-logo" src="/assets/EMCR.png" alt="ElectroMusicCR" onerror="this.style.display='none'">
            <div>
                <span class="maintenance-badge">
                    <span class="pulse-dot"></span> Mejorando la plataforma
                </span>
            </div>
            <h1 class="maintenance-title">Estamos aplicando mejoras</h1>
            <p class="maintenance-text">
                En este momento nos encontramos actualizando el sistema para brindarte una mejor experiencia. Volveremos a estar disponibles en unos minutos.
            </p>
            <div class="maintenance-actions">
                <a class="button button--primary" href="https://chat.whatsapp.com/Jqx3g3nkpjT9aPOtSd9LYP" target="_blank" rel="noopener noreferrer">
                    Unirme al WhatsApp oficial
                </a>
                <button class="button button--ghost" type="button" onclick="window.location.reload()">
                    Reintentar cargar
                </button>
            </div>
            <p class="maintenance-footer">
                &copy; <?= date('Y') ?> ElectroMusicCR &middot; Comunidad de música electrónica
            </p>
        </div>
    </div>
</body>
</html>
