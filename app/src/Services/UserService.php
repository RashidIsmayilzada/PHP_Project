<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\Interfaces\UserServiceInterface;

class UserService implements UserServiceInterface
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function testConnection(): array
    {
        return $this->userRepository->findAll();
    }

    public function findAll(): array
    {
        return $this->userRepository->findAll();
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function findAllStudents(): array
    {
        return $this->userRepository->findAllStudents();
    }

    public function findAllTeachers(): array
    {
        return $this->userRepository->findAllTeachers();
    }
}