<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Controller;
use App\Models\Assignment;
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

        $formData = $this->assignmentPayload($courseId);
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateAssignmentData($formData);

            if (empty($errors) && $this->assignmentService->createAssignment($formData)) {
                $this->setFlash('success', 'Assignment created successfully!');
                $this->redirect('/teacher/course-detail/' . $courseId);
                return;
            }

            if (empty($errors)) {
                $this->setFlash('error', 'Failed to create assignment. Please check your input.');
            }
        }

        $this->renderCreateForm($course, $courseId, $errors, $formData);
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

        $formData = $this->assignmentPayload($courseId, false, $assignment);
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateAssignmentData($formData, false);

            if (empty($errors) && $this->assignmentService->updateAssignment($assignment, $formData)) {
                $this->setFlash('success', 'Assignment updated successfully!');
                $this->redirect('/teacher/course-detail/' . $courseId);
                return;
            }

            if (empty($errors)) {
                $this->setFlash('error', 'Failed to update assignment. Please check your input.');
            }
        }

        $this->renderEditForm($assignment, $courseId, $errors, $formData);
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

    private function renderCreateForm(\App\Models\Course $course, int $courseId, array $errors, array $formData): void
    {
        $this->render('teacher/assignment-create', [
            'pageTitle' => 'Create Assignment',
            'course' => $course,
            'courseId' => $courseId,
            'errors' => $errors,
            'formData' => $formData,
        ]);
    }

    private function renderEditForm(Assignment $assignment, int $courseId, array $errors, array $formData): void
    {
        $this->render('teacher/assignment-edit', [
            'pageTitle' => 'Edit Assignment',
            'assignment' => $assignment,
            'courseId' => $courseId,
            'errors' => $errors,
            'formData' => $formData,
        ]);
    }

    private function assignmentPayload(int $courseId, bool $includeCourseId = true, ?Assignment $assignment = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $assignment !== null) {
            return [
                'assignment_name' => $assignment->getAssignmentName(),
                'description' => $assignment->getDescription() ?? '',
                'max_points' => (string) $assignment->getMaxPoints(),
                'due_date' => $assignment->getDueDate() ?? '',
            ];
        }

        $payload = [
            'assignment_name' => trim((string) $this->request('assignment_name', '')),
            'description' => trim((string) $this->request('description', '')),
            'max_points' => trim((string) $this->request('max_points', '')),
            'due_date' => trim((string) $this->request('due_date', '')),
        ];

        if ($includeCourseId) {
            $payload['course_id'] = $courseId;
        }

        return $payload;
    }

    private function validateAssignmentData(array $assignmentData, bool $requireCourseId = true): array
    {
        $errors = [];

        if ($requireCourseId && empty($assignmentData['course_id'])) {
            $errors['course_id'] = 'Course is required.';
        }

        if (empty($assignmentData['assignment_name'])) {
            $errors['assignment_name'] = 'Assignment name is required.';
        }

        if (empty($assignmentData['description'])) {
            $errors['description'] = 'Description is required.';
        }

        if ($assignmentData['max_points'] === '') {
            $errors['max_points'] = 'Maximum points is required.';
        } elseif (!is_numeric($assignmentData['max_points']) || (float) $assignmentData['max_points'] <= 0) {
            $errors['max_points'] = 'Maximum points must be a number greater than 0.';
        }

        if (empty($assignmentData['due_date'])) {
            $errors['due_date'] = 'Due date is required.';
        } else {
            $date = \DateTime::createFromFormat('Y-m-d', $assignmentData['due_date']);
            $dateErrors = \DateTime::getLastErrors() ?: ['warning_count' => 0, 'error_count' => 0];

            if (
                $date === false
                || $dateErrors['warning_count'] > 0
                || $dateErrors['error_count'] > 0
            ) {
                $errors['due_date'] = 'Due date must be a valid date.';
            }
        }

        return $errors;
    }
}
