<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\Assignment;
use App\Repositories\Interfaces\AssignmentRepositoryInterface;

class AssignmentRepository extends Repository implements AssignmentRepositoryInterface
{
    public function findAll(): array
    {
        $rows = $this->fetchAll("SELECT * FROM assignments ORDER BY due_date DESC");
        return array_map([$this, 'mapRowToAssignment'], $rows);
    }

    public function findById(int $id): ?Assignment
    {
        $row = $this->fetch("SELECT * FROM assignments WHERE assignment_id = :id", ['id' => $id]);
        return $row ? $this->mapRowToAssignment($row) : null;
    }

    public function findByCourseId(int $courseId): array
    {
        $rows = $this->fetchAll("SELECT * FROM assignments WHERE course_id = :course_id ORDER BY due_date", ['course_id' => $courseId]);
        return array_map([$this, 'mapRowToAssignment'], $rows);
    }

    public function create(Assignment $assignment): ?Assignment
    {
        $sql = "INSERT INTO assignments (course_id, assignment_name, description, max_points, due_date)
                VALUES (:course_id, :assignment_name, :description, :max_points, :due_date)";
        
        $success = $this->execute($sql, [
            'course_id' => $assignment->getCourseId(),
            'assignment_name' => $assignment->getAssignmentName(),
            'description' => $assignment->getDescription(),
            'max_points' => $assignment->getMaxPoints(),
            'due_date' => $assignment->getDueDate()
        ]);

        if (!$success) return null;

        return $this->findById($this->lastInsertId());
    }

    public function update(Assignment $assignment): bool
    {
        $sql = "UPDATE assignments
                SET course_id = :course_id,
                    assignment_name = :assignment_name,
                    description = :description,
                    max_points = :max_points,
                    due_date = :due_date
                WHERE assignment_id = :assignment_id";

        return $this->execute($sql, [
            'course_id' => $assignment->getCourseId(),
            'assignment_name' => $assignment->getAssignmentName(),
            'description' => $assignment->getDescription(),
            'max_points' => $assignment->getMaxPoints(),
            'due_date' => $assignment->getDueDate(),
            'assignment_id' => $assignment->getAssignmentId()
        ]);
    }

    public function delete(int $assignmentId): bool
    {
        return $this->execute("DELETE FROM assignments WHERE assignment_id = :assignment_id", ['assignment_id' => $assignmentId]);
    }

    private function mapRowToAssignment(array $row): Assignment
    {
        return new Assignment(
            (int)$row['course_id'],
            $row['assignment_name'],
            (float) $row['max_points'],
            $row['description'],
            $row['due_date'],
            (int)$row['assignment_id'],
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}
