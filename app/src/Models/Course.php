<?php
namespace App\Models;

// Course model representing the courses table
class Course
{
    private ?int $courseId = null;
    private string $courseCode;
    private string $courseName;
    private ?string $description = null;
    private int $teacherId;
    private ?float $credits = null;
    private ?string $semester = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function __construct(
        string $courseCode,
        string $courseName,
        int $teacherId,
        ?string $description = null,
        ?float $credits = null,
        ?string $semester = null,
        ?int $courseId = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->courseId = $courseId;
        $this->courseCode = $courseCode;
        $this->courseName = $courseName;
        $this->description = $description;
        $this->teacherId = $teacherId;
        $this->credits = $credits;
        $this->semester = $semester;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getCourseId(): ?int
    {
        return $this->courseId;
    }

    public function setCourseId(int $courseId): void
    {
        $this->courseId = $courseId;
    }

    public function getCourseCode(): string
    {
        return $this->courseCode;
    }

    public function setCourseCode(string $courseCode): void
    {
        $this->courseCode = $courseCode;
    }

    public function getCourseName(): string
    {
        return $this->courseName;
    }

    public function setCourseName(string $courseName): void
    {
        $this->courseName = $courseName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getTeacherId(): int
    {
        return $this->teacherId;
    }

    public function setTeacherId(int $teacherId): void
    {
        $this->teacherId = $teacherId;
    }

    public function getCredits(): ?float
    {
        return $this->credits;
    }

    public function setCredits(?float $credits): void
    {
        $this->credits = $credits;
    }

    public function getSemester(): ?string
    {
        return $this->semester;
    }

    public function setSemester(?string $semester): void
    {
        $this->semester = $semester;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }
}
