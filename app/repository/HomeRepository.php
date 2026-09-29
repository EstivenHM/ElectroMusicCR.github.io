<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class HomeRepository
{
    public function __construct(private PDO $connection)
    {
    }

    public function getPublishedNews(): array
    {
        try {
            $statement = $this->connection->prepare(
                'SELECT id, title, description, image_path, created_at
                 FROM news
                 WHERE status = "published"
                 ORDER BY created_at DESC
                 LIMIT 4'
            );
            $statement->execute();
            $results = $statement->fetchAll();
            foreach ($results as &$item) {
                if (!empty($item['image_path'])) {
                    $item['image_path'] = trim((string) $item['image_path']);
                    if ($item['image_path'] !== '' && !preg_match('#^(https?://|data:|/)#i', $item['image_path'])) {
                        $item['image_path'] = '/' . $item['image_path'];
                    }
                }
            }
            return $results;
        } catch (\Throwable $e) {
            error_log('HomeRepository::getPublishedNews() error: ' . $e->getMessage());
            return [];
        }
    }

    public function getPublishedEvents(): array
    {
        try {
            $statement = $this->connection->prepare(
                'SELECT id, title, description, image_path, event_date, event_time, location, ticket_url
                 FROM events
                 WHERE status = "published"
                   AND (event_date > CURDATE() OR (event_date = CURDATE() AND (event_time IS NULL OR event_time >= CURTIME())))
                 ORDER BY event_date ASC, event_time ASC
                 LIMIT 4'
            );
            $statement->execute();
            $results = $statement->fetchAll();
            foreach ($results as &$event) {
                if (!empty($event['image_path'])) {
                    $event['image_path'] = trim((string) $event['image_path']);
                    if ($event['image_path'] !== '' && !preg_match('#^(https?://|data:|/)#i', $event['image_path'])) {
                        $event['image_path'] = '/' . $event['image_path'];
                    }
                }
            }
            return $results;
        } catch (\Throwable $e) {
            error_log('HomeRepository::getPublishedEvents() error: ' . $e->getMessage());
            return [];
        }
    }

    public function getUpcomingEvents(int $limit = 50): array
    {
        try {
            $limit = max(1, min($limit, 100));
            $statement = $this->connection->prepare(
                'SELECT id, title, description, image_path, event_date, event_time, location, ticket_url
                 FROM events
                 WHERE status = "published"
                   AND (event_date > CURDATE() OR (event_date = CURDATE() AND (event_time IS NULL OR event_time >= CURTIME())))
                 ORDER BY event_date ASC, event_time ASC
                 LIMIT ' . (int) $limit
            );
            $statement->execute();
            $results = $statement->fetchAll();
            foreach ($results as &$event) {
                if (!empty($event['image_path'])) {
                    $event['image_path'] = trim((string) $event['image_path']);
                    if ($event['image_path'] !== '' && !preg_match('#^(https?://|data:|/)#i', $event['image_path'])) {
                        $event['image_path'] = '/' . $event['image_path'];
                    }
                }
            }
            return $results;
        } catch (\Throwable $e) {
            error_log('HomeRepository::getUpcomingEvents() error: ' . $e->getMessage());
            return [];
        }
    }
}
