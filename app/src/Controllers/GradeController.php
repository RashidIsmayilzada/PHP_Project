<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Controller;
use App\Services\Interfaces\AssignmentServiceInterface;
use App\Services\Interfaces\CourseServiceInterface;
use App\Services\Interfaces\EnrollmentServiceInterface;
use App\Services\Interfaces\GradeServiceInterface;

class GradeController extends Controller
{
    private CourseServiceInterface $courseService;
    private AssignmentServiceInterface $assignmentService;
    private EnrollmentServiceInterface $enrollmentService;
    private GradeServiceInterface $gradeService;

    public function __construct(
        CourseServiceInterface $courseService,
        AssignmentServiceInterface $assignmentService,
        EnrollmentServiceInterface $enrollmentService,
        GradeServiceInterface $gradeService
    )
    {
        parent::__construct();
        $this->courseService = $courseService;
        $this->assignmentService = $assignmentService;
        $this->enrollmentService = $enrollmentService;
        $this->gradeService = $gradeService;
    }

    public function showCourseGrades(int $courseId): void
    {
        Auth::requireRole('teacher');
        $course = $this->findOwnedCourse($courseId);
        if ($course === null) {
            $this->setFlash('error', 'Course not found or access denied.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        $enrollments = $this->enrollmentService->findByCourseId($courseId);
        $assignments = $this->assignmentService->findByCourseId($courseId);
        $gradeRows = array_map(fn($enrollment) => [
            'enrollment' => $enrollment,
            'average' => $this->gradeService->calculateCourseAverage($courseId, $enrollment->getStudentId()),
            'letter' => $this->resolveLetterGrade($courseId, $enrollment->getStudentId()),
        ], $enrollments);
        $assignmentRows = array_map(fn($assignment) => [
            'assignment' => $assignment,
            'graded_count' => count($this->gradeService->findByAssignmentId($assignment->getAssignmentId())),
        ], $assignments);

        $this->render('teacher/course-grades', [
            'pageTitle' => 'Course Grades',
            'course' => $course,
            'courseId' => $courseId,
            'gradeRows' => $gradeRows,
            'assignmentRows' => $assignmentRows,
        ]);
    }

    public function gradeAction(int $assignmentId): void
    {
        Auth::requireRole('teacher');
        $assignment = $this->assignmentService->findById($assignmentId);
        if ($assignment === null) {
            $this->setFlash('error', 'Assignment not found.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        $course = $this->findOwnedCourse($assignment->getCourseId());
        if ($course === null) {
            $this->setFlash('error', 'Access denied.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        if ($this->isPostRequest()) {
            $this->saveGrades($assignmentId);
            $this->redirect('/teacher/grade-assign/' . $assignmentId);
            return;
        }

        $enrollments = $this->enrollmentService->findByCourseId($course->getCourseId());
        $gradeRows = array_map(fn($enrollment) => [
            'enrollment' => $enrollment,
            'grade' => $this->gradeService->findByStudentAndAssignment($enrollment->getStudentId(), $assignmentId),
        ], $enrollments);

        $this->render('teacher/grade-assign', [
            'pageTitle' => 'Assign Grades',
            'course' => $course,
            'assignment' => $assignment,
            'gradeRows' => $gradeRows,
        ]);
    }

    public function editAction(int $id): void
    {
        Auth::requireRole('teacher');
        $grade = $this->gradeService->findById($id);
        if ($grade === null) {
            $this->setFlash('error', 'Grade not found.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        $assignment = $this->assignmentService->findById($grade->getAssignmentId());
        $course = $assignment ? $this->findOwnedCourse($assignment->getCourseId()) : null;
        if ($assignment === null || $course === null) {
            $this->setFlash('error', 'Access denied.');
            $this->redirect('/teacher/dashboard');
            return;
        }

        if ($this->isPostRequest()) {
            $pointsEarned = $this->request('points_earned');
            $validationError = $this->validatePointsEarned($pointsEarned, $assignment->getMaxPoints());

            if ($validationError !== null) {
                $this->setFlash('error', $validationError);
                $this->redirect('/teacher/grade-edit/' . $id);
                return;
            }

            $updated = $this->gradeService->updateGrade($grade, [
                'points_earned' => $pointsEarned,
                'feedback' => $this->request('feedback'),
            ]);

            $this->setFlash($updated ? 'success' : 'error', $updated ? 'Grade updated successfully!' : 'Failed to update grade.');
            $this->redirect('/teacher/grade-edit/' . $id);
            return;
        }

        $this->render('teacher/grade-edit', [
            'pageTitle' => 'Edit Grade',
            'grade' => $grade,
            'assignment' => $assignment,
            'course' => $course,
        ]);
    }

    private function findOwnedCourse(int $courseId): ?\App\Models\Course
    {
        $course = $this->courseService->findById($courseId);

        return $course && $course->getTeacherId() === Auth::id() ? $course : null;
    }

    private function resolveLetterGrade(int $courseId, int $studentId): string
    {
        $average = $this->gradeService->calculateCourseAverage($courseId, $studentId);

        return $average === null ? 'N/A' : $this->gradeService->percentageToLetterGrade($average);
    }

    private function saveGrades(int $assignmentId): void
    {
        $gradeInputs = $_POST['grades'] ?? [];
        $savedCount = 0;

        foreach ($gradeInputs as $studentId => $gradeInput) {
            if ($this->saveSingleGrade((int)$studentId, $assignmentId, $gradeInput)) {
                $savedCount++;
            }
        }

        $message = $savedCount > 0 ? 'Grades saved successfully!' : 'No grades were saved.';
        $this->setFlash($savedCount > 0 ? 'success' : 'error', $message);
    }

    private function saveSingleGrade(int $studentId, int $assignmentId, array $gradeInput): bool
    {
        if (($gradeInput['points_earned'] ?? '') === '') {
            return false;
        }

        $assignment = $this->assignmentService->findById($assignmentId);
        if ($assignment === null) {
            return false;
        }

        $validationError = $this->validatePointsEarned($gradeInput['points_earned'], $assignment->getMaxPoints());
        if ($validationError !== null) {
            $this->setFlash('error', "Student ID {$studentId}: {$validationError}");
            return false;
        }

        $payload = [
            'points_earned' => $gradeInput['points_earned'],
            'feedback' => $gradeInput['feedback'] ?? null,
        ];

        $grade = $this->gradeService->findByStudentAndAssignment($studentId, $assignmentId);
        if ($grade !== null) {
            return $this->gradeService->updateGrade($grade, $payload);
        }

        return $this->gradeService->createGrade($payload + [
            'student_id' => $studentId,
            'assignment_id' => $assignmentId,
        ]) !== null;
    }

    private function isPostRequest(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    private function validatePointsEarned(mixed $pointsEarned, float $maxPoints): ?string
    {
        if ($pointsEarned === null || $pointsEarned === '') {
            return 'Points earned is required.';
        }

        if (!is_numeric($pointsEarned)) {
            return 'Points earned must be a valid number.';
        }

        $numericPoints = (float) $pointsEarned;
        if ($numericPoints < 0) {
            return 'Points earned cannot be negative.';
        }

        if ($numericPoints > $maxPoints) {
            return 'Points earned cannot be greater than the assignment maximum of ' . rtrim(rtrim((string) $maxPoints, '0'), '.') . '.';
        }

        return null;
    }
}
