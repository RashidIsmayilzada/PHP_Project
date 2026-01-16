<?php
namespace App\Repositories\Interfaces;

use App\Models\Assignment;

interface AssignmentRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Assignment;
    public function findByCourseId(int $courseId): array;
    public function create(Assignment $assignment): ?Assignment;
    public function update(Assignment $assignment): bool;
    public function delete(int $assignmentId): bool;
}
