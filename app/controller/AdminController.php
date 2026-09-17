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
            Response::json(['data' => $this->triviaService->save($this->validateTrivia($payload), (int) $user['id'], $this->upload('image'))]);
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
        if (count($questions) < 1 || trim((string) ($payload['starts_at'] ?? '')) === '') {
            throw new \RuntimeException('La trivia necesita inicio y al menos una pregunta válida.');
        }
        return [
            'id' => (int) ($payload['id'] ?? 0),
            'title' => trim((string) $payload['title']),
            'description' => trim((string) ($payload['description'] ?? '')),
            'starts_at' => (string) ($payload['starts_at'] ?? ''),
            'ends_at' => (string) ($payload['ends_at'] ?? ''),
            'status' => in_array(($payload['status'] ?? 'draft'), ['draft', 'active', 'closed'], true) ? $payload['status'] : 'draft',
            'questions' => $questions,
        ];
    }
}