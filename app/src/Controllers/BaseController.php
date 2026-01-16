<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Repositories\UserRepository;

abstract class BaseController
{
    protected ?AuthService $authService = null;

    public function __construct()
    {
        // AuthService is lazy-loaded to prevent database connection errors on error pages
    }

    // Get or create the AuthService instance (lazy loading pattern)
    protected function getAuthService(): AuthService
    {
        if ($this->authService === null) {
            $userRepository = new UserRepository();
            $this->authService = new AuthService($userRepository);
        }
        return $this->authService;
    }

    // Render a view file with provided data
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../../public/views/' . $view . '.php';
    }

    // Redirect to a different URL and stop execution
    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    // Get the currently logged in user or null if not authenticated
    protected function getCurrentUser(): ?\App\Models\User
    {
        return $this->getAuthService()->getCurrentUser();
    }
}
