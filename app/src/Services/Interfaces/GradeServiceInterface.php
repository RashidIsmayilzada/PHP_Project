<?php

namespace App\Services\Interfaces;

use App\Models\Grade;

interface GradeServiceInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Grade;
    public function findByStudentId(int $studentId): array;
    public function findByAssignmentId(int $assignmentId): array;
    public function findByStudentAndAssignment(int $studentId, int $assignmentId): ?Grade;
    public function createGrade(array $gradeData): ?Grade;
    public function updateGrade(Grade $grade, array $updateData): bool;
    public function deleteGrade(int $gradeId): bool;
    public function calculateCourseAverage(int $courseId, int $studentId): ?float;
    public function calculateOverallGPA(int $studentId): float;
    public function percentageToLetterGrade(float $percentage): string;
    public function percentageToGPA(float $percentage): float;
    public function getStudentStatistics(int $studentId): array;
}