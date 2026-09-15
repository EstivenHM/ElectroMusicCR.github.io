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

    public function authenticate(string $nickname, string $recoveryCode): array
    {
        $this->validateNickname($nickname);
        $normalizedNickname = self::normalize($nickname);
        $player = $this->repository->findByNickname($normalizedNickname);

        if ($player !== null) {
            if ($recoveryCode === '' || !hash_equals($player['recovery_code_hash'], hash('sha256', $recoveryCode))) {
                throw new RuntimeException('Nickname o código no válidos.');
            }
            if ((int) $player['is_active'] !== 1) {
                throw new RuntimeException('Nickname o código no válidos.');
            }

            SessionManager::authenticatePlayer((int) $player['id']);
            return ['created' => false, 'nickname' => $player['nickname']];
        }

        if ($recoveryCode !== '') {
            throw new RuntimeException('Nickname o código no válidos.');
        }

        $code = strtoupper(bin2hex(random_bytes(8)));
        $playerId = $this->repository->create($nickname, $normalizedNickname, hash('sha256', $code));
        SessionManager::authenticatePlayer($playerId);

        return ['created' => true, 'nickname' => $nickname, 'recovery_code' => $code];
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
