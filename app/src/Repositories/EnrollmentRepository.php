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

    // Create a new enrollment
    public function create(Enrollment $enrollment): ?Enrollment
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO enrollments (student_id, course_id, status, enrollment_date)
                 VALUES (:student_id, :course_id, :status, :enrollment_date)"
            );

            $stmt->execute([
                'student_id' => $enrollment->getStudentId(),
                'course_id' => $enrollment->getCourseId(),
                'status' => $enrollment->getStatus(),
                'enrollment_date' => $enrollment->getEnrollmentDate() ?? date('Y-m-d H:i:s')
            ]);

            $enrollmentId = (int) $this->db->lastInsertId();
            return $this->findById($enrollmentId);
        } catch (PDOException $e) {
            // UNIQUE constraint on (student_id, course_id) may fail
            return null;
        }
    }

    // Update an existing enrollment
    public function update(Enrollment $enrollment): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE enrollments
                 SET student_id = :student_id,
                     course_id = :course_id,
                     status = :status,
                     enrollment_date = :enrollment_date
                 WHERE enrollment_id = :enrollment_id"
            );

            return $stmt->execute([
                'student_id' => $enrollment->getStudentId(),
                'course_id' => $enrollment->getCourseId(),
                'status' => $enrollment->getStatus(),
                'enrollment_date' => $enrollment->getEnrollmentDate(),
                'enrollment_id' => $enrollment->getEnrollmentId()
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Delete an enrollment
    public function delete(int $enrollmentId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM enrollments WHERE enrollment_id = :enrollment_id");
            return $stmt->execute(['enrollment_id' => $enrollmentId]);
        } catch (PDOException $e) {
            return false;
        }
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
