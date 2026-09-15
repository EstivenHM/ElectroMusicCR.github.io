<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class AuthRepository
{
    public function __construct(private PDO $connection)
    {
    }

    public function findByUsername(string $username): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, username, password_hash, role, last_update
               FROM users
               WHERE username = :username AND role IN (1, 2)
               LIMIT 1'
        );
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function updateProfile(int $userId, string $username, ?string $passwordHash): array
    {
        $fields = ['username = :username'];
        $parameters = ['id' => $userId, 'username' => $username];
        if ($passwordHash !== null) {
            $fields[] = 'password_hash = :password_hash';
            $parameters['password_hash'] = $passwordHash;
        }

        $statement = $this->connection->prepare(
            'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id'
        );
        $statement->execute($parameters);

        $user = $this->findByUsername($username);
        return $user ?: throw new \RuntimeException('No fue posible actualizar el perfil.');
    }
}