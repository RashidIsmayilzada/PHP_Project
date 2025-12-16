<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\Enrollment;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use PDO;
use PDOException;

// Repository for Enrollment database operations
class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    private PDO $db;

    // Constructor initializes database connection
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Test database connection by counting enrollments
    public function testConnection(): array
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM enrollments");
            $result = $stmt->fetch();
            return [
                'success' => true,
                'message' => 'Database connection successful',
                'enrollment_count' => $result['count']
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    // Find all enrollments
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM enrollments ORDER BY enrollment_date DESC");
        $enrollments = [];

        while ($row = $stmt->fetch()) {
            $enrollments[] = $this->mapRowToEnrollment($row);
        }

        return $enrollments;
    }

    // Find enrollment by ID
    public function findById(int $id): ?Enrollment
    {
        $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE enrollment_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToEnrollment($row) : null;
    }

    // Find enrollments by student ID
    public function findByStudentId(int $studentId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE student_id = :student_id");
        $stmt->execute(['student_id' => $studentId]);
        $enrollments = [];

        while ($row = $stmt->fetch()) {
            $enrollments[] = $this->mapRowToEnrollment($row);
        }

        return $enrollments;
    }

    // Find enrollments by course ID
    public function findByCourseId(int $courseId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE course_id = :course_id");
        $stmt->execute(['course_id' => $courseId]);
        $enrollments = [];

        while ($row = $stmt->fetch()) {
            $enrollments[] = $this->mapRowToEnrollment($row);
        }

        return $enrollments;
    }

    // Find active enrollments by student ID
    public function findActiveEnrollmentsByStudentId(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM enrollments WHERE student_id = :student_id AND status = 'active'"
        );
        $stmt->execute(['student_id' => $studentId]);
        $enrollments = [];

        while ($row = $stmt->fetch()) {
            $enrollments[] = $this->mapRowToEnrollment($row);
        }

        return $enrollments;
    }

    // Map database row to Enrollment object
    private function mapRowToEnrollment(array $row): Enrollment
    {
        return new Enrollment(
            $row['student_id'],
            $row['course_id'],
            $row['status'],
            $row['enrollment_id'],
            $row['enrollment_date']
        );
    }
}
