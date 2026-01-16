<?php
namespace App\Services\Interfaces;

use App\Models\Assignment;

interface AssignmentServiceInterface {
    public function findAll(): array;
    public function findById(int $id): ?Assignment;
    public function findByCourseId(int $courseId): array;
    public function createAssignment(array $assignmentData): ?Assignment;
    public function updateAssignment(Assignment $assignment, array $updateData): bool;
    public function deleteAssignment(int $assignmentId): bool;
}