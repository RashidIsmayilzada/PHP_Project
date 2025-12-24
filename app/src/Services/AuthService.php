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

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Attempt to log in a user with email and password
     *
     * @param string $email User's email address
     * @param string $password User's plain text password
     * @return bool True if login successful, false otherwise
     */
    public function login(string $email, string $password): bool
    {
        // Find user by email
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return false;
        }

        // Verify password
        if (!PasswordHelper::verify($password, $user->getPassword())) {
            return false;
        }

        // Store user ID in session
        $_SESSION['user_id'] = $user->getUserId();
        $_SESSION['user_role'] = $user->getRole();

        return true;
    }

    /**
     * Log out the current user
     *
     * @return void
     */
    public function logout(): void
    {
        // Unset all session variables
        $_SESSION = [];

        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy the session
        session_destroy();
    }

    /**
     * Register a new user
     *
     * @param array $userData User data (email, password, first_name, last_name, role, student_number)
     * @return User|null Created user object or null on failure
     */
    public function register(array $userData): ?User
    {
        // Check if email already exists
        if ($this->userRepository->findByEmail($userData['email'])) {
            return null;
        }

        // Hash password
        $hashedPassword = PasswordHelper::hash($userData['password']);

        // Create user object
        $user = new User(
            $userData['email'],
            $hashedPassword,
            $userData['first_name'],
            $userData['last_name'],
            $userData['role'],
            $userData['student_number'] ?? null
        );

        // Save user to database
        $createdUser = $this->userRepository->create($user);

        if ($createdUser) {
            // Auto-login the user
            $_SESSION['user_id'] = $createdUser->getUserId();
            $_SESSION['user_role'] = $createdUser->getRole();
        }

        return $createdUser;
    }

    /**
     * Get the currently logged in user
     *
     * @return User|null User object if logged in, null otherwise
     */
    public function getCurrentUser(): ?User
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return $this->userRepository->findById($_SESSION['user_id']);
    }

    /**
     * Check if a user is currently authenticated
     *
     * @return bool True if user is logged in, false otherwise
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Require authentication - redirect to login if not authenticated
     *
     * @return void
     */
    public function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            header('Location: /login.php');
            exit;
        }
    }

    /**
     * Require a specific role - redirect to 403 if user doesn't have the role
     *
     * @param string $role Required role (student or teacher)
     * @return void
     */
    public function requireRole(string $role): void
    {
        $this->requireAuth();

        $currentUser = $this->getCurrentUser();

        if (!$currentUser || $currentUser->getRole() !== $role) {
            header('Location: /403.php');
            exit;
        }
    }

    /**
     * Check if current user has a specific role
     *
     * @param string $role Role to check (student or teacher)
     * @return bool True if user has the role, false otherwise
     */
    public function hasRole(string $role): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $currentUser = $this->getCurrentUser();
        return $currentUser && $currentUser->getRole() === $role;
    }

    /**
     * Check if current user is a teacher
     *
     * @return bool
     */
    public function isTeacher(): bool
    {
        return $this->hasRole('teacher');
    }

    /**
     * Check if current user is a student
     *
     * @return bool
     */
    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }
}
