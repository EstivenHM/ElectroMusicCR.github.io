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
        $rows = [];
        try {
            $statement = $this->connection->prepare(
                'SELECT player.id, player.nickname,
                        COALESCE(SUM(CASE WHEN submission.status = \'submitted\' THEN submission.points_total ELSE 0 END), 0) AS points
                 FROM trivia_players player
                 LEFT JOIN trivia_submissions submission ON submission.player_id = player.id
                 WHERE player.is_active = 1
                 GROUP BY player.id, player.nickname
                 ORDER BY points DESC, player.nickname ASC
                 LIMIT :limit OFFSET :offset'
            );
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
            $statement->execute();
            $rawRows = $statement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rawRows as $index => $row) {
                $rows[] = [
                    'position' => $offset + $index + 1,
                    'nickname' => (string) ($row['nickname'] ?? ''),
                    'points' => (int) ($row['points'] ?? 0),
                ];
            }
            return $rows;
        } catch (\Throwable $exception) {
            error_log('Direct ranking query error: ' . $exception->getMessage());
        }

        try {
            $statement = $this->connection->prepare(
                'SELECT position, nickname, points
                 FROM trivia_ranking
                 ORDER BY position ASC
                 LIMIT :limit OFFSET :offset'
            );
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
            $statement->execute();
            $rawRows = $statement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rawRows as $index => $row) {
                $rows[] = [
                    'position' => (int) ($row['position'] ?? ($offset + $index + 1)),
                    'nickname' => (string) ($row['nickname'] ?? ''),
                    'points' => (int) ($row['points'] ?? 0),
                ];
            }
            return $rows;
        } catch (\Throwable $exception) {
            error_log('View ranking query error: ' . $exception->getMessage());
            return [];
        }
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
        if (is_string($settings['image_path'] ?? null) && trim($settings['image_path']) !== '') {
            $imagePath = trim($settings['image_path']);
            if (preg_match('/(?:\/|^)([^\/]+\.(?:jpg|jpeg|png|webp|gif|svg))$/i', $imagePath, $matches)) {
                $settings['image_path'] = '/public/images/trivia/' . strtolower($matches[1]);
            }
        }
        return $settings;
    }
}
