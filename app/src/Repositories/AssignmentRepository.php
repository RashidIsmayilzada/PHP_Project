<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\Assignment;
use App\Repositories\Interfaces\AssignmentRepositoryInterface;
use PDO;
use PDOException;

// Repository for Assignment database operations
class AssignmentRepository implements AssignmentRepositoryInterface
{
    private PDO $db;

    // Constructor initializes database connection
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Test database connection by counting assignments
    public function testConnection(): array
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM assignments");
            $result = $stmt->fetch();
            return [
                'success' => true,
                'message' => 'Database connection successful',
                'assignment_count' => $result['count']
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    // Find all assignments
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM assignments ORDER BY due_date DESC");
        $assignments = [];

        while ($row = $stmt->fetch()) {
            $assignments[] = $this->mapRowToAssignment($row);
        }

        return $assignments;
    }

    // Find assignment by ID
    public function findById(int $id): ?Assignment
    {
        $stmt = $this->db->prepare("SELECT * FROM assignments WHERE assignment_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToAssignment($row) : null;
    }

    // Find assignments by course ID
    public function findByCourseId(int $courseId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM assignments WHERE course_id = :course_id ORDER BY due_date");
        $stmt->execute(['course_id' => $courseId]);
        $assignments = [];

        while ($row = $stmt->fetch()) {
            $assignments[] = $this->mapRowToAssignment($row);
        }

        return $assignments;
    }

    // Create a new assignment
    public function create(Assignment $assignment): ?Assignment
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO assignments (course_id, assignment_name, description, max_points, due_date)
                 VALUES (:course_id, :assignment_name, :description, :max_points, :due_date)"
            );

            $stmt->execute([
                'course_id' => $assignment->getCourseId(),
                'assignment_name' => $assignment->getAssignmentName(),
                'description' => $assignment->getDescription(),
                'max_points' => $assignment->getMaxPoints(),
                'due_date' => $assignment->getDueDate()
            ]);

            $assignmentId = (int) $this->db->lastInsertId();
            return $this->findById($assignmentId);
        } catch (PDOException $e) {
            return null;
        }
    }

    // Update an existing assignment
    public function update(Assignment $assignment): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE assignments
                 SET course_id = :course_id,
                     assignment_name = :assignment_name,
                     description = :description,
                     max_points = :max_points,
                     due_date = :due_date
                 WHERE assignment_id = :assignment_id"
            );

            return $stmt->execute([
                'course_id' => $assignment->getCourseId(),
                'assignment_name' => $assignment->getAssignmentName(),
                'description' => $assignment->getDescription(),
                'max_points' => $assignment->getMaxPoints(),
                'due_date' => $assignment->getDueDate(),
                'assignment_id' => $assignment->getAssignmentId()
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Delete an assignment (cascade deletes grades via FK constraints)
    public function delete(int $assignmentId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM assignments WHERE assignment_id = :assignment_id");
            return $stmt->execute(['assignment_id' => $assignmentId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Map database row to Assignment object
    private function mapRowToAssignment(array $row): Assignment
    {
        return new Assignment(
            $row['course_id'],
            $row['assignment_name'],
            (float) $row['max_points'],
            $row['description'],
            $row['due_date'],
            $row['assignment_id'],
            $row['created_at'],
            $row['updated_at']
        );
    }
}
