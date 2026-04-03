<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Controller;
use App\Services\Interfaces\EnrollmentServiceInterface;
use App\Services\Interfaces\CourseServiceInterface;
use App\Services\Interfaces\UserServiceInterface;

class EnrollmentController extends Controller
{
    private EnrollmentServiceInterface $enrollmentService;
    private CourseServiceInterface $courseService;
    private UserServiceInterface $userService;

    public function __construct(
        EnrollmentServiceInterface $enrollmentService,
        CourseServiceInterface $courseService,
        UserServiceInterface $userService
    ) {
        parent::__construct();
        $this->enrollmentService = $enrollmentService;
        $this->courseService = $courseService;
        $this->userService = $userService;
    }

    public function enrollAction(int $courseId): void
    {
        Auth::requireRole('teacher');

        $course = $this->findOwnedCourse($courseId);
        if ($course === null) {
            $this->setFlash('error', 'Course not found or access denied.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        if ($this->isPostRequest()) {
            $this->handleEnrollmentAction($courseId);
            $this->redirect("/teacher/course-enroll/$courseId");
            return;
        }

        $enrollments = $this->enrollmentService->findByCourseId($courseId);
        $allStudents = $this->userService->findAllStudents();
        $enrolledIds = array_map(fn($e) => $e->getStudentId(), $enrollments);
        $availableStudents = array_filter(
            $allStudents,
            fn($student) => !in_array($student->getUserId(), $enrolledIds, true)
        );

        $this->render('teacher/course-enroll', [
            'pageTitle' => 'Manage Enrollments',
            'course' => $course,
            'courseId' => $courseId,
            'enrollments' => $enrollments,
            'availableStudents' => $availableStudents
        ]);
    }

    private function findOwnedCourse(int $courseId): ?\App\Models\Course
    {
        $course = $this->courseService->findById($courseId);

        return $course && $course->getTeacherId() === Auth::id() ? $course : null;
    }

    private function handleEnrollmentAction(int $courseId): void
    {
        match ($this->request('action')) {
            'bulk_enroll' => $this->handleBulkEnrollment($courseId),
            'unenroll' => $this->handleUnenroll($courseId),
            default => $this->setFlash('error', 'Unknown enrollment action.'),
        };
    }

    private function handleBulkEnrollment(int $courseId): void
    {
        $studentIds = $_POST['student_ids'] ?? [];
        if (empty($studentIds)) {
            $this->setFlash('error', 'No students selected.');
            return;
        }

        $count = 0;
        foreach ($studentIds as $studentId) {
            if ($this->enrollmentService->enrollStudent((int)$studentId, $courseId)) {
                $count++;
            }
        }

        $message = $count > 0
            ? "Successfully enrolled $count student(s)."
            : 'Failed to enroll students or they are already enrolled.';

        $this->setFlash($count > 0 ? 'success' : 'error', $message);
    }

    private function handleUnenroll(int $courseId): void
    {
        $enrollmentId = (int)$this->request('enrollment_id');
        $enrollment = $this->enrollmentService->findById($enrollmentId);

        if ($enrollment === null || $enrollment->getCourseId() !== $courseId) {
            $this->setFlash('error', 'Enrollment not found.');
            return;
        }

        $success = $this->enrollmentService->deleteEnrollment($enrollmentId);
        $this->setFlash($success ? 'success' : 'error', $success ? 'Student unenrolled.' : 'Failed to unenroll student.');
    }

    private function isPostRequest(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}
