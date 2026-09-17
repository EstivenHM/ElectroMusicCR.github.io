<?php

declare(strict_types=1);

namespace App\Services;

use App\Repository\HomeRepository;

final class HomeService
{
    public function __construct(private HomeRepository $repository)
    {
    }

    public function getHomeData(): array
    {
        return [
            'news' => $this->repository->getPublishedNews(),
            'events' => $this->repository->getPublishedEvents(),
        ];
    }
}
