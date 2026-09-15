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
        $this->ensureSettingsTable();

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
            'trivia' => $this->trivia($warnings),
            'warnings' => $warnings,
        ];
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

    public function createNews(string $title, string $description, ?string $imagePath, int $userId): array
    {
        $statement = $this->connection->prepare(
            'INSERT INTO news (title, description, image_path, status, created_by)
             VALUES (:title, :description, :image_path, "draft", :created_by)'
        );
        $statement->execute([
            'title' => $title,
            'description' => $description,
            'image_path' => $imagePath,
            'created_by' => $userId,
        ]);

        return ['id' => (int) $this->connection->lastInsertId()];
    }

    public function createEvent(array $event, int $userId): array
    {
        $statement = $this->connection->prepare(
            'INSERT INTO events (title, description, image_path, event_date, event_time, location, ticket_url, status, created_by)
             VALUES (:title, :description, :image_path, :event_date, :event_time, :location, :ticket_url, "draft", :created_by)'
        );
        $statement->execute([
            ...$event,
            'created_by' => $userId,
        ]);

        return ['id' => (int) $this->connection->lastInsertId()];
    }

    public function saveTrivia(array $trivia, int $userId): array
    {
        $this->connection->beginTransaction();
        try {
            $triviaId = (int) ($trivia['id'] ?? 0);
            if ($triviaId > 0) {
                $statement = $this->connection->prepare(
                    'UPDATE trivia SET title = :title, scheduled_date = :scheduled_date,
                     starts_at = :starts_at, ends_at = :ends_at, status = :status WHERE id = :id'
                );
                $statement->execute([
                    'id' => $triviaId,
                    'title' => $trivia['title'],
                    'scheduled_date' => $trivia['scheduled_date'],
                    'starts_at' => $trivia['starts_at'],
                    'ends_at' => $trivia['ends_at'] ?: null,
                    'status' => $trivia['status'],
                ]);
                $this->connection->prepare('DELETE FROM trivia_questions WHERE trivia_id = :id')->execute(['id' => $triviaId]);
            } else {
                $statement = $this->connection->prepare(
                    'INSERT INTO trivia (title, scheduled_date, starts_at, ends_at, status, created_by)
                     VALUES (:title, :scheduled_date, :starts_at, :ends_at, :status, :created_by)'
                );
                $statement->execute([
                    'title' => $trivia['title'],
                    'scheduled_date' => $trivia['scheduled_date'],
                    'starts_at' => $trivia['starts_at'],
                    'ends_at' => $trivia['ends_at'] ?: null,
                    'status' => $trivia['status'],
                    'created_by' => $userId,
                ]);
                $triviaId = (int) $this->connection->lastInsertId();
            }

            $questionStatement = $this->connection->prepare(
                'INSERT INTO trivia_questions (trivia_id, question_text, question_date, points, position)
                 VALUES (:trivia_id, :question_text, :question_date, :points, :position)'
            );
            $optionStatement = $this->connection->prepare(
                'INSERT INTO trivia_options (question_id, option_text, is_correct, position)
                 VALUES (:question_id, :option_text, :is_correct, :position)'
            );
            foreach ($trivia['questions'] as $position => $question) {
                $questionStatement->execute([
                    'trivia_id' => $triviaId,
                    'question_text' => $question['text'],
                    'question_date' => $trivia['scheduled_date'],
                    'points' => $question['points'],
                    'position' => $position + 1,
                ]);
                $questionId = (int) $this->connection->lastInsertId();
                foreach ($question['options'] as $optionPosition => $option) {
                    $optionStatement->execute([
                        'question_id' => $questionId,
                        'option_text' => $option['text'],
                        'is_correct' => $option['correct'] ? 1 : 0,
                        'position' => $optionPosition + 1,
                    ]);
                }
            }
            $this->connection->commit();
            return ['id' => $triviaId];
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    private function trivia(array &$warnings): ?array
    {
        try {
            $statement = $this->connection->query(
                'SELECT trivia.id, settings.title, settings.description,
                        settings.image_path, trivia.scheduled_date, trivia.starts_at, trivia.ends_at, trivia.status
                 FROM trivia
                 LEFT JOIN trivia_settings settings ON settings.id = 1
                 ORDER BY trivia.scheduled_date DESC LIMIT 1'
            );
            $trivia = $statement->fetch();
            if (!$trivia) {
                $settings = $this->connection->query(
                    'SELECT 0 AS id, title, description, image_path, NULL AS scheduled_date,
                            NULL AS starts_at, NULL AS ends_at, "draft" AS status
                     FROM trivia_settings WHERE id = 1'
                )->fetch();
                return $settings ?: null;
            }

            $questions = $this->connection->prepare(
                'SELECT id, question_text AS text, points, position FROM trivia_questions
                 WHERE trivia_id = :trivia_id ORDER BY position'
            );
            $questions->execute(['trivia_id' => $trivia['id']]);
            $trivia['questions'] = $questions->fetchAll();
            foreach ($trivia['questions'] as &$question) {
                $options = $this->connection->prepare(
                    'SELECT id, option_text AS text, is_correct AS correct, position FROM trivia_options
                     WHERE question_id = :question_id ORDER BY position'
                );
                $options->execute(['question_id' => $question['id']]);
                $question['options'] = $options->fetchAll();
            }

            return $trivia;
        } catch (\Throwable $exception) {
            $warnings[] = 'No se pudo cargar la trivia. Verifica las migraciones de trivia.';
            return null;
        }
    }
}