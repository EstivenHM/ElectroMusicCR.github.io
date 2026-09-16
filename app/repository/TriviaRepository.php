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

    public function findCurrent(string $dateTime, string $date, ?int $playerId = null): ?array
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
               AND starts_at <= :current_start
               AND (ends_at IS NULL OR ends_at >= :current_end)
             LIMIT 1'
        );
        $statement->execute([
            'status' => 'active',
            'scheduled_date' => $date,
            'current_start' => $dateTime,
            'current_end' => $dateTime,
        ]);
        $trivia = $statement->fetch();

        if ($trivia === false) {
            return null;
        }

        if (is_string($trivia['image_path'] ?? null) && preg_match('/(?:\/|^)([a-f0-9]{32}\.(?:jpg|png|webp))$/i', $trivia['image_path'], $matches)) {
            $filename = strtolower($matches[1]);
            $trivia['image_path'] = '/public/images/trivia/' . $filename;
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

        $trivia['participation'] = [
            'status' => 'not_started',
            'attempts_used' => 0,
            'attempts_remaining' => 2,
            'points' => 0,
        ];
        $trivia['player'] = ['authenticated' => false];
        if ($playerId !== null) {
            $playerStatement = $this->connection->prepare(
                'SELECT nickname
                 FROM trivia_players
                 WHERE id = :player_id AND is_active = 1
                 LIMIT 1'
            );
            $playerStatement->execute(['player_id' => $playerId]);
            $player = $playerStatement->fetch();
            if ($player !== false) {
                $trivia['player'] = [
                    'authenticated' => true,
                    'nickname' => $player['nickname'],
                ];
            }

            $participation = $this->connection->prepare(
                'SELECT COUNT(*) AS attempts_used,
                        COALESCE(SUM(submission.points_total), 0) AS points,
                        COALESCE(MAX(CASE WHEN submission.id IS NOT NULL
                            AND NOT EXISTS (
                                SELECT 1 FROM trivia_attempts failed_attempt
                                WHERE failed_attempt.submission_id = submission.id
                                  AND failed_attempt.is_correct = 0
                            ) THEN 1 ELSE 0 END), 0) AS has_won
                 FROM trivia_submissions submission
                 WHERE submission.trivia_id = :trivia_id
                   AND submission.player_id = :player_id
                   AND submission.status = :status'
            );
            $participation->execute([
                'trivia_id' => $trivia['id'],
                'player_id' => $playerId,
                'status' => 'submitted',
            ]);
            $state = $participation->fetch() ?: [];
            $attemptsUsed = (int) ($state['attempts_used'] ?? 0);
            $hasWon = (int) ($state['has_won'] ?? 0) === 1;
            $trivia['participation'] = [
                'status' => $hasWon ? 'won' : ($attemptsUsed >= 2 ? 'lost' : ($attemptsUsed === 1 ? 'second_attempt_available' : 'not_started')),
                'attempts_used' => $attemptsUsed,
                'attempts_remaining' => max(0, 2 - $attemptsUsed),
                'points' => (int) ($state['points'] ?? 0),
            ];
        }

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
                   AND starts_at <= :submission_start
                   AND (ends_at IS NULL OR ends_at >= :submission_end)
                 FOR UPDATE'
            );
            $triviaStatement->execute([
                'id' => $triviaId,
                'status' => 'active',
                'scheduled_date' => $date,
                'submission_start' => $dateTime,
                'submission_end' => $dateTime,
            ]);
            if ($triviaStatement->fetch() === false) {
                throw new RuntimeException('La trivia ya no está disponible.');
            }

            $countStatement = $this->connection->prepare(
                'SELECT COUNT(*) AS attempts_used,
                        COALESCE(MAX(CASE WHEN submission.id IS NOT NULL
                            AND NOT EXISTS (
                                SELECT 1 FROM trivia_attempts failed_attempt
                                WHERE failed_attempt.submission_id = submission.id
                                  AND failed_attempt.is_correct = 0
                            ) THEN 1 ELSE 0 END), 0) AS has_won
                                 FROM trivia_submissions submission
                                 WHERE submission.trivia_id = :trivia_id
                                     AND submission.player_id = :player_id
                                     AND submission.status = :status'
            );
            $countStatement->execute([
                'trivia_id' => $triviaId,
                'player_id' => $playerId,
                'status' => 'submitted',
            ]);
            $submissionState = $countStatement->fetch() ?: [];
            if ((int) ($submissionState['has_won'] ?? 0) === 1) {
                throw new RuntimeException('Ya acertaste esta trivia.');
            }
            $attemptNumber = (int) ($submissionState['attempts_used'] ?? 0) + 1;
            if ($attemptNumber > 2) {
                throw new RuntimeException('Ya utilizaste los dos intentos disponibles.');
            }

            $questions = $this->connection->prepare(
                'SELECT q.id AS question_id, q.question_text, q.explanation, q.points,
                    o.id AS option_id, o.option_text, o.is_correct
                 FROM trivia_questions q
                 INNER JOIN trivia_options o ON o.question_id = q.id
                 WHERE q.trivia_id = :trivia_id'
            );
            $questions->execute(['trivia_id' => $triviaId]);
            $validOptions = [];
            $questionPoints = [];
            $questionTexts = [];
            $questionExplanations = [];
            $correctOptionLabels = [];
            foreach ($questions->fetchAll() as $row) {
                $validOptions[(int) $row['question_id']][(int) $row['option_id']] = (bool) $row['is_correct'];
                $questionPoints[(int) $row['question_id']] = (int) $row['points'];
                $questionTexts[(int) $row['question_id']] = (string) $row['question_text'];
                $questionExplanations[(int) $row['question_id']] = trim((string) ($row['explanation'] ?? ''));
                if ((bool) $row['is_correct']) {
                    $correctOptionLabels[(int) $row['question_id']] = (string) $row['option_text'];
                }
            }

            if (count($answers) !== count($validOptions)) {
                throw new RuntimeException('Debes responder todas las preguntas.');
            }

            $results = [];
            $feedback = [];
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
                $feedback[] = [
                    'question' => $questionTexts[$questionId],
                    'correct_answer' => $correctOptionLabels[$questionId] ?? '',
                    'explanation' => $questionExplanations[$questionId],
                ];
                if (!in_array(true, $validOptions[$questionId], true)) {
                    throw new RuntimeException('La trivia no tiene una respuesta correcta configurada.');
                }
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
            $isSuccessful = count(array_filter($results, static fn (array $result): bool => !$result[2])) === 0;
            $response = [
                'attempt_number' => $attemptNumber,
                'points' => $pointsTotal,
                'is_correct' => $isSuccessful,
                'status' => $isSuccessful ? 'won' : ($attemptNumber === 2 ? 'lost' : 'second_attempt_available'),
                'attempts_remaining' => $isSuccessful || $attemptNumber === 2 ? 0 : 1,
            ];
            if ($isSuccessful || $attemptNumber === 2) {
                $response['feedback'] = $isSuccessful
                    ? array_map(static fn (array $item): array => [
                        'question' => $item['question'],
                        'explanation' => $item['explanation'],
                    ], $feedback)
                    : $feedback;
            }
            return $response;
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }
}
