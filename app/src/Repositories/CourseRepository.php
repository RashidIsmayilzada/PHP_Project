<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\Course;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use PDO;
use PDOException;

// Repository for Course database operations
class CourseRepository implements CourseRepositoryInterface
{
    private PDO $db;

    // Constructor initializes database connection
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Test database connection by counting courses
    public function testConnection(): array
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM courses");
            $result = $stmt->fetch();
            return [
                'success' => true,
                'message' => 'Database connection successful',
                'course_count' => $result['count']
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    // Find all courses
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM courses ORDER BY course_code");
        $courses = [];

        while ($row = $stmt->fetch()) {
            $courses[] = $this->mapRowToCourse($row);
        }

        return $courses;
    }

    // Find course by ID
    public function findById(int $id): ?Course
    {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE course_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToCourse($row) : null;
    }

    // Find courses by teacher ID
    public function findByTeacherId(int $teacherId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE teacher_id = :teacher_id ORDER BY course_code");
        $stmt->execute(['teacher_id' => $teacherId]);
        $courses = [];

        while ($row = $stmt->fetch()) {
            $courses[] = $this->mapRowToCourse($row);
        }

        return $courses;
    }

    // Find courses by semester
    public function findBySemester(string $semester): array
    {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE semester = :semester ORDER BY course_code");
        $stmt->execute(['semester' => $semester]);
        $courses = [];

        while ($row = $stmt->fetch()) {
            $courses[] = $this->mapRowToCourse($row);
        }

        return $courses;
    }

    // Map database row to Course object
    private function mapRowToCourse(array $row): Course
    {
        return new Course(
            $row['course_code'],
            $row['course_name'],
            $row['teacher_id'],
            $row['description'],
            $row['credits'] ? (float)$row['credits'] : null,
            $row['semester'],
            $row['course_id'],
            $row['created_at'],
            $row['updated_at']
        );
    }
}
