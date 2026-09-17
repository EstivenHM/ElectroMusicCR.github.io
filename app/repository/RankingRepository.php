<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class RankingRepository
{
    public function __construct(private PDO $connection)
    {
    }

    public function paginate(int $limit, int $offset): array
    {
        $statement = $this->connection->prepare(
            'SELECT position, nickname, points
             FROM trivia_ranking
             ORDER BY position ASC
             LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function getSettings(): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT title, description, image_path FROM trivia_settings WHERE id = 1 LIMIT 1'
        );
        $statement->execute();
        $settings = $statement->fetch();
        if ($settings === false) {
            return null;
        }
        if (is_string($settings['image_path'] ?? null) && preg_match('/(?:\/|^)([a-f0-9]{32}\.(?:jpg|png|webp))$/i', $settings['image_path'], $matches)) {
            $filename = strtolower($matches[1]);
            $settings['image_path'] = '/public/images/trivia/' . $filename;
        }
        return $settings;
    }
}
