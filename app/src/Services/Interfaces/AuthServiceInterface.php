<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\Models\User;

interface AuthServiceInterface
{
    public function authenticate(string $email, string $password): ?User;
    public function login(string $email, string $password): ?User;
    public function register(array $userData): ?User;
    public function hasRole(User $user, string $role): bool;
    public function isTeacher(User $user): bool;
    public function isStudent(User $user): bool;
}
