<?php
namespace App\Models;

// Assignment model representing the assignments table
class Assignment
{
    private ?int $assignmentId = null;
    private int $courseId;
    private string $assignmentName;
    private ?string $description = null;
    private ?string $dueDate = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function __construct(
        int $courseId,
        string $assignmentName,
        ?string $description = null,
        ?string $dueDate = null,
        ?int $assignmentId = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->assignmentId = $assignmentId;
        $this->courseId = $courseId;
        $this->assignmentName = $assignmentName;
        $this->description = $description;
        $this->dueDate = $dueDate;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getAssignmentId(): ?int
    {
        return $this->assignmentId;
    }

    public function setAssignmentId(int $assignmentId): void
    {
        $this->assignmentId = $assignmentId;
    }

    public function getCourseId(): int
    {
        return $this->courseId;
    }

    public function getAssignmentName(): string
    {
        return $this->assignmentName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDueDate(): ?string
    {
        return $this->dueDate;
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
