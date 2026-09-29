<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\AdminRepository;
use App\Services\AuthService;
use App\Services\TriviaService;
use App\Support\Response;
use App\Support\SessionManager;
use Throwable;

final class AdminController
{
    public function __construct(private AdminRepository $repository, private AuthService $authService, private TriviaService $triviaService)
    {
    }

    public function page(): never
    {
        $user = SessionManager::requireAdminPage();
        Response::adminView('settings', [
            'user' => $user,
            'csrfToken' => SessionManager::csrfToken(),
        ]);
    }

    public function dashboard(): never
    {
        $user = SessionManager::requireAdmin();
        try {
            Response::json(['data' => [...$this->repository->dashboard(), 'user' => $user]]);
        } catch (Throwable $exception) {
            error_log('Error al cargar el dashboard administrativo: ' . $exception->getMessage());
            Response::json(['error' => 'No fue posible cargar el panel. Verifica que las migraciones administrativas estén aplicadas.'], 500);
        }
    }

    public function createNews(): never
    {
        $user = SessionManager::requireAdmin();
        SessionManager::validateCsrf();
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true) ? $_POST['status'] : 'draft';
        if ($title === '' || $description === '') {
            Response::json(['error' => 'El título y la descripción son obligatorios.'], 422);
        }
        Response::json(['data' => $this->repository->createNews($title, $description, $this->upload('image'), $status, (int) $user['id'])], 201);
    }

    public function updateNews(): never
    {
        $user = SessionManager::requireAdmin();
        SessionManager::validateCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true) ? $_POST['status'] : 'draft';
        if ($id < 1 || $title === '' || $description === '') {
            Response::json(['error' => 'ID, título y descripción son obligatorios.'], 422);
        }
        try {
            Response::json(['data' => $this->repository->updateNews($id, $title, $description, $this->upload('image'), $status, (int) $user['id'])]);
        } catch (\RuntimeException $exception) {
            Response::json(['error' => $exception->getMessage()], 404);
        }
    }

    public function deleteNews(): never
    {
        $user = SessionManager::requireAdmin();
        SessionManager::validateCsrf();
        $payload = json_decode(file_get_contents('php://input') ?: '{}', true);
        $id = (int) ($payload['id'] ?? 0);
        if ($id < 1) {
            Response::json(['error' => 'ID inválido.'], 422);
        }
        $this->repository->deleteNews($id);
        Response::json(['data' => ['deleted' => true]]);
    }

    public function createEvent(): never
    {
        $user = SessionManager::requireAdmin();
        SessionManager::validateCsrf();
        $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'cancelled'], true) ? $_POST['status'] : 'draft';
        $event = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'image_path' => $this->upload('image'),
            'event_date' => (string) ($_POST['event_date'] ?? ''),
            'event_time' => (string) ($_POST['event_time'] ?? ''),
            'location' => trim((string) ($_POST['location'] ?? '')),
            'ticket_url' => trim((string) ($_POST['ticket_url'] ?? '')) ?: null,
            'status' => $status,
        ];
        if ($event['title'] === '' || $event['description'] === '' || $event['event_date'] === '' || $event['location'] === '') {
            Response::json(['error' => 'Completa los campos obligatorios del evento.'], 422);
        }
        Response::json(['data' => $this->repository->createEvent($event, (int) $user['id'])], 201);
    }

    public function updateEvent(): never
    {
        $user = SessionManager::requireAdmin();
        SessionManager::validateCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        $event = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'event_date' => (string) ($_POST['event_date'] ?? ''),
            'event_time' => (string) ($_POST['event_time'] ?? ''),
            'location' => trim((string) ($_POST['location'] ?? '')),
            'ticket_url' => trim((string) ($_POST['ticket_url'] ?? '')) ?: null,
            'status' => in_array($_POST['status'] ?? '', ['draft', 'published', 'cancelled'], true) ? $_POST['status'] : 'draft',
        ];
        if ($id < 1 || $event['title'] === '' || $event['description'] === '' || $event['event_date'] === '' || $event['location'] === '') {
            Response::json(['error' => 'Completa los campos obligatorios del evento.'], 422);
        }
        try {
            Response::json(['data' => $this->repository->updateEvent($id, $event, $this->upload('image'))]);
        } catch (\RuntimeException $exception) {
            Response::json(['error' => $exception->getMessage()], 404);
        }
    }

    public function deleteEvent(): never
    {
        $user = SessionManager::requireAdmin();
        SessionManager::validateCsrf();
        $payload = json_decode(file_get_contents('php://input') ?: '{}', true);
        $id = (int) ($payload['id'] ?? 0);
        if ($id < 1) {
            Response::json(['error' => 'ID inválido.'], 422);
        }
        $this->repository->deleteEvent($id);
        Response::json(['data' => ['deleted' => true]]);
    }

    public function saveTrivia(): never
    {
        $user = SessionManager::requireAdmin();
        SessionManager::validateCsrf();
        $payload = $_POST;
        $payload['questions'] = json_decode((string) ($payload['questions'] ?? '[]'), true);
        try {
            $validated = $this->validateTrivia($payload);
            $result = $this->triviaService->save($validated, (int) $user['id'], $this->upload('image'));
            Response::json(['data' => $result]);
        } catch (Throwable $exception) {
            error_log('Trivia save error: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
            Response::json(['error' => $exception->getMessage()], 422);
        }
    }

    public function trivia(int $triviaId): never
    {
        SessionManager::requireAdmin();
        if ($triviaId < 1) {
            Response::json(['error' => 'ID de trivia inválido.'], 422);
        }
        try {
            $trivia = $this->repository->triviaById($triviaId);
            if ($trivia === null) {
                Response::json(['error' => 'La trivia no existe.'], 404);
            }
            $now = new \DateTimeImmutable('now', new \DateTimeZone('America/Costa_Rica'));
            $start = new \DateTimeImmutable((string) $trivia['starts_at'], new \DateTimeZone('America/Costa_Rica'));
            if ($start <= $now) {
                Response::json(['error' => 'Solo se pueden editar trivias futuras que aún no están en período ni pasadas.'], 403);
            }
            Response::json(['data' => $trivia]);
        } catch (Throwable $exception) {
            Response::json(['error' => $exception->getMessage() ?: 'No se pudo cargar la trivia.'], 500);
        }
    }

    public function updateTriviaMetadata(): never
    {
        $user = SessionManager::requireAdmin();
        SessionManager::validateCsrf();
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        if ($title === '') {
            Response::json(['error' => 'El título de la trivia es obligatorio.'], 422);
        }
        try {
            Response::json(['data' => $this->repository->updateTriviaSettings(
                $title,
                $description,
                $this->upload('image'),
                (int) $user['id']
            )]);
        } catch (Throwable $exception) {
            Response::json(['error' => $exception->getMessage()], 422);
        }
    }

    public function updateProfile(): never
    {
        $user = SessionManager::requireAdmin();
        SessionManager::validateCsrf();
        $payload = json_decode(file_get_contents('php://input') ?: '{}', true);
        try {
            $updated = $this->authService->updateProfile(
                (int) $user['id'],
                (string) $user['username'],
                trim((string) ($payload['username'] ?? $user['username'])),
                (string) ($payload['current_password'] ?? ''),
                (string) ($payload['new_password'] ?? '')
            );
            SessionManager::authenticateAdmin($updated);
            Response::json(['data' => ['username' => $updated['username']]]);
        } catch (Throwable $exception) {
            Response::json(['error' => $exception->getMessage()], 422);
        }
    }

    private function upload(string $field): ?string
    {
        $file = $_FILES[$field] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 5 * 1024 * 1024) {
            throw new \RuntimeException('La imagen no es válida o supera 5 MB.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mime])) {
            throw new \RuntimeException('Solo se permiten imágenes JPG, PNG o WebP.');
        }
        $directory = dirname(__DIR__, 2) . '/public/images/trivia';
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new \RuntimeException('No se pudo preparar el almacenamiento de imágenes.');
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
            throw new \RuntimeException('No se pudo guardar la imagen.');
        }
        return '/public/images/trivia/' . $filename;
    }

    private function validateTrivia(mixed $payload): array
    {
        if (!is_array($payload)) {
            throw new \RuntimeException('Datos de trivia no válidos.');
        }
        $questions = [];
        foreach (($payload['questions'] ?? []) as $question) {
            $options = [];
            foreach (($question['options'] ?? []) as $option) {
                $text = trim((string) ($option['text'] ?? ''));
                if ($text !== '') {
                    $options[] = ['text' => $text, 'correct' => !empty($option['correct'])];
                }
            }
            if (trim((string) ($question['text'] ?? '')) !== '' && count($options) === 4 && count(array_filter($options, fn (array $option): bool => $option['correct'])) === 1) {
                $questions[] = [
                    'text' => trim((string) $question['text']),
                    'explanation' => trim((string) ($question['explanation'] ?? '')),
                    'points' => max(1, (int) ($question['points'] ?? 1)),
                    'options' => $options,
                ];
            }
        }
        if ((int) ($payload['id'] ?? 0) < 1 && trim((string) ($payload['title'] ?? '')) === '') {
            throw new \RuntimeException('La primera trivia necesita título.');
        }
        $startsAt = str_replace('T', ' ', trim((string) ($payload['starts_at'] ?? '')));
        $endsAt = str_replace('T', ' ', trim((string) ($payload['ends_at'] ?? '')));
        $timezone = new \DateTimeZone('America/Costa_Rica');
        $start = $this->parseTriviaDate($startsAt, $timezone);
        $end = $endsAt === '' ? null : $this->parseTriviaDate($endsAt, $timezone);
        if (count($questions) !== 3 || $start === null) {
            throw new \RuntimeException('La trivia debe contener exactamente 3 preguntas válidas con 4 opciones cada una y fecha de inicio.');
        }
        if ($endsAt !== '' && ($end === null || $end < $start)) {
            throw new \RuntimeException('La fecha de finalización debe ser válida y posterior al inicio.');
        }
        $startsAt = $start->format('Y-m-d H:i:s');
        $endsAt = $end?->format('Y-m-d H:i:s') ?? '';
        return [
            'id' => (int) ($payload['id'] ?? 0),
            'title' => trim((string) $payload['title']),
            'description' => trim((string) ($payload['description'] ?? '')),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => in_array(($payload['status'] ?? 'draft'), ['draft', 'active', 'closed'], true) ? $payload['status'] : 'draft',
            'questions' => $questions,
        ];
    }

    private function parseTriviaDate(string $value, \DateTimeZone $timezone): ?\DateTimeImmutable
    {
        foreach (['!Y-m-d H:i', '!Y-m-d H:i:s'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value, $timezone);
            if ($date && $date->format($format === '!Y-m-d H:i' ? 'Y-m-d H:i' : 'Y-m-d H:i:s') === $value) {
                return $date;
            }
        }
        return null;
    }
}