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
        
        $course = $this->courseService->findById($courseId);
        if (!$course || $course->getTeacherId() !== Auth::id()) {
            $this->setFlash('error', 'Course not found or access denied.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        // Handle POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->request('action');
            
            if ($action === 'bulk_enroll') {
                $studentIds = $_POST['student_ids'] ?? [];
                if (empty($studentIds)) {
                    $this->setFlash('error', 'No students selected.');
                } else {
                    $count = 0;
                    foreach ($studentIds as $sId) {
                        if ($this->enrollmentService->enrollStudent((int)$sId, $courseId)) {
                            $count++;
                        }
                    }
                    if ($count > 0) {
                        $this->setFlash('success', "Successfully enrolled $count student(s).");
                    } else {
                        $this->setFlash('error', "Failed to enroll students or they are already enrolled.");
                    }
                }
            } elseif ($action === 'unenroll') {
                $enrollmentId = (int)$this->request('enrollment_id');
                $enrollment = $this->enrollmentService->findById($enrollmentId);
                
                if ($enrollment && $enrollment->getCourseId() === $courseId) {
                    if ($this->enrollmentService->deleteEnrollment($enrollmentId)) {
                        $this->setFlash('success', "Student unenrolled.");
                    } else {
                        $this->setFlash('error', "Failed to unenroll student.");
                    }
                } else {
                    $this->setFlash('error', "Enrollment not found.");
                }
            }
            
            $this->redirect("/teacher/course-enroll/$courseId");
            return;
        }

        // Data for the view
        $enrollments = $this->enrollmentService->findByCourseId($courseId);
        $allStudents = $this->userService->findAllStudents();
        
        // Filter available students (those not already enrolled)
        $enrolledIds = array_map(fn($e) => $e->getStudentId(), $enrollments);
        $availableStudents = array_filter($allStudents, fn($s) => !in_array($s->getUserId(), $enrolledIds));

        $this->render('teacher/course-enroll', [
            'pageTitle' => 'Manage Enrollments',
            'course' => $course,
            'courseId' => $courseId,
            'enrollments' => $enrollments,
            'availableStudents' => $availableStudents
        ]);
    }
}
