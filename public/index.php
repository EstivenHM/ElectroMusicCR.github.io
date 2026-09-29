<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap/autoload.php';

if (function_exists('set_time_limit')) {
    @set_time_limit(25);
}

// ============================================================================
// MODO MANTENIMIENTO:
// - Cambia $maintenanceMode = true; para activar la pantalla temporal.
// - O crea un archivo vacío llamado "maintenance.flag" en la raíz del proyecto.
// - El acceso a /admin permanece activo para que puedas probar o administrar.
// ============================================================================
$maintenanceMode = false;

if ($maintenanceMode || file_exists(dirname(__DIR__) . '/maintenance.flag')) {
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $isBypass = str_starts_with($currentPath, '/admin') || str_starts_with($currentPath, '/api/admin');
    if (!$isBypass) {
        http_response_code(503);
        header('Retry-After: 300');
        require_once dirname(__DIR__) . '/app/resources/views/maintenance.php';
        exit;
    }
}

use App\Controller\HomeController;
use App\Controller\EventosController;
use App\Controller\RankingController;
use App\Controller\TriviaController;
use App\Controller\AuthController;
use App\Controller\AdminController;
use App\Repository\AuthRepository;
use App\Repository\AdminRepository;
use App\Repository\RankingRepository;
use App\Repository\TriviaRepository;
use App\Repository\TriviaPlayerRepository;
use App\Repository\HomeRepository;
use App\Services\RankingService;
use App\Services\AuthService;
use App\Services\TriviaService;
use App\Services\TriviaPlayerService;
use App\Services\HomeService;
use App\Services\EventosService;
use Config\DatabaseManager;
use App\Controller\CountdownController;

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (preg_match('#^/media/trivia/([a-f0-9]{32})$#i', $path, $matches)) {
    $imageDirectory = dirname(__DIR__) . '/storage/uploads/images/';
    $imagePath = null;
    foreach (['jpg', 'png', 'webp'] as $extension) {
        $candidate = $imageDirectory . strtolower($matches[1]) . '.' . $extension;
        if (is_file($candidate)) {
            $imagePath = $candidate;
            break;
        }
    }
    if ($imagePath === null) {
        http_response_code(404);
        exit;
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($imagePath);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowedMimes, true)) {
        http_response_code(404);
        exit;
    }

    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=86400');
    readfile($imagePath);
    exit;
}

$isApiRequest = str_starts_with(rtrim($path, '/'), '/api/');

$authController = null;
$adminController = null;
if (str_starts_with(rtrim($path, '/'), '/admin/') || str_starts_with(rtrim($path, '/'), '/api/admin/')) {
    try {
        $authService = new AuthService(new AuthRepository(DatabaseManager::connection()));
        $authController = new AuthController($authService);
        $adminController = new AdminController(
            new AdminRepository(DatabaseManager::connection()),
            $authService,
            new TriviaService(new TriviaRepository(DatabaseManager::connection()))
        );
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        http_response_code(503);
        echo 'El servicio administrativo no está disponible.';
        exit;
    }
}

if ($isApiRequest) {
    $rankingController = null;
    $triviaController = null;
    if (rtrim($path, '/') === '/api/ranking') {
        try {
            $rankingController = new RankingController(
                new RankingService(new RankingRepository(DatabaseManager::connection()))
            );
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            http_response_code(503);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'El servicio no está disponible.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    if (in_array(rtrim($path, '/'), ['/api/trivia/current', '/api/trivia/submissions'], true)) {
        try {
            $triviaController = new TriviaController(
                new TriviaService(new TriviaRepository(DatabaseManager::connection()))
            );
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            http_response_code(503);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'El servicio no está disponible.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    if (rtrim($path, '/') === '/api/csrf') {
        $triviaController = new TriviaController(null);
    }

    if (rtrim($path, '/') === '/api/trivia/player') {
        try {
            $triviaController = new TriviaController(
                null,
                new TriviaPlayerService(new TriviaPlayerRepository(DatabaseManager::connection()))
            );
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            http_response_code(503);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'El servicio no está disponible.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Ruta de calificaciones de la página
    if (rtrim($path, '/') === '/api/rating') {
        // El RatingController se instancia en routes/api.php directamente
        // sin dependencias externas (crea su propia conexión).
        // Solo necesitamos asegurar que cae en el flujo API.
    }

    $route = require dirname(__DIR__) . '/routes/api.php';
    $route($rankingController, $triviaController, $adminController);
}

$rankingController = null;
if (rtrim($path, '/') === '/ranking') {
    $rankingController = new RankingController(null);
}

$triviaController = null;
if (rtrim($path, '/') === '/trivia') {
    $triviaController = new TriviaController(null);
}

$homeController = null;
if (rtrim($path, '/') === '/' || rtrim($path, '/') === '') {
    try {
        $homeController = new HomeController(
            new HomeService(new HomeRepository(DatabaseManager::connection()))
        );
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        http_response_code(503);
        echo 'El servicio no esta disponible.';
        exit;
    }
}


$countdownController = null;

if (rtrim($path, '/') === '/countdown') {
    $countdownController = new CountdownController();
}

$eventosController = null;
if (rtrim($path, '/') === '/eventos') {
    try {
        $eventosController = new EventosController(
            new EventosService(new HomeRepository(DatabaseManager::connection()))
        );
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        http_response_code(503);
        echo 'El servicio no está disponible.';
        exit;
    }
}

$route = require dirname(__DIR__) . '/routes/web.php';
$route($homeController, $rankingController, $triviaController, $authController, $adminController, $countdownController, $eventosController);
