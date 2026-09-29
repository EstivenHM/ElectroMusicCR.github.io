<?php

declare(strict_types=1);

namespace App\Controller;

use App\Support\Response;
use Config\DatabaseManager;

/**
 * RatingController
 * Endpoint POST /api/rating — guarda calificaciones de la página.
 * No requiere autenticación; protegido contra spam por ip_hash.
 */
final class RatingController
{
    public function store(): never
    {
        // Solo POST
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            Response::json(['error' => 'Método no permitido.'], 405);
        }

        // Leer JSON body
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw ?: '{}', true);

        if (!is_array($data)) {
            Response::json(['error' => 'Petición inválida.'], 400);
        }

        $rating  = isset($data['rating'])  ? (int) $data['rating']  : 0;
        $comment = isset($data['comment']) ? trim((string) $data['comment']) : '';
        $pageUrl = isset($data['page_url']) ? trim((string) $data['page_url']) : '/';

        // Validar rating
        if ($rating < 1 || $rating > 5) {
            Response::json(['error' => 'La calificación debe estar entre 1 y 5.'], 422);
        }

        // Sanear valores
        $comment = mb_substr($comment, 0, 400);
        $pageUrl = mb_substr($pageUrl, 0, 255);

        // Hash de IP para privacidad (no se guarda la IP real)
        $ipHash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . date('Y-m-d'));

        try {
            $pdo = DatabaseManager::connection();

            // Crear tabla si no existe (solo la primera vez)
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `page_ratings` (
                    `id`         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `rating`     TINYINT(1) NOT NULL,
                    `comment`    TEXT DEFAULT NULL,
                    `page_url`   VARCHAR(255) NOT NULL DEFAULT '/',
                    `ip_hash`    VARCHAR(64) DEFAULT NULL,
                    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    INDEX `idx_rating_date` (`rating`, `created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $stmt = $pdo->prepare(
                'INSERT INTO `page_ratings` (`rating`, `comment`, `page_url`, `ip_hash`)
                 VALUES (:rating, :comment, :page_url, :ip_hash)'
            );

            $stmt->execute([
                ':rating'   => $rating,
                ':comment'  => $comment !== '' ? $comment : null,
                ':page_url' => $pageUrl,
                ':ip_hash'  => $ipHash,
            ]);

            Response::json(['success' => true]);

        } catch (\Throwable $e) {
            error_log('[RatingController] ' . $e->getMessage());
            Response::json(['error' => 'No se pudo guardar la calificación.'], 500);
        }
    }
}
