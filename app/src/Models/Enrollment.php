<?php
namespace App\Models;

// Enrollment model representing the enrollments table
class Enrollment
{
    private ?int $enrollmentId = null;
    private int $studentId;
    private int $courseId;
    private ?string $enrollmentDate = null;
    private string $status;

    public function __construct(
        int $studentId,
        int $courseId,
        string $status = 'active',
        ?int $enrollmentId = null,
        ?string $enrollmentDate = null
    ) {
        $this->enrollmentId = $enrollmentId;
        $this->studentId = $studentId;
        $this->courseId = $courseId;
        $this->enrollmentDate = $enrollmentDate;
        $this->status = $status;
    }

    public function getEnrollmentId(): ?int
    {
        return $this->enrollmentId;
    }

    public function setEnrollmentId(int $enrollmentId): void
    {
        $this->enrollmentId = $enrollmentId;
    }

    public function getStudentId(): int
    {
        return $this->studentId;
    }

    public function getCourseId(): int
    {
        return $this->courseId;
    }

    public function getEnrollmentDate(): ?string
    {
        return $this->enrollmentDate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
