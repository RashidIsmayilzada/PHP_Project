<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use PDO;
use PDOException;

// Handles all database operations for users
class UserRepository implements UserRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Grab all users from the database, newest first
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        $users = [];

        while ($row = $stmt->fetch()) {
            $users[] = $this->mapRowToUser($row);
        }

        return $users;
    }

    // Look up a specific user by their ID
    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToUser($row) : null;
    }

    // Find a user by their email address (case-insensitive)
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(:email)");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToUser($row) : null;
    }

    // Get all students, sorted by last name then first name
    public function findAllStudents(): array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE role = 'student' ORDER BY last_name, first_name");
        $students = [];

        while ($row = $stmt->fetch()) {
            $students[] = $this->mapRowToUser($row);
        }

        return $students;
    }

    // Get all teachers, sorted by last name then first name
    public function findAllTeachers(): array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE role = 'teacher' ORDER BY last_name, first_name");
        $teachers = [];

        while ($row = $stmt->fetch()) {
            $teachers[] = $this->mapRowToUser($row);
        }

        return $teachers;
    }

    // Save a new user to the database and return it with the generated ID
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

            $userId = (int) $this->db->lastInsertId();
            return $this->findById($userId);
        } catch (PDOException $e) {
            // This usually fails when the email is already taken
            return null;
        }
    }

    // Update an existing user's information in the database
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

    // Remove a user from the database
    public function delete(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE user_id = :user_id");
            return $stmt->execute(['user_id' => $userId]);
        } catch (PDOException $e) {
            // Can't delete if they have enrollments or grades linked to them
            return false;
        }
    }

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
