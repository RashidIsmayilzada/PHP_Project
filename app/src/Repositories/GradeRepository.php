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

    // Map database row to Grade object
    private function mapRowToGrade(array $row): Grade
    {
        return new Grade(
            $row['assignment_id'],
            $row['student_id'],
            $row['status'],
            $row['feedback'],
            $row['grade_id'],
            $row['graded_at'],
            $row['updated_at']
        );
    }
}
