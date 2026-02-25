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
            $this->setFlash('error', 'Course not found or access denied.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assignmentData = [
                'course_id' => $courseId,
                'assignment_name' => $this->request('assignment_name'),
                'description' => $this->request('description'),
                'max_points' => $this->request('max_points'),
                'due_date' => $this->request('due_date')
            ];

            if ($this->assignmentService->createAssignment($assignmentData)) {
                $this->setFlash('success', 'Assignment created successfully!');
                $this->redirect('/teacher/course-detail/' . $courseId);
                return;
            } else {
                $this->setFlash('error', 'Failed to create assignment. Please check your input.');
            }
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
        $assignment = $this->assignmentService->findById($id);

        if (!$assignment) {
            $this->setFlash('error', 'Assignment not found.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        $courseId = $assignment->getCourseId();
        $course = $this->courseService->findById($courseId);

        if (!$course || $course->getTeacherId() !== Auth::id()) {
            $this->setFlash('error', 'Access denied.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $updateData = [
                'assignment_name' => $this->request('assignment_name'),
                'description' => $this->request('description'),
                'max_points' => $this->request('max_points'),
                'due_date' => $this->request('due_date')
            ];

            if ($this->assignmentService->updateAssignment($assignment, $updateData)) {
                $this->setFlash('success', 'Assignment updated successfully!');
                $this->redirect('/teacher/course-detail/' . $courseId);
                return;
            } else {
                $this->setFlash('error', 'Failed to update assignment. Please check your input.');
            }
        }

        $this->render('teacher/assignment-edit', [
            'pageTitle' => 'Edit Assignment',
            'assignment' => $assignment,
            'courseId' => $courseId
        ]);
    }

    public function delete(int $id): void
    {
        Auth::requireRole('teacher');
        $assignment = $this->assignmentService->findById($id);

        if (!$assignment) {
            $this->setFlash('error', 'Assignment not found.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        $courseId = $assignment->getCourseId();
        $course = $this->courseService->findById($courseId);

        if (!$course || $course->getTeacherId() !== Auth::id()) {
            $this->setFlash('error', 'Access denied.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        if ($this->assignmentService->deleteAssignment($id)) {
            $this->setFlash('success', 'Assignment deleted successfully!');
        } else {
            $this->setFlash('error', 'Failed to delete assignment.');
        }

        $this->redirect('/teacher/course-detail/' . $courseId);
    }
}
