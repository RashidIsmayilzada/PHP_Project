<?php
declare(strict_types=1);

namespace App\Services;

use App\Constants\GradeConfig;
use App\Models\Grade;
use App\Repositories\Interfaces\GradeRepositoryInterface;
use App\Repositories\Interfaces\AssignmentRepositoryInterface;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Services\Interfaces\GradeServiceInterface;

class GradeService implements GradeServiceInterface
{
    private GradeRepositoryInterface $gradeRepository;
    private AssignmentRepositoryInterface $assignmentRepository;
    private EnrollmentRepositoryInterface $enrollmentRepository;
    private CourseRepositoryInterface $courseRepository;

    public function __construct(
        GradeRepositoryInterface $gradeRepository,
        AssignmentRepositoryInterface $assignmentRepository,
        EnrollmentRepositoryInterface $enrollmentRepository,
        CourseRepositoryInterface $courseRepository
    ) {
        $this->gradeRepository = $gradeRepository;
        $this->assignmentRepository = $assignmentRepository;
        $this->enrollmentRepository = $enrollmentRepository;
        $this->courseRepository = $courseRepository;
    }

    public function findAll(): array { return $this->gradeRepository->findAll(); }
    public function findById(int $id): ?Grade { return $this->gradeRepository->findById($id); }
    public function findByStudentId(int $studentId): array { return $this->gradeRepository->findByStudentId($studentId); }
    public function findByAssignmentId(int $assignmentId): array { return $this->gradeRepository->findByAssignmentId($assignmentId); }
    public function findByStudentAndAssignment(int $studentId, int $assignmentId): ?Grade { return $this->gradeRepository->findByStudentAndAssignment($studentId, $assignmentId); }

    public function createGrade(array $gradeData): ?Grade
    {
        // Validation logic...
        return $this->gradeRepository->create(new Grade(
            (int)$gradeData['assignment_id'],
            (int)$gradeData['student_id'],
            (float)$gradeData['points_earned'],
            $gradeData['feedback'] ?? null
        ));
    }

    public function updateGrade(Grade $grade, array $updateData): bool
    {
        $grade->setPointsEarned((float)($updateData['points_earned'] ?? $grade->getPointsEarned()));
        $grade->setFeedback($updateData['feedback'] ?? $grade->getFeedback());
        return $this->gradeRepository->update($grade);
    }

    public function deleteGrade(int $gradeId): bool { return $this->gradeRepository->delete($gradeId); }

    public function calculateCourseAverage(int $courseId, int $studentId): ?float
    {
        $gradeData = $this->gradeRepository->getGradeDataForCourseAndStudent($courseId, $studentId);
        if (empty($gradeData)) return null;

        $totalPoints = 0;
        $maxPoints = 0;
        foreach ($gradeData as $g) {
            $totalPoints += $g['points_earned'];
            $maxPoints += $g['max_points'];
        }

        return $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : null;
    }

    public function calculateOverallGPA(int $studentId): float
    {
        $courses = $this->courseRepository->findByStudentId($studentId);
        if (empty($courses)) return 0.0;

        $totalPoints = 0.0;
        $totalCredits = 0.0;

        foreach ($courses as $course) {
            $avg = $this->calculateCourseAverage($course->getCourseId(), $studentId);
            if ($avg !== null) {
                $gpa = $this->percentageToGPA($avg);
                $credits = $course->getCredits() ?? GradeConfig::DEFAULT_COURSE_CREDITS;
                $totalPoints += ($gpa * $credits);
                $totalCredits += $credits;
            }
        }

        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;
    }

    public function percentageToLetterGrade(float $percentage): string
    {
        if ($percentage >= GradeConfig::GRADE_A_THRESHOLD) return 'A';
        if ($percentage >= GradeConfig::GRADE_B_THRESHOLD) return 'B';
        if ($percentage >= GradeConfig::GRADE_C_THRESHOLD) return 'C';
        if ($percentage >= GradeConfig::GRADE_D_THRESHOLD) return 'D';
        return 'F';
    }

    public function percentageToGPA(float $percentage): float
    {
        if ($percentage >= GradeConfig::GRADE_A_THRESHOLD) return GradeConfig::GPA_A;
        if ($percentage >= GradeConfig::GRADE_B_THRESHOLD) return GradeConfig::GPA_B;
        if ($percentage >= GradeConfig::GRADE_C_THRESHOLD) return GradeConfig::GPA_C;
        if ($percentage >= GradeConfig::GRADE_D_THRESHOLD) return GradeConfig::GPA_D;
        return GradeConfig::GPA_F;
    }

    public function getStudentStatistics(int $studentId): array
    {
        $courses = $this->courseRepository->findByStudentId($studentId);
        $courseStats = [];
        $gradeDistribution = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
        $totalCredits = 0.0;

        foreach ($courses as $course) {
            $avg = $this->calculateCourseAverage($course->getCourseId(), $studentId);
            if ($avg !== null) {
                $letter = $this->percentageToLetterGrade($avg);
                $gpa = $this->percentageToGPA($avg);
                $credits = $course->getCredits() ?? GradeConfig::DEFAULT_COURSE_CREDITS;

                $courseStats[] = [
                    'course' => $course,
                    'average' => round($avg, 2),
                    'letter' => $letter,
                    'gpa' => $gpa,
                    'credits' => $credits
                ];
                $gradeDistribution[$letter]++;
                $totalCredits += $credits;
            }
        }

        return [
            'overall_gpa' => $this->calculateOverallGPA($studentId),
            'total_credits' => $totalCredits,
            'courses' => $courseStats,
            'grade_distribution' => $gradeDistribution,
            'total_courses' => count($courseStats)
        ];
    }
}
