<?php
namespace App\Repositories\Interfaces;

use App\Models\Grade;
use App\Repositories\Data\CourseGradeData;

interface GradeRepositoryInterface
{
    public function findAll(int $limit = 100, int $offset = 0): array;
    public function findById(int $id): ?Grade;
    public function findByStudentId(int $studentId, int $limit = 100, int $offset = 0): array;
    public function findByAssignmentId(int $assignmentId, int $limit = 100, int $offset = 0): array;
    public function findByStudentAndAssignment(int $studentId, int $assignmentId): ?Grade;
    public function findByCourseId(int $courseId, int $limit = 100, int $offset = 0): array;
    public function findGradeDataForCourseAndStudent(
        int $courseId,
        int $studentId,
        int $limit = 100,
        int $offset = 0
    ): array;
    public function create(Grade $grade): ?Grade;
    public function update(Grade $grade): bool;
    public function delete(int $gradeId): bool;
}
