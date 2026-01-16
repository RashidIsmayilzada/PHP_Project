<?php

namespace App\Services;

use App\Models\Assignment;
use App\Repositories\AssignmentRepository;
use App\Repositories\CourseRepository;
use App\Services\Interfaces\AssignmentServiceInterface;

class AssignmentService implements AssignmentServiceInterface
{
    private AssignmentRepository $assignmentRepository;
    private CourseRepository $courseRepository;

    public function __construct()
    {
        $this->assignmentRepository = new AssignmentRepository();
        $this->courseRepository = new CourseRepository();
    }

    // Get every assignment in the system
    public function findAll(): array
    {
        return $this->assignmentRepository->findAll();
    }

    // Find a single assignment by its ID
    public function findById(int $id): ?Assignment
    {
        return $this->assignmentRepository->findById($id);
    }

    // Pull all assignments that belong to a specific course
    public function findByCourseId(int $courseId): array
    {
        return $this->assignmentRepository->findByCourseId($courseId);
    }

    // Create a new assignment after checking that everything looks good
    public function createAssignment(array $assignmentData): ?Assignment
    {
        // Validate required fields
        if (empty($assignmentData['course_id']) || empty($assignmentData['assignment_name']) || !isset($assignmentData['max_points'])) {
            return null;
        }

        // Validate course exists
        if (!$this->courseRepository->findById($assignmentData['course_id'])) {
            return null;
        }

        // Validate max_points
        if ($assignmentData['max_points'] <= 0) {
            return null;
        }

        // Create assignment object
        $assignment = new Assignment(
            $assignmentData['course_id'],
            $assignmentData['assignment_name'],
            (float) $assignmentData['max_points'],
            $assignmentData['description'] ?? null,
            $assignmentData['due_date'] ?? null
        );

        return $this->assignmentRepository->create($assignment);
    }

    // Update an assignment with new details
    public function updateAssignment(Assignment $assignment, array $updateData): bool
    {
        // Validate max_points if updated
        if (isset($updateData['max_points']) && $updateData['max_points'] <= 0) {
            return false;
        }

        // Create updated assignment object
        $updatedAssignment = new Assignment(
            $updateData['course_id'] ?? $assignment->getCourseId(),
            $updateData['assignment_name'] ?? $assignment->getAssignmentName(),
            isset($updateData['max_points']) ? (float) $updateData['max_points'] : $assignment->getMaxPoints(),
            $updateData['description'] ?? $assignment->getDescription(),
            $updateData['due_date'] ?? $assignment->getDueDate(),
            $assignment->getAssignmentId(),
            $assignment->getCreatedAt(),
            $assignment->getUpdatedAt()
        );

        return $this->assignmentRepository->update($updatedAssignment);
    }

    // Delete an assignment and all the grades associated with it
    public function deleteAssignment(int $assignmentId): bool
    {
        return $this->assignmentRepository->delete($assignmentId);
    }
}
