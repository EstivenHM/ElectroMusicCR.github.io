<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\TriviaService;
use App\Support\Response;
use App\Support\SessionManager;
use App\Services\TriviaPlayerService;
use Throwable;

final class TriviaController
{
    public function __construct(
        private ?TriviaService $service,
        private ?TriviaPlayerService $playerService = null
    )
    {
    }

    public function page(): never
    {
        Response::view('trivia');
    }

    public function current(): never
    {
        if ($this->service === null) {
            Response::json(['error' => 'La trivia no está disponible.'], 503);
        }

        Response::json(['data' => $this->service->current()]);
    }

    public function csrf(): never
    {
        Response::json(['csrf_token' => SessionManager::csrfToken()]);
    }

    public function player(): never
    {
        SessionManager::validateCsrf();
        if ($this->playerService === null) {
            Response::json(['error' => 'El servicio no está disponible.'], 503);
        }

        $payload = json_decode(file_get_contents('php://input') ?: '{}', true);
        if (!is_array($payload)) {
            Response::json(['error' => 'Datos no válidos.'], 422);
        }

        try {
            Response::json(['data' => $this->playerService->authenticate(
                trim((string) ($payload['nickname'] ?? '')),
                (string) ($payload['recovery_code'] ?? '')
            )]);
        } catch (Throwable $exception) {
            Response::json(['error' => $exception->getMessage()], 422);
        }
    }

    public function submit(): never
    {
        SessionManager::validateCsrf();
        $service = $this->service;
        $playerId = SessionManager::playerId();
        if ($service === null || $playerId === null) {
            Response::json(['error' => 'Debes identificarte antes de participar.'], 401);
        }

        $payload = json_decode(file_get_contents('php://input') ?: '{}', true);
        $answers = $payload['answers'] ?? null;
        if (!is_array($payload) || !is_array($answers)) {
            Response::json(['error' => 'Datos no válidos.'], 422);
        }

        try {
            Response::json(['data' => $service->submit(
                (int) ($payload['trivia_id'] ?? 0),
                $playerId,
                $answers
            )]);
        } catch (Throwable $exception) {
            Response::json(['error' => $exception->getMessage()], 422);
        }
    }
}
