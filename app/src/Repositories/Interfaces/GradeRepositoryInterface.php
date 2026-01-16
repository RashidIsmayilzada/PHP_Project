<?php
namespace App\Repositories\Interfaces;

use App\Models\Grade;

interface GradeRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Grade;
    public function findByStudentId(int $studentId): array;
    public function findByAssignmentId(int $assignmentId): array;
    public function findByStudentAndAssignment(int $studentId, int $assignmentId): ?Grade;
    public function findByCourseId(int $courseId): array;
    public function create(Grade $grade): ?Grade;
    public function update(Grade $grade): bool;
    public function delete(int $gradeId): bool;
    public function getGradeDataForCourseAndStudent(int $courseId, int $studentId): array;
}
