<?php

declare(strict_types=1);

use App\Controller\RankingController;
use App\Controller\TriviaController;
use App\Controller\AuthController;
use App\Controller\AdminController;
use App\Support\Response;

return static function (?RankingController $rankingController = null, ?TriviaController $triviaController = null, ?AuthController $authController = null, ?AdminController $adminController = null): void {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = rtrim($path, '/') ?: '/';

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

    Response::json(['error' => 'Ruta no encontrada.'], 404);
};
