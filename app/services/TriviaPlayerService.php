<?php

declare(strict_types=1);

namespace App\Services;

use App\Repository\TriviaPlayerRepository;
use App\Support\SessionManager;
use RuntimeException;

final class TriviaPlayerService
{
    public function __construct(private TriviaPlayerRepository $repository)
    {
    }

    public function authenticate(string $nickname, string $recoveryCode, string $mode = 'register'): array
    {
        $this->validateNickname($nickname);
        $normalizedNickname = self::normalize($nickname);
        $player = $this->repository->findByNickname($normalizedNickname);

        if ($mode === 'login') {
            if ($player === null) {
                throw new RuntimeException('El nickname no está registrado. Selecciona "Registrar nickname" para crear tu cuenta.');
            }

            if ($recoveryCode === '' || !hash_equals($player['recovery_code_hash'], hash('sha256', $recoveryCode))) {
                throw new RuntimeException('Código de acceso incorrecto.');
            }

            if ((int) $player['is_active'] !== 1) {
                throw new RuntimeException('Esta cuenta de jugador se encuentra inactiva.');
            }

            SessionManager::authenticatePlayer((int) $player['id']);
            return ['created' => false, 'nickname' => $player['nickname']];
        }

        // Modo registro:
        if ($player !== null) {
            throw new RuntimeException('El nickname ya está en uso. Si es tu cuenta, selecciona "Ingresar con mi nickname".');
        }

        $cleanCode = trim($recoveryCode);
        if (!preg_match('/^\d{6}$/', $cleanCode)) {
            throw new RuntimeException('El código de acceso debe contener exactamente 6 dígitos numéricos.');
        }

        $playerId = $this->repository->create($nickname, $normalizedNickname, hash('sha256', $cleanCode));
        SessionManager::authenticatePlayer($playerId);

        return ['created' => true, 'nickname' => $nickname];
    }

    private function validateNickname(string $nickname): void
    {
        if (strlen($nickname) < 3 || strlen($nickname) > 40 || !preg_match('/^[\p{L}\p{N}_ .-]+$/u', $nickname)) {
            throw new RuntimeException('El nickname debe tener entre 3 y 40 caracteres válidos.');
        }
    }

    private static function normalize(string $nickname): string
    {
        return strtolower(trim(preg_replace('/\s+/u', ' ', $nickname)));
    }
}
