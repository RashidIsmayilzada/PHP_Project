<?php
namespace App\Repositories\Interfaces;

use App\Models\Course;

interface CourseRepositoryInterface
{
    public function testConnection(): array;
    public function findAll(): array;
    public function findById(int $id): ?Course;
    public function findByTeacherId(int $teacherId): array;
    public function findBySemester(string $semester): array;
}
