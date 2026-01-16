<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Utils\PasswordHelper;

class AuthService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Verify user credentials and create a session if valid
    public function login(string $email, string $password): bool
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!PasswordHelper::verify($password, $user->getPassword())) {
            return false;
        }

        $_SESSION['user_id'] = $user->getUserId();
        $_SESSION['user_role'] = $user->getRole();

        return true;
    }

    // Clear the user's session and destroy the session cookie
    public function logout(): void
    {
        $_SESSION = [];

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();
    }

    // Create a new user account and automatically log them in
    public function register(array $userData): ?User
    {
        if ($this->userRepository->findByEmail($userData['email'])) {
            return null;
        }

        $hashedPassword = PasswordHelper::hash($userData['password']);

        $user = new User(
            $userData['email'],
            $hashedPassword,
            $userData['first_name'],
            $userData['last_name'],
            $userData['role'],
            $userData['student_number'] ?? null
        );

        $createdUser = $this->userRepository->create($user);

        if ($createdUser) {
            $_SESSION['user_id'] = $createdUser->getUserId();
            $_SESSION['user_role'] = $createdUser->getRole();
        }

        return $createdUser;
    }

    // Retrieve the full user object for the currently logged in user
    public function getCurrentUser(): ?User
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return $this->userRepository->findById($_SESSION['user_id']);
    }

    // Check whether any user is currently logged in
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    // Force user to be logged in or redirect them to login page
    public function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }

    // Ensure the current user has a specific role or show forbidden page
    public function requireRole(string $role): void
    {
        $this->requireAuth();

        $currentUser = $this->getCurrentUser();

        if (!$currentUser || $currentUser->getRole() !== $role) {
            header('Location: /403');
            exit;
        }
    }

    // Check if the logged in user has a particular role
    public function hasRole(string $role): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $currentUser = $this->getCurrentUser();
        return $currentUser && $currentUser->getRole() === $role;
    }

    // Quick check if current user is a teacher
    public function isTeacher(): bool
    {
        return $this->hasRole('teacher');
    }

    // Quick check if current user is a student
    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }
}
