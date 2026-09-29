<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\EventosService;
use App\Support\Response;

final class EventosController
{
    public function __construct(private ?EventosService $service)
    {
    }

    public function index(): never
    {
        $events = $this->service?->getUpcomingEvents() ?? [];
        Response::view('eventos', ['events' => $events]);
    }
}
