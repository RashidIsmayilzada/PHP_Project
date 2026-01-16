<?php

namespace App\Controllers;

use App\Services\UserService;

class UserController
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    // Return JSON list of all users in the system
    public function index(): void
    {
        header('Content-Type: application/json');
        try {
            $users = $this->userService->findAll();
            $usersData = array_map(function($user) {
                return [
                    'user_id' => $user->getUserId(),
                    'email' => $user->getEmail(),
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'full_name' => $user->getFullName(),
                    'role' => $user->getRole(),
                    'student_number' => $user->getStudentNumber(),
                    'created_at' => $user->getCreatedAt(),
                    'updated_at' => $user->getUpdatedAt()
                ];
            }, $users);
            echo json_encode(['success' => true, 'data' => $usersData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Return JSON data for a specific user by their ID
    public function show(int $id): void
    {
        header('Content-Type: application/json');
        try {
            $user = $this->userService->findById($id);
            if ($user === null) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'User not found']);
                return;
            }

            $userData = [
                'user_id' => $user->getUserId(),
                'email' => $user->getEmail(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'full_name' => $user->getFullName(),
                'role' => $user->getRole(),
                'student_number' => $user->getStudentNumber(),
                'created_at' => $user->getCreatedAt(),
                'updated_at' => $user->getUpdatedAt()
            ];
            echo json_encode(['success' => true, 'data' => $userData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Return JSON list of all students (users with student role)
    public function students(): void
    {
        header('Content-Type: application/json');
        try {
            $students = $this->userService->findAllStudents();
            $studentsData = array_map(function($user) {
                return [
                    'user_id' => $user->getUserId(),
                    'email' => $user->getEmail(),
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'full_name' => $user->getFullName(),
                    'student_number' => $user->getStudentNumber(),
                    'created_at' => $user->getCreatedAt()
                ];
            }, $students);
            echo json_encode(['success' => true, 'data' => $studentsData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Return JSON list of all teachers (users with teacher role)
    public function teachers(): void
    {
        header('Content-Type: application/json');
        try {
            $teachers = $this->userService->findAllTeachers();
            $teachersData = array_map(function($user) {
                return [
                    'user_id' => $user->getUserId(),
                    'email' => $user->getEmail(),
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'full_name' => $user->getFullName(),
                    'created_at' => $user->getCreatedAt()
                ];
            }, $teachers);
            echo json_encode(['success' => true, 'data' => $teachersData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Search for and return a specific user by their email address
    public function findByEmail(string $email): void
    {
        header('Content-Type: application/json');
        try {
            $user = $this->userService->findByEmail($email);
            if ($user === null) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'User not found']);
                return;
            }

            $userData = [
                'user_id' => $user->getUserId(),
                'email' => $user->getEmail(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'full_name' => $user->getFullName(),
                'role' => $user->getRole(),
                'student_number' => $user->getStudentNumber(),
                'created_at' => $user->getCreatedAt(),
                'updated_at' => $user->getUpdatedAt()
            ];
            echo json_encode(['success' => true, 'data' => $userData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
