<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Grade;
use App\Repositories\Interfaces\GradeRepositoryInterface;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Services\Interfaces\GradeServiceInterface;
use App\Services\Interfaces\GradePolicyInterface;

class GradeService implements GradeServiceInterface
{
    private GradeRepositoryInterface $gradeRepository;
    private CourseRepositoryInterface $courseRepository;
    private GradePolicyInterface $gradePolicy;

    public function __construct(
        GradeRepositoryInterface $gradeRepository,
        CourseRepositoryInterface $courseRepository,
        GradePolicyInterface $gradePolicy
    ) {
        $this->gradeRepository = $gradeRepository;
        $this->courseRepository = $courseRepository;
        $this->gradePolicy = $gradePolicy;
    }

    public function findAll(): array { return $this->gradeRepository->findAll(); }
    public function findById(int $id): ?Grade { return $this->gradeRepository->findById($id); }
    public function findByStudentId(int $studentId): array { return $this->gradeRepository->findByStudentId($studentId); }
    public function findByAssignmentId(int $assignmentId): array { return $this->gradeRepository->findByAssignmentId($assignmentId); }
    public function findByStudentAndAssignment(int $studentId, int $assignmentId): ?Grade { return $this->gradeRepository->findByStudentAndAssignment($studentId, $assignmentId); }

    public function createGrade(array $gradeData): ?Grade
    {
        if (!$this->hasValidGradeData($gradeData)) {
            return null;
        }

        if ($this->findByStudentAndAssignment((int)$gradeData['student_id'], (int)$gradeData['assignment_id'])) {
            return null;
        }

        return $this->gradeRepository->create($this->buildGrade($gradeData));
    }

    public function updateGrade(Grade $grade, array $updateData): bool
    {
        $this->applyGradeUpdates($grade, $updateData);

        return $this->gradeRepository->update($grade);
    }

    public function deleteGrade(int $gradeId): bool { return $this->gradeRepository->delete($gradeId); }

    public function calculateCourseAverage(int $courseId, int $studentId): ?float
    {
        $gradeData = $this->gradeRepository->findGradeDataForCourseAndStudent($courseId, $studentId);
        if (empty($gradeData)) {
            return null;
        }

        [$totalPoints, $maxPoints] = $this->sumGradeData($gradeData);

        return $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : null;
    }

    public function calculateOverallGPA(int $studentId): float
    {
        $courses = $this->courseRepository->findByStudentId($studentId);
        if (empty($courses)) return 0.0;

        $totalPoints = 0.0;
        $totalCredits = 0.0;

        foreach ($courses as $course) {
            $courseSummary = $this->buildCoursePerformance($course->getCourseId(), $studentId, $course->getCredits());
            if ($courseSummary === null) {
                continue;
            }

            $totalPoints += $courseSummary['gpa'] * $courseSummary['credits'];
            $totalCredits += $courseSummary['credits'];
        }

        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;
    }

    public function percentageToLetterGrade(float $percentage): string
    {
        return $this->gradePolicy->letterForPercentage($percentage);
    }

    public function percentageToGPA(float $percentage): float
    {
        return $this->gradePolicy->gpaForPercentage($percentage);
    }

    public function getStudentStatistics(int $studentId): array
    {
        $courses = $this->courseRepository->findByStudentId($studentId);
        $courseStats = [];
        $gradeDistribution = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
        $totalCredits = 0.0;

        foreach ($courses as $course) {
            $courseSummary = $this->buildCoursePerformance($course->getCourseId(), $studentId, $course->getCredits());
            if ($courseSummary === null) {
                continue;
            }

            $courseStats[] = [
                'course' => $course,
                'average' => $courseSummary['average'],
                'letter' => $courseSummary['letter'],
                'gpa' => $courseSummary['gpa'],
                'credits' => $courseSummary['credits']
            ];
            $gradeDistribution[$courseSummary['letter']]++;
            $totalCredits += $courseSummary['credits'];
        }

        return [
            'overall_gpa' => $this->calculateOverallGPA($studentId),
            'total_credits' => $totalCredits,
            'courses' => $courseStats,
            'grade_distribution' => $gradeDistribution,
            'total_courses' => count($courseStats)
        ];
    }

    private function hasValidGradeData(array $gradeData): bool
    {
        return isset($gradeData['assignment_id'], $gradeData['student_id'], $gradeData['points_earned']);
    }

    private function buildGrade(array $gradeData): Grade
    {
        return new Grade(
            (int)$gradeData['assignment_id'],
            (int)$gradeData['student_id'],
            (float)$gradeData['points_earned'],
            $gradeData['feedback'] ?? null
        );
    }

    private function applyGradeUpdates(Grade $grade, array $updateData): void
    {
        if (array_key_exists('points_earned', $updateData)) {
            $grade->setPointsEarned((float)$updateData['points_earned']);
        }

        if (array_key_exists('feedback', $updateData)) {
            $grade->setFeedback($updateData['feedback']);
        }
    }

    private function sumGradeData(array $gradeData): array
    {
        $totalPoints = 0.0;
        $maxPoints = 0.0;

        foreach ($gradeData as $gradeRow) {
            $totalPoints += $gradeRow->getPointsEarned();
            $maxPoints += $gradeRow->getMaxPoints();
        }

        return [$totalPoints, $maxPoints];
    }

    private function buildCoursePerformance(int $courseId, int $studentId, ?float $credits): ?array
    {
        $average = $this->calculateCourseAverage($courseId, $studentId);
        if ($average === null) {
            return null;
        }

        $resolvedCredits = $credits ?? $this->gradePolicy->getDefaultCourseCredits();

        return [
            'average' => round($average, 2),
            'letter' => $this->percentageToLetterGrade($average),
            'gpa' => $this->percentageToGPA($average),
            'credits' => $resolvedCredits
        ];
    }
}
