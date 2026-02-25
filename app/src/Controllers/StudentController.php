<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Controller;
use App\Services\Interfaces\CourseServiceInterface;
use App\Services\Interfaces\GradeServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use App\Services\Interfaces\EnrollmentServiceInterface;

class StudentController extends Controller
{
    private CourseServiceInterface $courseService;
    private GradeServiceInterface $gradeService;
    private UserServiceInterface $userService;
    private EnrollmentServiceInterface $enrollmentService;

    public function __construct(
        CourseServiceInterface $courseService,
        GradeServiceInterface $gradeService,
        UserServiceInterface $userService,
        EnrollmentServiceInterface $enrollmentService
    ) {
        parent::__construct();
        $this->courseService = $courseService;
        $this->gradeService = $gradeService;
        $this->userService = $userService;
        $this->enrollmentService = $enrollmentService;
    }

    public function dashboard(): void
    {
        Auth::requireRole('student');
        $studentId = Auth::id();

        $enrolledCourses = $this->courseService->getCoursesForStudent($studentId);
        $overallGPA = $this->gradeService->calculateOverallGPA($studentId);

        $coursesData = [];
        foreach ($enrolledCourses as $course) {
            $courseAverage = $this->gradeService->calculateCourseAverage($course->getCourseId(), $studentId);
            $letterGrade = $courseAverage !== null ? $this->gradeService->percentageToLetterGrade($courseAverage) : 'N/A';
            $teacher = $this->userService->findById($course->getTeacherId());
            
            $enrollments = $this->enrollmentService->findByStudentId($studentId);
            $status = 'unknown';
            foreach ($enrollments as $enrollment) {
                if ($enrollment->getCourseId() === $course->getCourseId()) {
                    $status = $enrollment->getStatus();
                    break;
                }
            }

            $coursesData[] = [
                'course' => $course,
                'average' => $courseAverage,
                'letter_grade' => $letterGrade,
                'teacher' => $teacher,
                'status' => $status
            ];
        }

        $this->render('student/dashboard', [
            'pageTitle' => 'Student Dashboard',
            'overallGPA' => $overallGPA,
            'coursesData' => $coursesData,
            'enrolledCourses' => $enrolledCourses
        ]);
    }

    public function courseDetail(int $id): void
    {
        Auth::requireRole('student');
        $studentId = Auth::id();

        $course = $this->courseService->findById($id);
        if (!$course) {
            $this->redirect('/student/dashboard');
            return;
        }

        // Logic to verify enrollment could go here...
        
        $this->render('student/course-detail', [
            'pageTitle' => $course->getCourseCode() . ' - Details',
            'course' => $course
        ]);
    }

    public function statistics(): void
    {
        Auth::requireRole('student');
        $statistics = $this->gradeService->getStudentStatistics(Auth::id());

        $this->render('student/statistics', [
            'pageTitle' => 'Academic Statistics',
            'statistics' => $statistics
        ]);
    }
}
