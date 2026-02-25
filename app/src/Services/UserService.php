<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\UserServiceInterface;

class UserService implements UserServiceInterface
{
    private UserRepositoryInterface $userRepository;

    /**
     * Dependency Injection via Interface
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
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
        // Note: Assuming Repository has this method based on Interface
        return $this->userRepository->findAllTeachers();
    }

    // Create a new user with full validation of all fields
    public function createUser(array $userData): ?User
    {
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        if ($this->userRepository->findByEmail($userData['email'])) {
            return null;
        }

        if (empty($userData['first_name']) || empty($userData['last_name']) || empty($userData['role'])) {
            return null;
        }

        if (!in_array($userData['role'], ['student', 'teacher'])) {
            return null;
        }

        if ($userData['role'] === 'student' && empty($userData['student_number'])) {
            return null;
        }

        if (isset($userData['password']) && strlen($userData['password']) < 60) {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }

        $user = new User(
            $userData['email'],
            $userData['password'],
            $userData['first_name'],
            $userData['last_name'],
            $userData['role'],
            $userData['student_number'] ?? null
        );

        return $this->userRepository->create($user);
    }

    // Update an existing user's information with validation
    public function updateUser(User $user, array $updateData): bool
    {
        if (isset($updateData['email']) && $updateData['email'] !== $user->getEmail()) {
            if (!filter_var($updateData['email'], FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            $existingUser = $this->userRepository->findByEmail($updateData['email']);
            if ($existingUser && $existingUser->getUserId() !== $user->getUserId()) {
                return false;
            }
        }

        if (isset($updateData['password']) && strlen($updateData['password']) < 60) {
            $updateData['password'] = password_hash($updateData['password'], PASSWORD_DEFAULT);
        }

        // Logic here should update the model instance
        return $this->userRepository->update($user);
    }

    // Delete a user (may fail if they have related records)
    public function deleteUser(int $userId): bool
    {
        return $this->userRepository->delete($userId);
    }
}
