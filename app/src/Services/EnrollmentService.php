<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Repositories\EnrollmentRepository;
use App\Services\Interfaces\EnrollmentServiceInterface;

class EnrollmentService implements EnrollmentServiceInterface
{

    private EnrollmentRepository $enrollmentRepository;

    public function __construct()
    {
        $this->enrollmentRepository = new EnrollmentRepository();
    }

    public function findAll(): array
    {
        return $this->enrollmentRepository->findAll();
    }

    public function findById(int $id): ?Enrollment
    {
        return $this->enrollmentRepository->findById($id);
    }

    public function findByStudentId(int $studentId): array
    {
        return $this->enrollmentRepository->findByStudentId($studentId);
    }

    public function findByCourseId(int $courseId): array
    {
        return $this->enrollmentRepository->findByCourseId($courseId);
    }

    public function findActiveEnrollmentsByStudentId(int $studentId): array
    {
        return $this->enrollmentRepository->findActiveEnrollmentsByStudentId($studentId);
    }
}