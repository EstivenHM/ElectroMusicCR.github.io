<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class AdminRepository
{
    public function __construct(private PDO $connection)
    {
    }

    public function dashboard(): array
    {
        $warnings = [];

        return [
            'news' => $this->queryList(
                'SELECT id, title, description, image_path, status, created_at
                 FROM news ORDER BY created_at DESC LIMIT 50',
                'novedades',
                $warnings
            ),
            'events' => $this->queryList(
                'SELECT id, title, description, image_path, event_date, event_time, location, ticket_url, status
                 FROM events ORDER BY event_date DESC, event_time DESC LIMIT 50',
                'eventos',
                $warnings
            ),
            'triviaSettings' => $this->triviaSettings(),
            'trivias' => $this->trivias($warnings),
            'warnings' => $warnings,
        ];
    }

    public function updateTriviaSettings(string $title, string $description, ?string $imagePath, int $userId): array
    {
        $this->ensureSettingsTable();
        $existing = $this->connection->query(
            'SELECT image_path FROM trivia_settings WHERE id = 1'
        )->fetch();
        $storedImage = $imagePath ?? ($existing['image_path'] ?? null);

        $statement = $this->connection->prepare(
            'INSERT INTO trivia_settings (id, title, description, image_path, updated_by)
             VALUES (1, :title, :description, :image_path, :updated_by)
             ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description),
             image_path = VALUES(image_path), updated_by = VALUES(updated_by)'
        );
        $statement->execute([
            'title' => $title,
            'description' => $description,
            'image_path' => $storedImage,
            'updated_by' => $userId,
        ]);

        return ['updated' => true];
    }

    private function triviaSettings(): array
    {
        $settings = $this->connection->query(
            'SELECT title, description, image_path FROM trivia_settings WHERE id = 1'
        )->fetch();
        if ($settings === false) {
            return ['title' => '', 'description' => '', 'image_path' => null];
        }
        $settings['image_path'] = $this->normalizeImagePath($settings['image_path'] ?? null);
        return $settings;
    }

    private function ensureSettingsTable(): void
    {
        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS trivia_settings (
                id TINYINT UNSIGNED NOT NULL,
                title VARCHAR(160) NOT NULL,
                description TEXT NOT NULL,
                image_path VARCHAR(500) NULL,
                updated_by BIGINT UNSIGNED NOT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                CONSTRAINT fk_trivia_settings_updated_by FOREIGN KEY (updated_by) REFERENCES users (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private function queryList(string $query, string $section, array &$warnings): array
    {
        try {
            return $this->connection->query($query)->fetchAll();
        } catch (\Throwable $exception) {
            $warnings[] = sprintf('No se pudo cargar %s. Verifica la migración administrativa correspondiente.', $section);
            return [];
        }
    }

    public function triviaById(int $triviaId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, title, description, image_path, scheduled_date, starts_at, ends_at, status
             FROM trivia WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $triviaId]);
        $trivia = $statement->fetch();
        if ($trivia === false) {
            return null;
        }
        $trivia['image_path'] = $this->normalizeImagePath($trivia['image_path'] ?? null);

        $questions = $this->connection->prepare(
            'SELECT id, question_text AS text, explanation, points, position
             FROM trivia_questions WHERE trivia_id = :trivia_id ORDER BY position'
        );
        $questions->execute(['trivia_id' => $triviaId]);
        $trivia['questions'] = $questions->fetchAll();
        foreach ($trivia['questions'] as &$question) {
            $options = $this->connection->prepare(
                'SELECT id, option_text AS text, is_correct AS correct, position
                 FROM trivia_options WHERE question_id = :question_id ORDER BY position'
            );
            $options->execute(['question_id' => $question['id']]);
            $question['options'] = $options->fetchAll();
        }
        unset($question);

        return $trivia;
    }

    private function normalizeImagePath(?string $imagePath): ?string
    {
        if (!is_string($imagePath) || $imagePath === '') {
            return null;
        }
        if (preg_match('/(?:\/|^)([a-f0-9]{32}\.(?:jpg|png|webp))$/i', $imagePath, $matches)) {
            return '/public/images/trivia/' . strtolower($matches[1]);
        }
        return $imagePath;
    }

    public function createNews(string $title, string $description, ?string $imagePath, string $status, int $userId): array
    {
        $statement = $this->connection->prepare(
            'INSERT INTO news (title, description, image_path, status, created_by)
             VALUES (:title, :description, :image_path, :status, :created_by)'
        );
        $statement->execute([
            'title' => $title,
            'description' => $description,
            'image_path' => $imagePath,
            'status' => $status,
            'created_by' => $userId,
        ]);

        return ['id' => (int) $this->connection->lastInsertId()];
    }

    public function updateNews(int $id, string $title, string $description, ?string $imagePath, string $status, int $userId): array
    {
        $existing = $this->connection->prepare('SELECT image_path FROM news WHERE id = :id');
        $existing->execute(['id' => $id]);
        $current = $existing->fetch();
        if ($current === false) {
            throw new \RuntimeException('La novedad no existe.');
        }
        $finalImage = $imagePath ?? ($current['image_path'] ?? null);
        $statement = $this->connection->prepare(
            'UPDATE news SET title = :title, description = :description, image_path = :image_path, status = :status, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'image_path' => $finalImage,
            'status' => $status,
        ]);

        return ['id' => $id];
    }

    public function deleteNews(int $id): void
    {
        $statement = $this->connection->prepare('DELETE FROM news WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function createEvent(array $event, int $userId): array
    {
        $statement = $this->connection->prepare(
            'INSERT INTO events (title, description, image_path, event_date, event_time, location, ticket_url, status, created_by)
             VALUES (:title, :description, :image_path, :event_date, :event_time, :location, :ticket_url, :status, :created_by)'
        );
        $statement->execute([
            'title' => $event['title'],
            'description' => $event['description'],
            'image_path' => $event['image_path'] ?? null,
            'event_date' => $event['event_date'],
            'event_time' => $event['event_time'] ?: null,
            'location' => $event['location'],
            'ticket_url' => $event['ticket_url'] ?: null,
            'status' => $event['status'] ?? 'draft',
            'created_by' => $userId,
        ]);

        return ['id' => (int) $this->connection->lastInsertId()];
    }

    public function updateEvent(int $id, array $event, ?string $imagePath): array
    {
        $existing = $this->connection->prepare('SELECT image_path FROM events WHERE id = :id');
        $existing->execute(['id' => $id]);
        $current = $existing->fetch();
        if ($current === false) {
            throw new \RuntimeException('El evento no existe.');
        }
        $finalImage = $imagePath ?? ($current['image_path'] ?? null);
        $statement = $this->connection->prepare(
            'UPDATE events SET title = :title, description = :description, image_path = :image_path,
             event_date = :event_date, event_time = :event_time, location = :location,
             ticket_url = :ticket_url, status = :status, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'title' => $event['title'],
            'description' => $event['description'],
            'image_path' => $finalImage,
            'event_date' => $event['event_date'],
            'event_time' => $event['event_time'] ?: null,
            'location' => $event['location'],
            'ticket_url' => $event['ticket_url'] ?: null,
            'status' => $event['status'],
        ]);

        return ['id' => $id];
    }

    public function deleteEvent(int $id): void
    {
        $statement = $this->connection->prepare('DELETE FROM events WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    private function trivias(array &$warnings): array
    {
        try {
            $trivias = $this->connection->query(
                'SELECT trivia.id, trivia.title, trivia.description, trivia.image_path,
                    trivia.scheduled_date, trivia.starts_at,
                        trivia.ends_at, trivia.status,
                        COUNT(DISTINCT questions.id) AS question_count,
                        COUNT(DISTINCT submissions.id) AS submission_count
                 FROM trivia
                 LEFT JOIN trivia_questions questions ON questions.trivia_id = trivia.id
                 LEFT JOIN trivia_submissions submissions ON submissions.trivia_id = trivia.id
                 GROUP BY trivia.id
                 ORDER BY trivia.scheduled_date DESC, trivia.starts_at DESC, trivia.id DESC'
            )->fetchAll();
            $now = new \DateTimeImmutable('now', new \DateTimeZone('America/Costa_Rica'));
            foreach ($trivias as &$trivia) {
                $trivia['question_count'] = (int) $trivia['question_count'];
                $trivia['submission_count'] = (int) $trivia['submission_count'];
                $startsAt = new \DateTimeImmutable((string) $trivia['starts_at'], new \DateTimeZone('America/Costa_Rica'));
                $isFuture = $startsAt > $now;
                $trivia['is_future'] = $isFuture;
                $trivia['can_edit'] = $isFuture && $trivia['submission_count'] === 0;
            }
            unset($trivia);
            return $trivias;
        } catch (\Throwable $exception) {
            $warnings[] = 'No se pudo cargar el listado de trivias. Verifica las migraciones de trivia.';
            return [];
        }
    }
}