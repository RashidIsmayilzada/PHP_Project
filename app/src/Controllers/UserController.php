<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Controller;
use App\Services\Interfaces\UserServiceInterface;

class UserController extends Controller
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    /**
     * API: Get all users (Admin/Teacher only)
     */
    public function index(): void
    {
        Auth::requireRole('teacher');
        
        $users = $this->userService->findAll();
        $data = array_map(fn($u) => [
            'id' => $u->getUserId(),
            'name' => $u->getFullName(),
            'email' => $u->getEmail(),
            'role' => $u->getRole()
        ], $users);

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * API: Get students only
     */
    public function students(): void
    {
        Auth::requireRole('teacher');
        
        $students = $this->userService->findAllStudents();
        $data = array_map(fn($s) => [
            'id' => $s->getUserId(),
            'name' => $s->getFullName(),
            'student_number' => $s->getStudentNumber()
        ], $students);

        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
