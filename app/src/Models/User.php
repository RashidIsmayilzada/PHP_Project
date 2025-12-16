<?php
namespace App\Models;

// User model representing the users table
class User
{
    private ?int $userId = null;
    private string $email;
    private string $password;
    private string $firstName;
    private string $lastName;
    private string $role;
    private ?string $studentNumber = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function __construct(
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        string $role,
        ?string $studentNumber = null,
        ?int $userId = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->userId = $userId;
        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->role = $role;
        $this->studentNumber = $studentNumber;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getStudentNumber(): ?string
    {
        return $this->studentNumber;
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
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
