<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository extends Repository implements UserRepositoryInterface
{
    public function findAll(): array
    {
        $rows = $this->fetchAll("SELECT * FROM users ORDER BY last_name, first_name");
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

    public function findAllStudents(): array
    {
        $rows = $this->fetchAll("SELECT * FROM users WHERE role = 'student' ORDER BY last_name, first_name");
        return array_map([$this, 'mapRowToUser'], $rows);
    }

    public function findAllTeachers(): array
    {
        $rows = $this->fetchAll("SELECT * FROM users WHERE role = 'teacher' ORDER BY last_name, first_name");
        return array_map([$this, 'mapRowToUser'], $rows);
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
}
