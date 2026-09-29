<?php

declare(strict_types=1);

use App\Controller\HomeController;
use App\Controller\RankingController;
use App\Controller\TriviaController;
use App\Controller\AuthController;
use App\Controller\AdminController;
use App\Controller\CountdownController;
use App\Controller\EventosController;
use App\Support\Response;

return static function (
    ?HomeController $homeController = null,
    ?RankingController $rankingController = null,
    ?TriviaController $triviaController = null,
    ?AuthController $authController = null,
    ?AdminController $adminController = null,
    ?CountdownController $countdownController = null,
    ?EventosController $eventosController = null
): void {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = rtrim($path, '/') ?: '/';

    if ($homeController !== null && $path === '/') {
        $homeController->index();
    }

    if ($authController !== null && $path === '/admin/login') {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $authController->login();
        }
        $authController->loginPage();
    }

    if ($path === '/admin') {
        header('Location: /admin/settings');
        exit;
    }

    if ($authController !== null && $path === '/admin/logout' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $authController->logout();
    }

    if ($adminController !== null && $path === '/admin/settings') {
        $adminController->page();
    }

    if ($path === '/ranking' && $rankingController !== null) {
        $rankingController->page();
    }

    if ($path === '/trivia' && $triviaController !== null) {
        $triviaController->page();
    }

    if ($path === '/countdown' && $countdownController !== null) {
        $countdownController->page();
    }

    if ($path === '/eventos' && $eventosController !== null) {
        $eventosController->index();
    }

    http_response_code(404);
$view404 = dirname(__DIR__) . '/404.php';

if (file_exists($view404)) {
    require_once $view404;
} else {
    Response::json(['error' => 'Ruta no encontrada.'], 404);
}
exit;
};
