<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use PDO;
use PDOException;

// Repository for User database operations
class UserRepository implements UserRepositoryInterface
{
    private PDO $db;

    // Constructor initializes database connection
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Test database connection by counting users
    public function testConnection(): array
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();
            return [
                'success' => true,
                'message' => 'Database connection successful',
                'user_count' => $result['count']
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    // Find all users
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        $users = [];

        while ($row = $stmt->fetch()) {
            $users[] = $this->mapRowToUser($row);
        }

        return $users;
    }

    // Find user by ID
    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToUser($row) : null;
    }

    // Find user by email
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToUser($row) : null;
    }

    // Find all students
    public function findAllStudents(): array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE role = 'student' ORDER BY last_name, first_name");
        $students = [];

        while ($row = $stmt->fetch()) {
            $students[] = $this->mapRowToUser($row);
        }

        return $students;
    }

    // Find all teachers
    public function findAllTeachers(): array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE role = 'teacher' ORDER BY last_name, first_name");
        $teachers = [];

        while ($row = $stmt->fetch()) {
            $teachers[] = $this->mapRowToUser($row);
        }

        return $teachers;
    }

    // Create a new user
    public function create(User $user): ?User
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO users (email, password, first_name, last_name, role, student_number)
                 VALUES (:email, :password, :first_name, :last_name, :role, :student_number)"
            );

            $stmt->execute([
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'role' => $user->getRole(),
                'student_number' => $user->getStudentNumber()
            ]);

            // Get the last inserted ID
            $userId = (int) $this->db->lastInsertId();

            // Return the created user with the new ID
            return $this->findById($userId);
        } catch (PDOException $e) {
            // Return null if creation fails (e.g., duplicate email)
            return null;
        }
    }

    // Update an existing user
    public function update(User $user): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE users
                 SET email = :email,
                     password = :password,
                     first_name = :first_name,
                     last_name = :last_name,
                     role = :role,
                     student_number = :student_number
                 WHERE user_id = :user_id"
            );

            return $stmt->execute([
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'role' => $user->getRole(),
                'student_number' => $user->getStudentNumber(),
                'user_id' => $user->getUserId()
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Delete a user
    public function delete(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE user_id = :user_id");
            return $stmt->execute(['user_id' => $userId]);
        } catch (PDOException $e) {
            // Foreign key constraint may prevent deletion
            return false;
        }
    }

    // Map database row to User object
    private function mapRowToUser(array $row): User
    {
        return new User(
            $row['email'],
            $row['password'],
            $row['first_name'],
            $row['last_name'],
            $row['role'],
            $row['student_number'],
            $row['user_id'],
            $row['created_at'],
            $row['updated_at']
        );
    }
}
