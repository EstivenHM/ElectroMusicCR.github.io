<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\RankingService;
use App\Support\Response;

final class RankingController
{
    public function __construct(private ?RankingService $service)
    {
    }

    public function page(): never
    {
        Response::view('ranking');
    }

    public function index(): never
    {
        if ($this->service === null) {
            Response::json(['error' => 'El ranking no está disponible.'], 503);
        }

        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 20;
        Response::json(['data' => $this->service->paginate($page, $limit)]);
    }
}
