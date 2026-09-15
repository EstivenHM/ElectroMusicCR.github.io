<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class SessionManager
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (!session_start([
            'cookie_httponly' => true,
            'cookie_secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
            'use_only_cookies' => true,
        ])) {
            throw new RuntimeException('No fue posible iniciar la sesión.');
        }
    }

    public static function csrfToken(): string
    {
        self::start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf(?string $provided = null, bool $jsonResponse = true): void
    {
        self::start();
        $provided ??= $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $stored = $_SESSION['csrf_token'] ?? '';
        if ($provided === '' || $stored === '' || !hash_equals($stored, $provided)) {
            if (!$jsonResponse) {
                throw new RuntimeException('Solicitud no válida.');
            }

            Response::json(['error' => 'Solicitud no válida.'], 419);
        }
    }

    public static function authenticatePlayer(int $playerId): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['trivia_player_id'] = $playerId;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function playerId(): ?int
    {
        self::start();
        $playerId = $_SESSION['trivia_player_id'] ?? null;
        return is_int($playerId) || ctype_digit((string) $playerId) ? (int) $playerId : null;
    }

    public static function authenticateAdmin(array $user): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['admin_user'] = [
            'id' => (int) $user['id'],
            'username' => (string) $user['username'],
            'role' => (int) $user['role'],
        ];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function adminUser(): ?array
    {
        self::start();
        $user = $_SESSION['admin_user'] ?? null;
        if (!is_array($user) || !isset($user['id'], $user['username'], $user['role'])) {
            return null;
        }

        if (!is_int($user['id']) && !ctype_digit((string) $user['id'])) {
            return null;
        }

        return (int) $user['id'] > 0 && $user['username'] !== '' && in_array((int) $user['role'], [1, 2], true)
            ? $user
            : null;
    }

    public static function requireAdmin(): array
    {
        $user = self::adminUser();
        if ($user === null || !in_array((int) $user['role'], [1, 2], true)) {
            Response::json(['error' => 'Debes iniciar sesión como administrador o colaborador.'], 401);
        }

        return $user;
    }

    public static function requireAdminPage(): array
    {
        $user = self::adminUser();
        if ($user === null) {
            header('Location: /admin/login', true, 302);
            exit;
        }

        return $user;
    }

    public static function logoutAdmin(): void
    {
        self::start();
        unset($_SESSION['admin_user']);
        session_regenerate_id(true);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
