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
        $password = getenv('DB_PASSWORD') ?: '';

        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $host) || !ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
            throw new RuntimeException('La configuración del servidor de base de datos no es válida.');
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $name) || $user === '') {
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
                PDO::ATTR_TIMEOUT => 5,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('No fue posible conectar con la base de datos.', 0, $exception);
        }

        return $connection;
    }

    private static function environment(string $name, string $default): string
    {
        $value = getenv($name);
        return is_string($value) && $value !== '' ? trim($value) : $default;
    }
}
