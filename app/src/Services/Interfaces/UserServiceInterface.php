<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface UserServiceInterface
{
    public function testConnection(): array;
    public function findAll(): array;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findAllStudents(): array;
    public function findAllTeachers(): array;
    public function createUser(array $userData): ?User;
    public function updateUser(User $user, array $updateData): bool;
    public function deleteUser(int $userId): bool;
}