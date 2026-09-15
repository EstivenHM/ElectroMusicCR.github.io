<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class TriviaPlayerRepository
{
    public function __construct(private PDO $connection)
    {
    }

    public function findByNickname(string $nickname): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, nickname, recovery_code_hash, is_active
             FROM trivia_players
             WHERE nickname_normalized = :nickname_normalized
             LIMIT 1'
        );
        $statement->execute(['nickname_normalized' => $nickname]);
        $player = $statement->fetch();

        return $player === false ? null : $player;
    }

    public function create(string $nickname, string $normalizedNickname, string $codeHash): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO trivia_players (nickname, nickname_normalized, recovery_code_hash)
             VALUES (:nickname, :nickname_normalized, :recovery_code_hash)'
        );
        $statement->execute([
            'nickname' => $nickname,
            'nickname_normalized' => $normalizedNickname,
            'recovery_code_hash' => $codeHash,
        ]);

        return (int) $this->connection->lastInsertId();
    }
}
