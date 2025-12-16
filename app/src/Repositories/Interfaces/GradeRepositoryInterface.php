<?php
namespace App\Repositories\Interfaces;

use App\Models\Grade;

interface GradeRepositoryInterface
{
    public function testConnection(): array;
    public function findAll(): array;
    public function findById(int $id): ?Grade;
    public function findByStudentId(int $studentId): array;
    public function findByAssignmentId(int $assignmentId): array;
    public function findByStudentAndAssignment(int $studentId, int $assignmentId): ?Grade;
}
