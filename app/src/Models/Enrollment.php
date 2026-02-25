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
    private ?string $studentFirstName = null;
    private ?string $studentLastName = null;
    private ?string $studentNumber = null;

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

    public function getStudentFirstName(): ?string
    {
        return $this->studentFirstName;
    }

    public function setStudentFirstName(?string $firstName): void
    {
        $this->studentFirstName = $firstName;
    }

    public function getStudentLastName(): ?string
    {
        return $this->studentLastName;
    }

    public function setStudentLastName(?string $lastName): void
    {
        $this->studentLastName = $lastName;
    }

    public function getStudentNumber(): ?string
    {
        return $this->studentNumber;
    }

    public function setStudentNumber(?string $studentNumber): void
    {
        $this->studentNumber = $studentNumber;
    }

    public function getStudentFullName(): string
    {
        return trim(($this->studentFirstName ?? '') . ' ' . ($this->studentLastName ?? ''));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
