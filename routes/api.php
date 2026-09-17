<?php

declare(strict_types=1);

use App\Controller\RankingController;
use App\Controller\TriviaController;
use App\Controller\AdminController;
use App\Support\Response;

return static function (?RankingController $rankingController = null, ?TriviaController $triviaController = null, ?AdminController $adminController = null): void {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = rtrim($path, '/') ?: '/';

    if ($adminController !== null && $path === '/api/admin/dashboard' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        $adminController->dashboard();
    }

    if ($adminController !== null && $path === '/api/admin/news' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $adminController->createNews();
    }

    if ($adminController !== null && $path === '/api/admin/news/update' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $adminController->updateNews();
    }

    if ($adminController !== null && $path === '/api/admin/news/delete' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $adminController->deleteNews();
    }

    if ($adminController !== null && $path === '/api/admin/events' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $adminController->createEvent();
    }

    if ($adminController !== null && $path === '/api/admin/events/update' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $adminController->updateEvent();
    }

    if ($adminController !== null && $path === '/api/admin/events/delete' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $adminController->deleteEvent();
    }

    if ($adminController !== null && $path === '/api/admin/trivia' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $adminController->saveTrivia();
    }

    if ($adminController !== null && $path === '/api/admin/profile' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $adminController->updateProfile();
    }

    if ($path === '/api/ranking' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $rankingController !== null) {
        $rankingController->index();
    }

    if ($path === '/api/trivia/current' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $triviaController !== null) {
        $triviaController->current();
    }

    if ($path === '/api/csrf' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $triviaController !== null) {
        $triviaController->csrf();
    }

    if ($path === '/api/trivia/player' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $triviaController !== null) {
        $triviaController->player();
    }

    if ($path === '/api/trivia/submissions' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $triviaController !== null) {
        $triviaController->submit();
    }

    Response::json(['error' => 'Ruta no encontrada.'], 404);
};
