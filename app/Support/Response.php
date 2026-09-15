<?php

declare(strict_types=1);

namespace App\Support;

final class Response
{
    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function view(string $view, array $data = []): never
    {
        $viewFile = dirname(__DIR__) . '/resources/views/' . $view . '.php';
        if (!is_file($viewFile)) {
            self::json(['error' => 'Vista no encontrada.'], 500);
        }

        extract($data, EXTR_SKIP);
        require $viewFile;
        exit;
    }

    public static function adminView(string $view, array $data = []): never
    {
        $viewFile = dirname(__DIR__) . '/resources/adminviews/' . $view . '.php';
        if (!is_file($viewFile)) {
            self::json(['error' => 'Vista administrativa no encontrada.'], 500);
        }

        extract($data, EXTR_SKIP);
        require $viewFile;
        exit;
    }
}
