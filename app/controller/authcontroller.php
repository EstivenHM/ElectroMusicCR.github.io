<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\AuthService;
use App\Support\Response;
use App\Support\SessionManager;
use Throwable;

final class AuthController
{
	public function __construct(private AuthService $service)
	{
	}

	public function loginPage(): never
	{
		if (SessionManager::adminUser() !== null) {
			header('Location: /admin/settings');
			exit;
		}

		Response::adminView('login', ['csrfToken' => SessionManager::csrfToken()]);
	}

	public function login(): never
	{
		try {
			SessionManager::validateCsrf((string) ($_POST['csrf_token'] ?? ''), false);
			$user = $this->service->authenticate(
				trim((string) ($_POST['username'] ?? '')),
				(string) ($_POST['password'] ?? '')
			);
			SessionManager::authenticateAdmin($user);
			header('Location: /admin/settings');
			exit;
		} catch (Throwable $exception) {
			Response::adminView('login', [
				'csrfToken' => SessionManager::csrfToken(),
				'error' => $exception->getMessage(),
			]);
		}
	}

	public function logout(): never
	{
		SessionManager::validateCsrf((string) ($_POST['csrf_token'] ?? ''));
		SessionManager::logoutAdmin();
		header('Location: /admin/login');
		exit;
	}
}
