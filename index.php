<?php

declare(strict_types=1);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (str_starts_with(rtrim($requestPath, '/'), '/admin') || str_starts_with(rtrim($requestPath, '/'), '/api/')) {
    require __DIR__ . '/public/index.php';
    exit;
}

require __DIR__ . '/public/index.php';
