<?php

namespace App\Services;

use App\Models\Grade;
use App\Repositories\GradeRepository;
use App\Services\Interfaces\GradeServiceInterface;

class GradeService implements GradeServiceInterface
{
    private GradeRepository $gradeRepository;

    public function __construct()
    {
        $this->gradeRepository = new GradeRepository();
    }

    public function findAll(): array
    {
        return $this->gradeRepository->findAll();
    }

    public function findById(int $id): ?Grade
    {
        return $this->gradeRepository->findById($id);
    }

    public function findByStudentId(int $studentId): array
    {
        return $this->gradeRepository->findByStudentId($studentId);
    }

    public function findByAssignmentId(int $assignmentId): array
    {
        return $this->gradeRepository->findByAssignmentId($assignmentId);
    }

    public function findByStudentAndAssignment(int $studentId, int $assignmentId): ?Grade
    {
        return $this->gradeRepository->findByStudentAndAssignment($studentId, $assignmentId);
    }
}