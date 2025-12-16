<?php
namespace App\Repositories\Interfaces;

use App\Models\Assignment;

interface AssignmentRepositoryInterface
{
    public function testConnection(): array;
    public function findAll(): array;
    public function findById(int $id): ?Assignment;
    public function findByCourseId(int $courseId): array;
}
