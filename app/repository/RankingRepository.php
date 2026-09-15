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
}
