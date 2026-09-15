<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;
use RuntimeException;
use Throwable;

final class TriviaRepository
{
    public function __construct(private PDO $connection)
    {
    }

    public function findCurrent(string $dateTime, string $date): ?array
    {
        $this->ensureSettingsTable();
        $statement = $this->connection->prepare(
            'SELECT trivia.id, COALESCE(settings.title, trivia.title) AS title,
                    COALESCE(settings.description, "") AS description, settings.image_path,
                    trivia.starts_at, trivia.ends_at
             FROM trivia
             LEFT JOIN trivia_settings settings ON settings.id = 1
             WHERE status = :status
               AND scheduled_date = :scheduled_date
               AND starts_at <= :date_time
               AND (ends_at IS NULL OR ends_at >= :date_time)
             LIMIT 1'
        );
        $statement->execute([
            'status' => 'active',
            'scheduled_date' => $date,
            'date_time' => $dateTime,
        ]);
        $trivia = $statement->fetch();

        if ($trivia === false) {
            return null;
        }

        $questions = $this->connection->prepare(
            'SELECT id, question_text, points, position
             FROM trivia_questions
             WHERE trivia_id = :trivia_id
             ORDER BY position ASC'
        );
        $questions->execute(['trivia_id' => $trivia['id']]);
        $trivia['questions'] = $questions->fetchAll();

        $options = $this->connection->prepare(
            'SELECT id, question_id, option_text, position
             FROM trivia_options
             WHERE question_id IN (
                 SELECT id FROM trivia_questions WHERE trivia_id = :trivia_id
             )
             ORDER BY question_id ASC, position ASC'
        );
        $options->execute(['trivia_id' => $trivia['id']]);

        $optionsByQuestion = [];
        foreach ($options->fetchAll() as $option) {
            $optionsByQuestion[$option['question_id']][] = $option;
        }
        foreach ($trivia['questions'] as &$question) {
            $question['options'] = $optionsByQuestion[$question['id']] ?? [];
        }
        unset($question);

        return $trivia;
    }

    public function saveAdmin(array $trivia, int $userId, ?string $imagePath): array
    {
        $this->ensureSettingsTable();
        $this->connection->beginTransaction();
        try {
            $settings = $this->connection->query(
                'SELECT title, description, image_path FROM trivia_settings WHERE id = 1'
            )->fetch() ?: [];
            $title = trim((string) ($settings['title'] ?? $trivia['title'] ?? ''));
            $description = (string) ($settings['description'] ?? $trivia['description'] ?? '');
            $storedImage = $imagePath ?? ($settings['image_path'] ?? null);

            $settingsStatement = $this->connection->prepare(
                'INSERT INTO trivia_settings (id, title, description, image_path, updated_by)
                 VALUES (1, :title, :description, :image_path, :updated_by)
                 ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description),
                 image_path = VALUES(image_path), updated_by = VALUES(updated_by)'
            );
            $settingsStatement->execute([
                'title' => $title,
                'description' => $description,
                'image_path' => $storedImage,
                'updated_by' => $userId,
            ]);

            $triviaId = (int) ($trivia['id'] ?? 0);
            if ($triviaId > 0) {
                $statement = $this->connection->prepare(
                    'UPDATE trivia SET title = :title, scheduled_date = :scheduled_date,
                     starts_at = :starts_at, ends_at = :ends_at, status = :status WHERE id = :id'
                );
                $statement->execute([
                    'id' => $triviaId,
                    'title' => $title,
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
                    'title' => $title,
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
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
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

    public function submit(int $triviaId, int $playerId, array $answers, string $dateTime, string $date): array
    {
        $this->connection->beginTransaction();

        try {
            $triviaStatement = $this->connection->prepare(
                'SELECT id
                 FROM trivia
                 WHERE id = :id
                   AND status = :status
                   AND scheduled_date = :scheduled_date
                   AND starts_at <= :date_time
                   AND (ends_at IS NULL OR ends_at >= :date_time)
                 FOR UPDATE'
            );
            $triviaStatement->execute([
                'id' => $triviaId,
                'status' => 'active',
                'scheduled_date' => $date,
                'date_time' => $dateTime,
            ]);
            if ($triviaStatement->fetch() === false) {
                throw new RuntimeException('La trivia ya no está disponible.');
            }

            $countStatement = $this->connection->prepare(
                'SELECT COUNT(*) FROM trivia_submissions
                 WHERE trivia_id = :trivia_id AND player_id = :player_id AND status = :status
                 FOR UPDATE'
            );
            $countStatement->execute([
                'trivia_id' => $triviaId,
                'player_id' => $playerId,
                'status' => 'submitted',
            ]);
            $attemptNumber = (int) $countStatement->fetchColumn() + 1;
            if ($attemptNumber > 2) {
                throw new RuntimeException('Ya utilizaste los dos intentos disponibles.');
            }

            $questions = $this->connection->prepare(
                'SELECT q.id AS question_id, q.points, o.id AS option_id, o.is_correct
                 FROM trivia_questions q
                 INNER JOIN trivia_options o ON o.question_id = q.id
                 WHERE q.trivia_id = :trivia_id'
            );
            $questions->execute(['trivia_id' => $triviaId]);
            $validOptions = [];
            $questionPoints = [];
            foreach ($questions->fetchAll() as $row) {
                $validOptions[(int) $row['question_id']][(int) $row['option_id']] = (bool) $row['is_correct'];
                $questionPoints[(int) $row['question_id']] = (int) $row['points'];
            }

            if (count($answers) !== count($validOptions)) {
                throw new RuntimeException('Debes responder todas las preguntas.');
            }

            $results = [];
            $pointsTotal = 0;
            foreach ($answers as $questionId => $optionId) {
                $questionId = (int) $questionId;
                $optionId = (int) $optionId;
                if (!isset($validOptions[$questionId][$optionId])) {
                    throw new RuntimeException('Una respuesta no pertenece a la trivia.');
                }
                $isCorrect = $validOptions[$questionId][$optionId];
                $points = $isCorrect ? $questionPoints[$questionId] : 0;
                $pointsTotal += $points;
                $results[] = [$questionId, $optionId, $isCorrect, $points];
            }

            $submission = $this->connection->prepare(
                'INSERT INTO trivia_submissions (trivia_id, player_id, attempt_number, points_total)
                 VALUES (:trivia_id, :player_id, :attempt_number, :points_total)'
            );
            $submission->execute([
                'trivia_id' => $triviaId,
                'player_id' => $playerId,
                'attempt_number' => $attemptNumber,
                'points_total' => $pointsTotal,
            ]);
            $submissionId = (int) $this->connection->lastInsertId();

            $attempt = $this->connection->prepare(
                'INSERT INTO trivia_attempts
                    (submission_id, question_id, selected_option_id, is_correct, points_awarded)
                 VALUES (:submission_id, :question_id, :selected_option_id, :is_correct, :points_awarded)'
            );
            foreach ($results as [$questionId, $optionId, $isCorrect, $points]) {
                $attempt->execute([
                    'submission_id' => $submissionId,
                    'question_id' => $questionId,
                    'selected_option_id' => $optionId,
                    'is_correct' => $isCorrect ? 1 : 0,
                    'points_awarded' => $points,
                ]);
            }

            $this->connection->commit();
            return ['attempt_number' => $attemptNumber, 'points' => $pointsTotal];
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }
}
