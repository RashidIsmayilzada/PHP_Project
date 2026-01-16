<?php

namespace App\Controllers;

use App\Services\GradeService;

class GradeController extends BaseController
{
    private GradeService $gradeService;

    public function __construct()
    {
        parent::__construct();
        $this->gradeService = new GradeService();
    }

    // Display all grades for students in a specific course
    public function showCourseGrades(int $courseId): void
    {
        $this->getAuthService()->requireRole('teacher');
        $_GET['course_id'] = $courseId;

        require __DIR__ . '/../../public/teacher/course-grades.php';
    }

    // Handle grade assignment form for a specific assignment
    public function gradeAction(int $assignmentId): void
    {
        $this->getAuthService()->requireRole('teacher');
        $_GET['assignment_id'] = $assignmentId;

        require __DIR__ . '/../../public/teacher/grade-assign.php';
    }

    // Handle grade editing form display and processing
    public function editAction(int $id): void
    {
        $this->getAuthService()->requireRole('teacher');
        $_GET['id'] = $id;

        require __DIR__ . '/../../public/teacher/grade-edit.php';
    }
}
