<?php
namespace App\Repositories\Interfaces;

use App\Models\Assignment;

interface AssignmentRepositoryInterface
{
    public function findAll(int $limit = 100, int $offset = 0): array;
    public function findById(int $id): ?Assignment;
    public function findByCourseId(int $courseId, int $limit = 100, int $offset = 0): array;
    public function create(Assignment $assignment): ?Assignment;
    public function update(Assignment $assignment): bool;
    public function delete(int $assignmentId): bool;
}
