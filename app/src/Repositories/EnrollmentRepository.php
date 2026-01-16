<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\Enrollment;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use PDO;
use PDOException;

// Handles all database operations for enrollments
class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Grab all enrollments from the database, newest first
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM enrollments ORDER BY enrollment_date DESC");
        $enrollments = [];

        while ($row = $stmt->fetch()) {
            $enrollments[] = $this->mapRowToEnrollment($row);
        }

        return $enrollments;
    }

    // Look up a specific enrollment by its ID
    public function findById(int $id): ?Enrollment
    {
        $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE enrollment_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToEnrollment($row) : null;
    }

    // Get all enrollments for a particular student
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

    // Get all enrollments for a specific course
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

    // Get only the active enrollments for a student
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

    // Save a new enrollment to the database and return it with the generated ID
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
            // This usually fails when trying to enroll the same student in a course twice
            return null;
        }
    }

    // Update an existing enrollment in the database
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

    // Remove an enrollment from the database
    public function delete(int $enrollmentId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM enrollments WHERE enrollment_id = :enrollment_id");
            return $stmt->execute(['enrollment_id' => $enrollmentId]);
        } catch (PDOException $e) {
            return false;
        }
    }

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
