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

        try {
            Response::json(['data' => $this->service->current(SessionManager::playerId())]);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Response::json(['error' => 'No fue posible cargar la trivia.'], 503);
        }
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
            error_log($exception->getMessage());
            Response::json(['error' => 'No fue posible validar la identidad.'], 422);
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
        if (!is_array($payload) || !is_array($payload['answers'] ?? null)) {
            Response::json(['error' => 'Datos no válidos.'], 422);
        }
        $answers = $payload['answers'];

        try {
            Response::json(['data' => $service->submit(
                (int) ($payload['trivia_id'] ?? 0),
                $playerId,
                $answers
            )]);
        } catch (Throwable $exception) {
            error_log('Error al registrar submission: ' . $exception->getMessage());
            $message = $exception->getMessage();
            $status = str_contains($message, 'intentos') || str_contains($message, 'acertaste') ? 409 : 422;
            Response::json(['error' => $status === 409 ? $message : 'No fue posible registrar las respuestas.'], $status);
        }
    }
}
