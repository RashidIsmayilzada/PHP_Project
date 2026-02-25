<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Controller;
use App\Services\Interfaces\GradeServiceInterface;

class GradeController extends Controller
{
    private GradeServiceInterface $gradeService;

    public function __construct(GradeServiceInterface $gradeService)
    {
        parent::__construct();
        $this->gradeService = $gradeService;
    }

    public function showCourseGrades(int $courseId): void
    {
        Auth::requireRole('teacher');
        $this->render('teacher/course-grades', ['pageTitle' => 'Course Grades']);
    }

    public function gradeAction(int $assignmentId): void
    {
        Auth::requireRole('teacher');
        $this->render('teacher/grade-assign', ['pageTitle' => 'Assign Grades']);
    }

    public function editAction(int $id): void
    {
        Auth::requireRole('teacher');
        $this->render('teacher/grade-edit', ['pageTitle' => 'Edit Grade']);
    }
}
