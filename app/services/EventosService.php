<?php

declare(strict_types=1);

namespace App\Services;

use App\Repository\HomeRepository;

final class EventosService
{
    public function __construct(private HomeRepository $repository)
    {
    }

    public function getUpcomingEvents(): array
    {
        return $this->repository->getUpcomingEvents(50);
    }
}
