<?php
namespace App\Repositories\Interfaces;

use App\Models\Course;

interface CourseRepositoryInterface
{
    public function findAll(int $limit = 100, int $offset = 0): array;
    public function findById(int $id): ?Course;
    public function findByTeacherId(int $teacherId, int $limit = 100, int $offset = 0): array;
    public function findBySemester(string $semester, int $limit = 100, int $offset = 0): array;
    public function findByStudentId(int $studentId, int $limit = 100, int $offset = 0): array;
    public function create(Course $course): ?Course;
    public function update(Course $course): bool;
    public function delete(int $courseId): bool;
}
