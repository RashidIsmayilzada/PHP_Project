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
        $statuses = $this->mapEnrollmentStatuses($studentId);
        $summary = $this->buildDashboardSummary($enrolledCourses, $statuses);

        $coursesData = [];
        foreach ($enrolledCourses as $course) {
            $courseAverage = $this->gradeService->calculateCourseAverage($course->getCourseId(), $studentId);
            $letterGrade = $courseAverage !== null ? $this->gradeService->percentageToLetterGrade($courseAverage) : 'N/A';
            $teacher = $this->userService->findById($course->getTeacherId());
            $status = $statuses[$course->getCourseId()] ?? 'unknown';

            $coursesData[] = [
                'course' => $course,
                'average' => $courseAverage,
                'average_display' => $courseAverage !== null ? number_format($courseAverage, 1) . '%' : 'N/A',
                'letter_grade' => $letterGrade ?: '-',
                'teacher' => $teacher,
                'status' => $status,
                'status_badge_class' => $status === 'active' ? 'success' : 'secondary',
                'grade_color' => $this->resolveGradeColor($courseAverage),
            ];
        }

        $this->render('student/dashboard', [
            'pageTitle' => 'Student Dashboard',
            'overallGPA' => $overallGPA,
            'dashboardSummary' => $summary,
            'coursesData' => $coursesData,
            'enrolledCourses' => $enrolledCourses
        ]);
    }

    public function courseDetail(int $id): void
    {
        Auth::requireRole('student');
        $studentId = Auth::id();

        $course = $this->courseService->findById($id);
        if (!$course || !$this->isEnrolledInCourse($studentId, $id)) {
            $this->redirect('/student/dashboard');
            return;
        }

        $this->render('student/course-detail', [
            'pageTitle' => $course->getCourseCode() . ' - Details',
            'course' => $course
        ]);
    }

    public function statistics(): void
    {
        Auth::requireRole('student');
        $statistics = $this->buildStatisticsViewData(
            $this->gradeService->getStudentStatistics(Auth::id())
        );

        $this->render('student/statistics', [
            'pageTitle' => 'Academic Statistics',
            'statistics' => $statistics
        ]);
    }

    private function mapEnrollmentStatuses(int $studentId): array
    {
        $statuses = [];

        foreach ($this->enrollmentService->findByStudentId($studentId) as $enrollment) {
            $statuses[$enrollment->getCourseId()] = $enrollment->getStatus();
        }

        return $statuses;
    }

    private function isEnrolledInCourse(int $studentId, int $courseId): bool
    {
        foreach ($this->enrollmentService->findByStudentId($studentId) as $enrollment) {
            if ($enrollment->getCourseId() === $courseId) {
                return true;
            }
        }

        return false;
    }

    private function buildDashboardSummary(array $courses, array $statuses): array
    {
        $activeCount = 0;
        $totalCredits = 0.0;

        foreach ($courses as $course) {
            if (($statuses[$course->getCourseId()] ?? null) === 'active') {
                $activeCount++;
            }

            $totalCredits += $course->getCredits() ?? 0.0;
        }

        return [
            'course_count' => count($courses),
            'active_count' => $activeCount,
            'total_credits' => $totalCredits,
        ];
    }

    private function buildStatisticsViewData(array $statistics): array
    {
        $statistics['courses'] = array_map(function (array $courseStat): array {
            $courseStat['average_display'] = number_format($courseStat['average'], 1) . '%';
            $courseStat['gpa_display'] = number_format($courseStat['gpa'], 1);
            $courseStat['grade_color'] = $this->resolveGradeColor($courseStat['average']);

            return $courseStat;
        }, $statistics['courses']);

        return $statistics;
    }

    private function resolveGradeColor(?float $average): string
    {
        if ($average === null) {
            return 'secondary';
        }

        if ($average >= 90) {
            return 'success';
        }

        if ($average >= 70) {
            return 'primary';
        }

        return $average >= 50 ? 'warning' : 'danger';
    }
}
