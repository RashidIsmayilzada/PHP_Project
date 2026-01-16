<?php
namespace App\Repositories\Interfaces;

use App\Models\Course;

interface CourseRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Course;
    public function findByTeacherId(int $teacherId): array;
    public function findBySemester(string $semester): array;
    public function findByStudentId(int $studentId): array;
    public function create(Course $course): ?Course;
    public function update(Course $course): bool;
    public function delete(int $courseId): bool;
}
