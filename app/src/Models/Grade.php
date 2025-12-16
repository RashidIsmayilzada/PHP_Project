<?php
namespace App\Models;

// Grade model representing the grades table
class Grade
{
    private ?int $gradeId = null;
    private int $assignmentId;
    private int $studentId;
    private string $status;
    private ?string $feedback = null;
    private ?string $gradedAt = null;
    private ?string $updatedAt = null;

    public function __construct(
        int $assignmentId,
        int $studentId,
        string $status,
        ?string $feedback = null,
        ?int $gradeId = null,
        ?string $gradedAt = null,
        ?string $updatedAt = null
    ) {
        $this->gradeId = $gradeId;
        $this->assignmentId = $assignmentId;
        $this->studentId = $studentId;
        $this->status = $status;
        $this->feedback = $feedback;
        $this->gradedAt = $gradedAt;
        $this->updatedAt = $updatedAt;
    }

    public function getGradeId(): ?int
    {
        return $this->gradeId;
    }

    public function setGradeId(int $gradeId): void
    {
        $this->gradeId = $gradeId;
    }

    public function getAssignmentId(): int
    {
        return $this->assignmentId;
    }

    public function getStudentId(): int
    {
        return $this->studentId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    public function getGradedAt(): ?string
    {
        return $this->gradedAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }
}
