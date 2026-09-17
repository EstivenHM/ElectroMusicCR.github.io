<?php

declare(strict_types=1);

namespace Config;

use PDO;
use PDOException;
use RuntimeException;

final class DatabaseManager
{
    private const DEFAULT_HOST = '127.0.0.1';
    private const DEFAULT_PORT = '3306';
    private const DEFAULT_NAME = 'electromusiccrdb';
    private const DEFAULT_USER = 'root';

    private static ?array $envCache = null;

    public static function connection(): PDO
    {
        static $connection = null;

        if ($connection instanceof PDO) {
            return $connection;
        }

        $host = self::environment('DB_HOST', self::DEFAULT_HOST);
        $port = self::environment('DB_PORT', self::DEFAULT_PORT);
        $name = self::environment('DB_NAME', self::DEFAULT_NAME);
        $user = self::environment('DB_USER', self::DEFAULT_USER);
        $password = self::environment('DB_PASSWORD', '');

        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $host) || !ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
            throw new RuntimeException('La configuración del servidor de base de datos no es válida.');
        }
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name) || $user === '') {
            throw new RuntimeException('La configuración de la base de datos no es válida.');
        }

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);

        try {
            $connection = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_TIMEOUT => 10,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('No fue posible conectar con la base de datos.', 0, $exception);
        }

        return $connection;
    }

    private static function environment(string $name, string $default): string
    {
        $loadedEnv = self::loadEnvFile();
        if (isset($loadedEnv[$name])) {
            return $loadedEnv[$name];
        }

        $value = getenv($name);
        if (is_string($value) && $value !== '') {
            return trim($value);
        }

        if (isset($_ENV[$name]) && is_string($_ENV[$name]) && $_ENV[$name] !== '') {
            return trim($_ENV[$name]);
        }

        if (isset($_SERVER[$name]) && is_string($_SERVER[$name]) && $_SERVER[$name] !== '') {
            return trim($_SERVER[$name]);
        }

        return $default;
    }

    private static function loadEnvFile(): array
    {
        if (self::$envCache !== null) {
            return self::$envCache;
        }

        self::$envCache = [];
        $envFile = dirname(__DIR__) . '/.env';

        if (!is_file($envFile) || !is_readable($envFile)) {
            return self::$envCache;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return self::$envCache;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                if (
                    (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, '\'') && str_ends_with($value, '\''))
                ) {
                    $value = substr($value, 1, -1);
                }

                self::$envCache[$key] = $value;
            }
        }

        return self::$envCache;
    }
}
