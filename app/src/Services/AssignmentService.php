<?php

namespace App\Services;

use App\Models\Assignment;
use App\Repositories\AssignmentRepository;
use App\Services\Interfaces\AssignmentServiceInterface;

class AssignmentService implements AssignmentServiceInterface
{
    private AssignmentRepository $assignmentRepository;
    public function __construct()
    {
        $this->assignmentRepository = new AssignmentRepository();
    }

    public function findAll(): array
    {
        return $this->assignmentRepository->findAll();
    }

    public function findById(int $id): ?Assignment
    {
        return $this->assignmentRepository->findById($id);
    }

    public function findByCourseId(int $courseId): array
    {
        return $this->assignmentRepository->findByCourseId($courseId);
    }
}