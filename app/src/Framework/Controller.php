<?php
declare(strict_types=1);

namespace App\Framework;

class Controller
{
    public function __construct()
    {
    }

    protected function render(string $view, array $data = []): void
    {
        // Build the full path to the requested view file.
        // It expects the view name relative to the Views directory (e.g., 'auth/login')
        $viewPath = __DIR__ . '/../Views/' . trim($view, '/') . '.php';

        if (!is_file($viewPath)) {
            // If the view does not exist, fail with a server error.
            http_response_code(500);
            echo 'View not found: ' . $viewPath;
            return;
        }

        // Turn array keys into local variables for the view.
        extract($data, EXTR_SKIP);

        // Load and clear flash messages for one-time display.
        $flash = $this->getFlashAll(); // Assuming getFlashAll will be implemented or removed

        // Prefer rendering inside the layout if it exists.
        $layoutPath = __DIR__ . '/../Views/layout.php'; // Expects app/src/Views/layout.php
        if (is_file($layoutPath)) {
            require $layoutPath;
            return;
        }

        // Fallback: render the view directly.
        require $viewPath;
    }

    protected function includeView(string $view, array $data = []): void
    {
        // Build the full path to a partial view file.
        $viewPath = __DIR__ . '/../Views/' . trim($view, '/') . '.php';

        if (!is_file($viewPath)) {
            // If the partial does not exist, output nothing.
            echo '';
            return;
        }

        // Turn array keys into local variables for the partial.
        extract($data, EXTR_SKIP);
        require $viewPath;
    }

    protected function redirect(string $path): void
    {
        // Send a redirect header and stop execution.
        header('Location: ' . $path);
        exit;
    }

    protected function setFlash(string $type, string $message): void
    {
        // Ensure the session is available for storing flash data.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Store the flash message by type.
        $_SESSION['flash'][$type][] = $message;
    }

    protected function getFlashAll(): array
    {
        // Ensure the session is available for reading flash data.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Read and clear flash messages so they display once.
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        return $flash;
    }

    protected function request(string $key, mixed $default = null): mixed
    {
        // Read from POST first, then GET, then use the default.
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}
