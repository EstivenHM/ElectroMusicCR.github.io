<?php

declare(strict_types=1);

namespace App\Services;

use App\Repository\AuthRepository;

final class AuthService
{
    public function __construct(private AuthRepository $repository)
    {
    }

    public function authenticate(string $username, string $password): array
    {
        if ($username === '' || $password === '') {
            throw new \RuntimeException('El usuario y la contraseña son obligatorios.');
        }

        $user = $this->repository->findByUsername($username);
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            throw new \RuntimeException('Los datos ingresados son incorrectos. Intenta nuevamente.');
        }
        if (!in_array((int) $user['role'], [1, 2], true)) {
            throw new \RuntimeException('Este usuario no tiene permisos de acceso.');
        }

        return $user;
    }

    public function updateProfile(int $userId, string $currentUsername, string $newUsername, ?string $currentPassword, ?string $newPassword): array
    {
        $currentUser = $this->repository->findByUsername($currentUsername);
        if ($currentUser === null || (int) $currentUser['id'] !== $userId) {
            throw new \RuntimeException('El usuario no es válido.');
        }
        if ($newUsername === '') {
            throw new \RuntimeException('El usuario no puede quedar vacío.');
        }

        $passwordHash = null;
        if ($newPassword !== null && $newPassword !== '') {
            if ($currentPassword === null || !password_verify($currentPassword, $currentUser['password_hash'])) {
                throw new \RuntimeException('La contraseña actual no es válida.');
            }
            if (strlen($newPassword) < 10) {
                throw new \RuntimeException('La nueva contraseña debe tener al menos 10 caracteres.');
            }
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        return $this->repository->updateProfile(
            $userId,
            $newUsername,
            $passwordHash
        );
    }
}