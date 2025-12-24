<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\Grade;
use App\Repositories\Interfaces\GradeRepositoryInterface;
use PDO;
use PDOException;

// Repository for Grade database operations
class GradeRepository implements GradeRepositoryInterface
{
    private PDO $db;

    // Constructor initializes database connection
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Test database connection by counting grades
    public function testConnection(): array
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM grades");
            $result = $stmt->fetch();
            return [
                'success' => true,
                'message' => 'Database connection successful',
                'grade_count' => $result['count']
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    // Find all grades
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM grades ORDER BY graded_at DESC");
        $grades = [];

        while ($row = $stmt->fetch()) {
            $grades[] = $this->mapRowToGrade($row);
        }

        return $grades;
    }

    // Find grade by ID
    public function findById(int $id): ?Grade
    {
        $stmt = $this->db->prepare("SELECT * FROM grades WHERE grade_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToGrade($row) : null;
    }

    // Find grades by student ID
    public function findByStudentId(int $studentId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM grades WHERE student_id = :student_id ORDER BY graded_at DESC");
        $stmt->execute(['student_id' => $studentId]);
        $grades = [];

        while ($row = $stmt->fetch()) {
            $grades[] = $this->mapRowToGrade($row);
        }

        return $grades;
    }

    // Find grades by assignment ID
    public function findByAssignmentId(int $assignmentId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM grades WHERE assignment_id = :assignment_id");
        $stmt->execute(['assignment_id' => $assignmentId]);
        $grades = [];

        while ($row = $stmt->fetch()) {
            $grades[] = $this->mapRowToGrade($row);
        }

        return $grades;
    }

    // Find grade for a specific student and assignment
    public function findByStudentAndAssignment(int $studentId, int $assignmentId): ?Grade
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM grades WHERE student_id = :student_id AND assignment_id = :assignment_id"
        );
        $stmt->execute([
            'student_id' => $studentId,
            'assignment_id' => $assignmentId
        ]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToGrade($row) : null;
    }

    // Find all grades for a course (via assignments)
    public function findByCourseId(int $courseId): array
    {
        $stmt = $this->db->prepare(
            "SELECT g.* FROM grades g
             INNER JOIN assignments a ON g.assignment_id = a.assignment_id
             WHERE a.course_id = :course_id
             ORDER BY g.graded_at DESC"
        );
        $stmt->execute(['course_id' => $courseId]);
        $grades = [];

        while ($row = $stmt->fetch()) {
            $grades[] = $this->mapRowToGrade($row);
        }

        return $grades;
    }

    // Create a new grade
    public function create(Grade $grade): ?Grade
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO grades (assignment_id, student_id, points_earned, feedback, graded_at)
                 VALUES (:assignment_id, :student_id, :points_earned, :feedback, :graded_at)"
            );

            $stmt->execute([
                'assignment_id' => $grade->getAssignmentId(),
                'student_id' => $grade->getStudentId(),
                'points_earned' => $grade->getPointsEarned(),
                'feedback' => $grade->getFeedback(),
                'graded_at' => $grade->getGradedAt() ?? date('Y-m-d H:i:s')
            ]);

            $gradeId = (int) $this->db->lastInsertId();
            return $this->findById($gradeId);
        } catch (PDOException $e) {
            // UNIQUE constraint on (student_id, assignment_id) may fail
            return null;
        }
    }

    // Update an existing grade
    public function update(Grade $grade): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE grades
                 SET assignment_id = :assignment_id,
                     student_id = :student_id,
                     points_earned = :points_earned,
                     feedback = :feedback,
                     graded_at = :graded_at
                 WHERE grade_id = :grade_id"
            );

            return $stmt->execute([
                'assignment_id' => $grade->getAssignmentId(),
                'student_id' => $grade->getStudentId(),
                'points_earned' => $grade->getPointsEarned(),
                'feedback' => $grade->getFeedback(),
                'graded_at' => $grade->getGradedAt(),
                'grade_id' => $grade->getGradeId()
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Delete a grade
    public function delete(int $gradeId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM grades WHERE grade_id = :grade_id");
            return $stmt->execute(['grade_id' => $gradeId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Calculate course average for a student (percentage 0-100)
    public function calculateCourseAverage(int $courseId, int $studentId): ?float
    {
        $stmt = $this->db->prepare(
            "SELECT
                AVG((g.points_earned / a.max_points) * 100) as average
             FROM grades g
             INNER JOIN assignments a ON g.assignment_id = a.assignment_id
             WHERE a.course_id = :course_id AND g.student_id = :student_id"
        );
        $stmt->execute([
            'course_id' => $courseId,
            'student_id' => $studentId
        ]);
        $row = $stmt->fetch();

        return $row && $row['average'] !== null ? (float) $row['average'] : null;
    }

    // Map database row to Grade object
    private function mapRowToGrade(array $row): Grade
    {
        return new Grade(
            $row['assignment_id'],
            $row['student_id'],
            (float) $row['points_earned'],
            $row['feedback'],
            $row['grade_id'],
            $row['graded_at'],
            $row['updated_at']
        );
    }
}
