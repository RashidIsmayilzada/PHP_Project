<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Enums\UserRole;
use App\Framework\Repository;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository extends Repository implements UserRepositoryInterface
{
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $rows = $this->fetchAll(
            "SELECT * FROM users ORDER BY last_name, first_name LIMIT :limit OFFSET :offset",
            $this->paginationParams($limit, $offset)
        );

        return array_map([$this, 'mapRowToUser'], $rows);
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->fetch("SELECT * FROM users WHERE email = :email", ['email' => $email]);
        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findById(int $id): ?User
    {
        $row = $this->fetch("SELECT * FROM users WHERE user_id = :id", ['id' => $id]);
        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findByRole(UserRole $role, int $limit = 100, int $offset = 0): array
    {
        $rows = $this->fetchAll(
            "SELECT * FROM users WHERE role = :role ORDER BY last_name, first_name LIMIT :limit OFFSET :offset",
            ['role' => $role->value] + $this->paginationParams($limit, $offset)
        );

        return array_map([$this, 'mapRowToUser'], $rows);
    }

    public function findAllStudents(): array
    {
        return $this->findByRole(UserRole::STUDENT);
    }

    public function findAllTeachers(): array
    {
        return $this->findByRole(UserRole::TEACHER);
    }

    public function create(User $user): ?User
    {
        $sql = "INSERT INTO users (email, password, first_name, last_name, role, student_number)
                VALUES (:email, :password, :first_name, :last_name, :role, :student_number)";
        
        $success = $this->execute($sql, [
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'role' => $user->getRole(),
            'student_number' => $user->getStudentNumber()
        ]);

        if (!$success) return null;

        return $this->findById($this->lastInsertId());
    }

    public function update(User $user): bool
    {
        $sql = "UPDATE users 
                SET email = :email,
                    password = :password,
                    first_name = :first_name,
                    last_name = :last_name,
                    role = :role,
                    student_number = :student_number
                WHERE user_id = :user_id";

        return $this->execute($sql, [
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'role' => $user->getRole(),
            'student_number' => $user->getStudentNumber(),
            'user_id' => $user->getUserId()
        ]);
    }

    public function delete(int $userId): bool
    {
        return $this->execute("DELETE FROM users WHERE user_id = :id", ['id' => $userId]);
    }

    private function mapRowToUser(array $row): User
    {
        return new User(
            $row['email'],
            $row['password'],
            $row['first_name'],
            $row['last_name'],
            $row['role'],
            $row['student_number'] ?? null,
            (int)$row['user_id'],
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }

    private function paginationParams(int $limit, int $offset): array
    {
        return [
            'limit' => max(1, $limit),
            'offset' => max(0, $offset),
        ];
    }
}
