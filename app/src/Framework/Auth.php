<?php
declare(strict_types=1);

namespace App\Framework;

use App\Enums\UserRole;

final class Auth
{
    private static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function login(int $userId, string $role): void
    {
        self::startSession();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = $role;
    }

    public static function logout(): void
    {
        self::startSession();
        
        // Fully clear session state
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    public static function check(): bool
    {
        self::startSession();
        // Strict check: both ID and Role must be present
        return isset($_SESSION['user_id']) && !empty($_SESSION['role']);
    }

    public static function id(): ?int
    {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        self::startSession();
        return $_SESSION['role'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === UserRole::TEACHER->value;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();

        if (self::role() !== $role) {
            http_response_code(403);
            header('Location: /403');
            exit;
        }
    }
}
