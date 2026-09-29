<?php

declare(strict_types=1);

namespace App\Services;

use App\Repository\TriviaRepository;
use DateTimeImmutable;
use DateTimeZone;

final class TriviaService
{
    public function __construct(private TriviaRepository $repository)
    {
    }

    public function current(?int $playerId = null): ?array
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('America/Costa_Rica'));
        return $this->repository->findCurrent($now->format('Y-m-d H:i:s'), $now->format('Y-m-d'), $playerId);
    }

    public function submit(int $triviaId, int $playerId, array $answers): array
    {
        if ($triviaId < 1 || count($answers) < 1 || count($answers) > 100) {
            throw new \RuntimeException('Datos de trivia no válidos.');
        }

        $now = new DateTimeImmutable('now', new DateTimeZone('America/Costa_Rica'));
        return $this->repository->submit(
            $triviaId,
            $playerId,
            $answers,
            $now->format('Y-m-d H:i:s'),
            $now->format('Y-m-d')
        );
    }

    public function save(array $trivia, int $userId, ?string $imagePath = null): array
    {
        $start = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $trivia['starts_at'], new DateTimeZone('America/Costa_Rica'));
        if (!$start || $start->format('Y-m-d H:i:s') !== $trivia['starts_at']) {
            throw new \RuntimeException('La fecha de inicio no es válida.');
        }
        $trivia['scheduled_date'] = $start->format('Y-m-d');

        return $this->repository->saveAdmin($trivia, $userId, $imagePath);
    }
}
