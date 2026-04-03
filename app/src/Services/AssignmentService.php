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
        if (!$this->hasValidAssignmentData($assignmentData)) {
            return null;
        }

        if (!$this->courseExists((int)$assignmentData['course_id'])) {
            return null;
        }

        return $this->assignmentRepository->create($this->buildAssignment($assignmentData));
    }

    public function updateAssignment(Assignment $assignment, array $updateData): bool
    {
        if (!$this->canUpdateAssignment($updateData)) {
            return false;
        }

        $this->applyAssignmentUpdates($assignment, $updateData);

        return $this->assignmentRepository->update($assignment);
    }

    public function deleteAssignment(int $assignmentId): bool
    {
        return $this->assignmentRepository->delete($assignmentId);
    }

    private function hasValidAssignmentData(array $assignmentData): bool
    {
        if (
            empty($assignmentData['course_id'])
            || empty($assignmentData['assignment_name'])
            || empty($assignmentData['description'])
            || empty($assignmentData['due_date'])
        ) {
            return false;
        }

        if (!isset($assignmentData['max_points']) || !is_numeric((string) $assignmentData['max_points']) || (float) $assignmentData['max_points'] <= 0) {
            return false;
        }

        $date = \DateTime::createFromFormat('Y-m-d', (string) $assignmentData['due_date']);
        $dateErrors = \DateTime::getLastErrors() ?: ['warning_count' => 0, 'error_count' => 0];

        return $date !== false
            && $dateErrors['warning_count'] === 0
            && $dateErrors['error_count'] === 0;
    }

    private function courseExists(int $courseId): bool
    {
        return $this->courseRepository->findById($courseId) !== null;
    }

    private function buildAssignment(array $assignmentData): Assignment
    {
        return new Assignment(
            (int)$assignmentData['course_id'],
            $assignmentData['assignment_name'],
            (float)$assignmentData['max_points'],
            $assignmentData['description'] ?? null,
            $assignmentData['due_date'] ?? null
        );
    }

    private function canUpdateAssignment(array $updateData): bool
    {
        if (
            (isset($updateData['assignment_name']) && trim((string) $updateData['assignment_name']) === '')
            || (array_key_exists('description', $updateData) && trim((string) $updateData['description']) === '')
            || (array_key_exists('due_date', $updateData) && trim((string) $updateData['due_date']) === '')
        ) {
            return false;
        }

        if (isset($updateData['max_points']) && (!is_numeric((string) $updateData['max_points']) || (float) $updateData['max_points'] <= 0)) {
            return false;
        }

        if (array_key_exists('due_date', $updateData)) {
            $date = \DateTime::createFromFormat('Y-m-d', (string) $updateData['due_date']);
            $dateErrors = \DateTime::getLastErrors() ?: ['warning_count' => 0, 'error_count' => 0];

            return $date !== false
                && $dateErrors['warning_count'] === 0
                && $dateErrors['error_count'] === 0;
        }

        return true;
    }

    private function applyAssignmentUpdates(Assignment $assignment, array $updateData): void
    {
        if (isset($updateData['assignment_name'])) {
            $assignment->setAssignmentName($updateData['assignment_name']);
        }

        if (isset($updateData['max_points'])) {
            $assignment->setMaxPoints((float)$updateData['max_points']);
        }

        if (array_key_exists('description', $updateData)) {
            $assignment->setDescription($updateData['description']);
        }

        if (array_key_exists('due_date', $updateData)) {
            $assignment->setDueDate($updateData['due_date']);
        }
    }
}
