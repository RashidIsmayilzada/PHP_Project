<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Controller;
use App\Services\Interfaces\CourseServiceInterface;
use App\Services\Interfaces\EnrollmentServiceInterface;
use App\Services\Interfaces\AssignmentServiceInterface;

class TeacherController extends Controller
{
    private CourseServiceInterface $courseService;
    private EnrollmentServiceInterface $enrollmentService;
    private AssignmentServiceInterface $assignmentService;

    public function __construct(
        CourseServiceInterface $courseService,
        EnrollmentServiceInterface $enrollmentService,
        AssignmentServiceInterface $assignmentService
    ) {
        parent::__construct();
        $this->courseService = $courseService;
        $this->enrollmentService = $enrollmentService;
        $this->assignmentService = $assignmentService;
    }

    public function dashboard(): void
    {
        Auth::requireRole('teacher');
        $teacherId = Auth::id();

        $courses = $this->courseService->getCoursesForTeacher($teacherId);
        
        $courseData = [];
        foreach ($courses as $course) {
            $enrollments = $this->enrollmentService->findByCourseId($course->getCourseId());
            $assignments = $this->assignmentService->findByCourseId($course->getCourseId());

            $courseData[] = [
                'course' => $course,
                'enrollment_count' => count($enrollments),
                'assignment_count' => count($assignments)
            ];
        }

        $this->render('teacher/dashboard', [
            'pageTitle' => 'Teacher Dashboard',
            'courseData' => $courseData
        ]);
    }
}
