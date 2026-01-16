<?php

namespace App\Controllers;

use App\Services\CourseService;
use App\Repositories\CourseRepository;
use App\Repositories\UserRepository;

class CourseController extends BaseController
{
    private CourseService $courseService;

    public function __construct()
    {
        parent::__construct();
        $courseRepository = new CourseRepository();
        $userRepository = new UserRepository();
        $this->courseService = new CourseService($courseRepository, $userRepository);
    }

    // Display detailed information about a specific course (teacher view)
    public function show(int $id): void
    {
        $this->getAuthService()->requireRole('teacher');
        $_GET['id'] = $id;

        require __DIR__ . '/../../public/teacher/course-detail.php';
    }

    // Handle course creation form display and processing
    public function createAction(): void
    {
        $this->getAuthService()->requireRole('teacher');

        require __DIR__ . '/../../public/teacher/course-create.php';
    }

    // Handle course editing form display and processing
    public function editAction(int $id): void
    {
        $this->getAuthService()->requireRole('teacher');
        $_GET['id'] = $id;

        require __DIR__ . '/../../public/teacher/course-edit.php';
    }

    // Handle course deletion with confirmation
    public function delete(int $id): void
    {
        $this->getAuthService()->requireRole('teacher');
        $_GET['id'] = $id;

        require __DIR__ . '/../../public/teacher/course-delete.php';
    }
}
