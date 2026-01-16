<?php

namespace App\Controllers;

use App\Services\EnrollmentService;

class EnrollmentController extends BaseController
{
    private EnrollmentService $enrollmentService;

    public function __construct()
    {
        parent::__construct();
        $this->enrollmentService = new EnrollmentService();
    }

    // Handle student enrollment in a course (teacher-initiated)
    public function enrollAction(int $courseId): void
    {
        $this->getAuthService()->requireRole('teacher');
        $_GET['course_id'] = $courseId;

        require __DIR__ . '/../../public/teacher/course-enroll.php';
    }
}
