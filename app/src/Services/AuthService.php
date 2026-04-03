<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\PasswordHasherInterface;
use App\Services\Interfaces\SessionInterface;

class AuthService implements AuthServiceInterface
{
    private UserRepositoryInterface $userRepository;
    private PasswordHasherInterface $passwordHasher;
    private SessionInterface $session;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PasswordHasherInterface $passwordHasher,
        SessionInterface $session
    )
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->session = $session;
    }

    // Verify user credentials and return the authenticated user when valid.
    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return null;
        }

        if (!$this->passwordHasher->verify($password, $user->getPassword())) {
            return null;
        }

        return $user;
    }

    // Backward-compatible alias for credential verification.
    public function login(string $email, string $password): ?User
    {
        $this->session->ensureStarted();

        return $this->authenticate($email, $password);
    }

    // Create a new user account without mutating HTTP or session state.
    public function register(array $userData): ?User
    {
        if (!$this->hasRequiredRegistrationFields($userData)) {
            return null;
        }

        if ($this->userRepository->findByEmail($userData['email']) !== null) {
            return null;
        }

        return $this->userRepository->create($this->buildUser($userData));
    }

    // Check whether a user holds a specific role.
    public function hasRole(User $user, string $role): bool
    {
        return $user->getRole() === $role;
    }

    // Quick check if a user is a teacher.
    public function isTeacher(User $user): bool
    {
        return $this->hasRole($user, 'teacher');
    }

    // Quick check if a user is a student.
    public function isStudent(User $user): bool
    {
        return $this->hasRole($user, 'student');
    }

    private function hasRequiredRegistrationFields(array $userData): bool
    {
        $requiredFields = ['email', 'password', 'first_name', 'last_name', 'role'];

        foreach ($requiredFields as $field) {
            if (empty($userData[$field])) {
                return false;
            }
        }

        return true;
    }

    private function buildUser(array $userData): User
    {
        return new User(
            $userData['email'],
            $this->passwordHasher->hash($userData['password']),
            $userData['first_name'],
            $userData['last_name'],
            $userData['role'],
            $userData['student_number'] ?? null
        );
    }
}
