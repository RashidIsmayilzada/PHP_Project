<?php
namespace App\Repositories\Interfaces;

use App\Enums\UserRole;
use App\Models\User;

interface UserRepositoryInterface
{
    public function findAll(int $limit = 100, int $offset = 0): array;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findByRole(UserRole $role, int $limit = 100, int $offset = 0): array;
    public function findAllStudents(): array;
    public function findAllTeachers(): array;
    public function create(User $user): ?User;
    public function update(User $user): bool;
    public function delete(int $userId): bool;
}
