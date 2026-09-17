<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap/autoload.php';

use App\Controller\HomeController;
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
use Config\DatabaseManager;

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

$route = require dirname(__DIR__) . '/routes/web.php';
$route($homeController, $rankingController, $triviaController, $authController, $adminController);
