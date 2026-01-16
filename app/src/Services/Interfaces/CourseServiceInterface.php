<?php

namespace App\Services\Interfaces;

use App\Models\Course;

interface CourseServiceInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Course;
    public function findByTeacherId(int $teacherId): array;
    public function findBySemester(string $semester): array;
    public function getCoursesForStudent(int $studentId): array;
    public function getCoursesForTeacher(int $teacherId): array;
    public function createCourse(array $courseData): ?Course;
    public function updateCourse(Course $course, array $updateData): bool;
    public function deleteCourse(int $courseId): bool;
}