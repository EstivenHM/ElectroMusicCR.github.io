<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\HomeService;
use App\Support\Response;

final class HomeController
{
    public function __construct(private ?HomeService $service)
    {
    }

    public function index(): never
    {
        $data = $this->service?->getHomeData() ?? ['news' => [], 'events' => []];
        Response::view('home', $data);
    }
}
