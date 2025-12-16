<?php

namespace App\Services;

use App\Models\Course;
use App\Repositories\CourseRepository;
use App\Services\Interfaces\CourseServiceInterface;

class CourseService implements CourseServiceInterface
{
    private CourseRepository $courseRepository;

    public function __construct()
    {
        $this->courseRepository = new CourseRepository();
    }

    public function findAll(): array
    {
        return $this->courseRepository->findAll();
    }

    public function findById(int $id): ?Course
    {
        return $this->courseRepository->findById($id);
    }

    public function findByTeacherId(int $teacherId): array
    {
        return $this->courseRepository->findByTeacherId($teacherId);
    }

    public function findBySemester(string $semester): array
    {
        return $this->courseRepository->findBySemester($semester);
    }
}