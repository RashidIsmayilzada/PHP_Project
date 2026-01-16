<?php

namespace App\Services\Interfaces;

use App\Models\Enrollment;

interface EnrollmentServiceInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Enrollment;
    public function findByStudentId(int $studentId): array;
    public function findByCourseId(int $courseId): array;
    public function findActiveEnrollmentsByStudentId(int $studentId): array;
    public function enrollStudent(int $studentId, int $courseId): ?Enrollment;
    public function updateEnrollmentStatus(int $enrollmentId, string $status): bool;
    public function dropCourse(int $enrollmentId): bool;
    public function deleteEnrollment(int $enrollmentId): bool;
}