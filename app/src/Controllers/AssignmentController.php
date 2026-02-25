<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Controller;
use App\Services\Interfaces\AssignmentServiceInterface;
use App\Services\Interfaces\CourseServiceInterface;

class AssignmentController extends Controller
{
    private AssignmentServiceInterface $assignmentService;
    private CourseServiceInterface $courseService;

    public function __construct(
        AssignmentServiceInterface $assignmentService,
        CourseServiceInterface $courseService
    ) {
        parent::__construct();
        $this->assignmentService = $assignmentService;
        $this->courseService = $courseService;
    }

    public function createAction(int $courseId): void
    {
        Auth::requireRole('teacher');
        $course = $this->courseService->findById($courseId);
        
        if (!$course || $course->getTeacherId() !== Auth::id()) {
            $this->redirect('/teacher/dashboard');
            return;
        }

        $this->render('teacher/assignment-create', [
            'pageTitle' => 'Create Assignment',
            'course' => $course,
            'courseId' => $courseId,
            'errors' => [],
            'formData' => []
        ]);
    }

    public function editAction(int $id): void
    {
        Auth::requireRole('teacher');
        // Logic...
        $this->render('teacher/assignment-edit', ['pageTitle' => 'Edit Assignment']);
    }

    public function delete(int $id): void
    {
        Auth::requireRole('teacher');
        // Logic...
        $this->redirect('/teacher/dashboard');
    }
}
