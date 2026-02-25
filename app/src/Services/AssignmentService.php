<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Assignment;
use App\Repositories\Interfaces\AssignmentRepositoryInterface;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Services\Interfaces\AssignmentServiceInterface;

class AssignmentService implements AssignmentServiceInterface
{
    private AssignmentRepositoryInterface $assignmentRepository;
    private CourseRepositoryInterface $courseRepository;

    public function __construct(
        AssignmentRepositoryInterface $assignmentRepository,
        CourseRepositoryInterface $courseRepository
    ) {
        $this->assignmentRepository = $assignmentRepository;
        $this->courseRepository = $courseRepository;
    }

    public function findAll(): array
    {
        return $this->assignmentRepository->findAll();
    }

    public function findById(int $id): ?Assignment
    {
        return $this->assignmentRepository->findById($id);
    }

    public function findByCourseId(int $courseId): array
    {
        return $this->assignmentRepository->findByCourseId($courseId);
    }

    public function createAssignment(array $assignmentData): ?Assignment
    {
        if (empty($assignmentData['course_id']) || empty($assignmentData['assignment_name']) || !isset($assignmentData['max_points'])) {
            return null;
        }

        if (!$this->courseRepository->findById((int)$assignmentData['course_id'])) {
            return null;
        }

        if ($assignmentData['max_points'] <= 0) {
            return null;
        }

        $assignment = new Assignment(
            (int)$assignmentData['course_id'],
            $assignmentData['assignment_name'],
            (float)$assignmentData['max_points'],
            $assignmentData['description'] ?? null,
            $assignmentData['due_date'] ?? null
        );

        return $this->assignmentRepository->create($assignment);
    }

    public function updateAssignment(Assignment $assignment, array $updateData): bool
    {
        if (isset($updateData['max_points'])) {
            if ($updateData['max_points'] <= 0) {
                return false;
            }
            $assignment->setMaxPoints((float)$updateData['max_points']);
        }

        if (isset($updateData['assignment_name'])) {
            $assignment->setAssignmentName($updateData['assignment_name']);
        }

        if (array_key_exists('description', $updateData)) {
            $assignment->setDescription($updateData['description']);
        }

        if (isset($updateData['due_date'])) {
            $assignment->setDueDate($updateData['due_date']);
        }

        return $this->assignmentRepository->update($assignment);
    }

    public function deleteAssignment(int $assignmentId): bool
    {
        return $this->assignmentRepository->delete($assignmentId);
    }
}
