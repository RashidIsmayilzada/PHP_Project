<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\PasswordHasherInterface;
use App\Services\Interfaces\UserServiceInterface;

class UserService implements UserServiceInterface
{
    private const VALID_ROLES = ['student', 'teacher'];

    private UserRepositoryInterface $userRepository;
    private PasswordHasherInterface $passwordHasher;

    /**
     * Dependency Injection via Interface
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        PasswordHasherInterface $passwordHasher
    )
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Implements the missing testConnection method from UserServiceInterface.
     * Performs a simple check to see if the repository/database is reachable.
     */
    public function testConnection(): array
    {
        try {
            $users = $this->userRepository->findAll();
            return [
                'status' => 'success',
                'message' => 'Database connection is healthy.',
                'count' => count($users)
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    // Get all users from the database
    public function findAll(): array
    {
        return $this->userRepository->findAll();
    }

    // Find a specific user by their ID
    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    // Find a user by their email address
    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    // Get all users who have the student role
    public function findAllStudents(): array
    {
        return $this->userRepository->findAllStudents();
    }

    // Get all users who have the teacher role
    public function findAllTeachers(): array
    {
        return $this->userRepository->findAllTeachers();
    }

    // Create a new user with full validation of all fields
    public function createUser(array $userData): ?User
    {
        if (!$this->hasValidNewUserData($userData)) {
            return null;
        }

        if ($this->emailExists($userData['email'])) {
            return null;
        }

        return $this->userRepository->create($this->buildUser($userData));
    }

    // Update an existing user's information with validation
    public function updateUser(User $user, array $updateData): bool
    {
        if (!$this->canUpdateUser($user, $updateData)) {
            return false;
        }

        // Apply the validated changes before persisting so the repository writes fresh state.
        $this->applyUserUpdates($user, $updateData);

        return $this->userRepository->update($user);
    }

    // Delete a user (may fail if they have related records)
    public function deleteUser(int $userId): bool
    {
        return $this->userRepository->delete($userId);
    }

    private function hasValidNewUserData(array $userData): bool
    {
        $requiredFields = ['email', 'password', 'first_name', 'last_name', 'role'];

        foreach ($requiredFields as $field) {
            if (empty($userData[$field])) {
                return false;
            }
        }

        return $this->hasValidRoleData($userData['role'], $userData['student_number'] ?? null)
            && filter_var($userData['email'], FILTER_VALIDATE_EMAIL) !== false;
    }

    private function hasValidRoleData(string $role, ?string $studentNumber): bool
    {
        if (!in_array($role, self::VALID_ROLES, true)) {
            return false;
        }

        return $role !== 'student' || !empty($studentNumber);
    }

    private function emailExists(string $email, ?int $ignoredUserId = null): bool
    {
        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser === null) {
            return false;
        }

        return $ignoredUserId === null || $existingUser->getUserId() !== $ignoredUserId;
    }

    private function buildUser(array $userData): User
    {
        return new User(
            $userData['email'],
            $this->normalizePassword($userData['password']),
            $userData['first_name'],
            $userData['last_name'],
            $userData['role'],
            $userData['student_number'] ?? null
        );
    }

    private function canUpdateUser(User $user, array $updateData): bool
    {
        if (isset($updateData['email']) && !$this->isEmailAvailableForUser($user, $updateData['email'])) {
            return false;
        }

        $role = $updateData['role'] ?? $user->getRole();
        $studentNumber = array_key_exists('student_number', $updateData)
            ? $updateData['student_number']
            : $user->getStudentNumber();

        return $this->hasValidRoleData($role, $studentNumber);
    }

    private function isEmailAvailableForUser(User $user, string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return !$this->emailExists($email, $user->getUserId());
    }

    private function applyUserUpdates(User $user, array $updateData): void
    {
        if (isset($updateData['email'])) {
            $user->setEmail($updateData['email']);
        }

        if (isset($updateData['password'])) {
            $user->setPassword($this->normalizePassword($updateData['password']));
        }

        if (isset($updateData['first_name'])) {
            $user->setFirstName($updateData['first_name']);
        }

        if (isset($updateData['last_name'])) {
            $user->setLastName($updateData['last_name']);
        }

        if (isset($updateData['role'])) {
            $user->setRole($updateData['role']);
        }

        if (array_key_exists('student_number', $updateData)) {
            $user->setStudentNumber($updateData['student_number']);
        }
    }

    private function normalizePassword(string $password): string
    {
        return strlen($password) < 60
            ? $this->passwordHasher->hash($password)
            : $password;
    }
}
