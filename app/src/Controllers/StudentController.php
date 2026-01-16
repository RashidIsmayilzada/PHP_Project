<?php

namespace App\Controllers;

class StudentController extends BaseController
{
    // Show student dashboard with enrolled courses and grades
    public function dashboard(): void
    {
        $this->getAuthService()->requireRole('student');

        require __DIR__ . '/../../public/student/dashboard.php';
    }

    // Show detailed information about a specific course for student
    public function courseDetail(int $id): void
    {
        $this->getAuthService()->requireRole('student');
        $_GET['id'] = $id;

        require __DIR__ . '/../../public/student/course-detail.php';
    }

    // Show student statistics including GPA and grade distribution
    public function statistics(): void
    {
        $this->getAuthService()->requireRole('student');

        require __DIR__ . '/../../public/student/statistics.php';
    }
}
