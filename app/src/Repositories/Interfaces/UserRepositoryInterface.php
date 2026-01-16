<?php
namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findAllStudents(): array;
    public function findAllTeachers(): array;
    public function create(User $user): ?User;
    public function update(User $user): bool;
    public function delete(int $userId): bool;
}
