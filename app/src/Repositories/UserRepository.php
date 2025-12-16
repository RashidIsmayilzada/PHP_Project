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
