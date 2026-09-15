<?php

declare(strict_types=1);

namespace App\Services;

use App\Repository\RankingRepository;

final class RankingService
{
    public function __construct(private RankingRepository $repository)
    {
    }

    public function paginate(int $requestedPage, int $requestedLimit): array
    {
        $page = max(1, $requestedPage);
        $limit = min(50, max(1, $requestedLimit));
        $offset = ($page - 1) * $limit;

        return [
            'items' => $this->repository->paginate($limit, $offset),
            'page' => $page,
            'limit' => $limit,
        ];
    }
}
