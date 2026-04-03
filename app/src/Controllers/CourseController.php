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

    public function index(): void
    {
        Auth::requireRole('teacher');

        $courses = $this->courseService->findAll();
        $payload = array_map(fn($course) => [
            'id' => $course->getCourseId(),
            'course_code' => $course->getCourseCode(),
            'course_name' => $course->getCourseName(),
            'teacher_id' => $course->getTeacherId(),
            'credits' => $course->getCredits(),
            'semester' => $course->getSemester(),
        ], $courses);

        header('Content-Type: application/json');
        echo json_encode($payload);
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

        if ($this->isPostRequest() && $this->handleCreate()) {
            return;
        }

        $this->render('teacher/course-create', ['pageTitle' => 'Create Course']);
    }

    public function editAction(int $id): void
    {
        Auth::requireRole('teacher');
        $course = $this->findOwnedCourse($id);

        if ($course === null) {
            $this->setFlash('error', 'Course not found or access denied.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        if ($this->isPostRequest() && $this->handleUpdate($course, $id)) {
            return;
        }

        $this->render('teacher/course-edit', [
            'pageTitle' => 'Edit Course',
            'course' => $course
        ]);
    }

    public function delete(int $id): void
    {
        Auth::requireRole('teacher');
        $course = $this->findOwnedCourse($id);

        if ($course === null) {
            $this->setFlash('error', 'Course not found or access denied.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        if ($this->courseService->deleteCourse($id)) {
            $this->setFlash('success', 'Course deleted successfully!');
        } else {
            $this->setFlash('error', 'Failed to delete course.');
        }

        $this->redirect('/teacher/dashboard');
    }

    private function findOwnedCourse(int $courseId): ?\App\Models\Course
    {
        $course = $this->courseService->findById($courseId);

        return $course && $course->getTeacherId() === Auth::id() ? $course : null;
    }

    private function handleCreate(): bool
    {
        $course = $this->courseService->createCourse($this->coursePayload());
        if ($course === null) {
            $this->setFlash('error', 'Failed to create course. Please check your input.');
            return false;
        }

        $this->setFlash('success', 'Course created successfully!');
        $this->redirect('/teacher/dashboard');
        return true;
    }

    private function handleUpdate(\App\Models\Course $course, int $courseId): bool
    {
        if (!$this->courseService->updateCourse($course, $this->coursePayload(false))) {
            $this->setFlash('error', 'Failed to update course. Please check your input.');
            return false;
        }

        $this->setFlash('success', 'Course updated successfully!');
        $this->redirect('/teacher/course-detail/' . $courseId);
        return true;
    }

    private function coursePayload(bool $includeTeacher = true): array
    {
        $payload = [
            'course_code' => $this->request('course_code'),
            'course_name' => $this->request('course_name'),
            'description' => $this->request('description'),
            'credits' => $this->request('credits'),
            'semester' => $this->request('semester'),
        ];

        if ($includeTeacher) {
            $payload['teacher_id'] = Auth::id();
        }

        return $payload;
    }

    private function isPostRequest(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}
