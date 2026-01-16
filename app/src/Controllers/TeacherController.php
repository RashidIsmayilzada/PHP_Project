<?php

namespace App\Controllers;

class TeacherController extends BaseController
{
    // Show teacher dashboard with courses they teach
    public function dashboard(): void
    {
        $this->getAuthService()->requireRole('teacher');

        require __DIR__ . '/../../public/teacher/dashboard.php';
    }
}
