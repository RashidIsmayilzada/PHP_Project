<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Controller;
use App\Services\Interfaces\CourseServiceInterface;

class CourseController extends Controller
{
    private CourseServiceInterface $courseService;

    public function __construct(CourseServiceInterface $courseService)
    {
        parent::__construct();
        $this->courseService = $courseService;
    }

    public function show(int $id): void
    {
        Auth::requireRole('teacher');
        $course = $this->courseService->findById($id);
        
        if (!$course || $course->getTeacherId() !== Auth::id()) {
            $this->redirect('/teacher/dashboard');
            return;
        }

        $this->render('teacher/course-detail', [
            'pageTitle' => 'Course Details',
            'course' => $course
        ]);
    }

    public function createAction(): void
    {
        Auth::requireRole('teacher');
        // Handle logic...
        $this->render('teacher/course-create', ['pageTitle' => 'Create Course']);
    }

    public function editAction(int $id): void
    {
        Auth::requireRole('teacher');
        // Handle logic...
        $this->render('teacher/course-edit', ['pageTitle' => 'Edit Course']);
    }

    public function delete(int $id): void
    {
        Auth::requireRole('teacher');
        // Handle logic...
        $this->redirect('/teacher/dashboard');
    }
}
