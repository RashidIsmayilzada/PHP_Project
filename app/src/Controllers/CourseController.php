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
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $courseData = [
                'course_code' => $this->request('course_code'),
                'course_name' => $this->request('course_name'),
                'description' => $this->request('description'),
                'credits' => $this->request('credits'),
                'semester' => $this->request('semester'),
                'teacher_id' => Auth::id()
            ];

            $course = $this->courseService->createCourse($courseData);
            if ($course) {
                $this->setFlash('success', 'Course created successfully!');
                $this->redirect('/teacher/dashboard');
                return;
            } else {
                $this->setFlash('error', 'Failed to create course. Please check your input.');
            }
        }

        $this->render('teacher/course-create', ['pageTitle' => 'Create Course']);
    }

    public function editAction(int $id): void
    {
        Auth::requireRole('teacher');
        $course = $this->courseService->findById($id);

        if (!$course || $course->getTeacherId() !== Auth::id()) {
            $this->setFlash('error', 'Course not found or access denied.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $updateData = [
                'course_code' => $this->request('course_code'),
                'course_name' => $this->request('course_name'),
                'description' => $this->request('description'),
                'credits' => $this->request('credits'),
                'semester' => $this->request('semester')
            ];

            if ($this->courseService->updateCourse($course, $updateData)) {
                $this->setFlash('success', 'Course updated successfully!');
                $this->redirect('/teacher/course-detail/' . $id);
                return;
            } else {
                $this->setFlash('error', 'Failed to update course. Please check your input.');
            }
        }

        $this->render('teacher/course-edit', [
            'pageTitle' => 'Edit Course',
            'course' => $course
        ]);
    }

    public function delete(int $id): void
    {
        Auth::requireRole('teacher');
        $course = $this->courseService->findById($id);

        if (!$course || $course->getTeacherId() !== Auth::id()) {
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
}
