<?php

namespace App\Services;

use App\Constants\GradeConfig;
use App\Models\Grade;
use App\Repositories\GradeRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\CourseRepository;
use App\Services\Interfaces\GradeServiceInterface;

class GradeService implements GradeServiceInterface
{
    private GradeRepository $gradeRepository;
    private AssignmentRepository $assignmentRepository;
    private EnrollmentRepository $enrollmentRepository;
    private CourseRepository $courseRepository;

    public function __construct()
    {
        $this->gradeRepository = new GradeRepository();
        $this->assignmentRepository = new AssignmentRepository();
        $this->enrollmentRepository = new EnrollmentRepository();
        $this->courseRepository = new CourseRepository();
    }

    // Get all grades in the system
    public function findAll(): array
    {
        return $this->gradeRepository->findAll();
    }

    // Find a specific grade by its ID
    public function findById(int $id): ?Grade
    {
        return $this->gradeRepository->findById($id);
    }

    // Get all grades for a particular student
    public function findByStudentId(int $studentId): array
    {
        return $this->gradeRepository->findByStudentId($studentId);
    }

    // Get all grades for a specific assignment
    public function findByAssignmentId(int $assignmentId): array
    {
        return $this->gradeRepository->findByAssignmentId($assignmentId);
    }

    // Find a student's grade for a specific assignment
    public function findByStudentAndAssignment(int $studentId, int $assignmentId): ?Grade
    {
        return $this->gradeRepository->findByStudentAndAssignment($studentId, $assignmentId);
    }

    // Create a new grade with validation checks
    public function createGrade(array $gradeData): ?Grade
    {
        if (empty($gradeData['assignment_id']) || empty($gradeData['student_id']) || !isset($gradeData['points_earned'])) {
            return null;
        }

        $assignment = $this->assignmentRepository->findById($gradeData['assignment_id']);
        if (!$assignment) {
            return null;
        }

        if ($gradeData['points_earned'] < 0 || $gradeData['points_earned'] > $assignment->getMaxPoints()) {
            return null;
        }

        $enrollments = $this->enrollmentRepository->findByStudentId($gradeData['student_id']);
        $isEnrolled = false;
        foreach ($enrollments as $enrollment) {
            if ($enrollment->getCourseId() === $assignment->getCourseId()) {
                $isEnrolled = true;
                break;
            }
        }
        if (!$isEnrolled) {
            return null;
        }

        $grade = new Grade(
            $gradeData['assignment_id'],
            $gradeData['student_id'],
            (float) $gradeData['points_earned'],
            $gradeData['feedback'] ?? null
        );

        return $this->gradeRepository->create($grade);
    }

    // Update an existing grade with validation
    public function updateGrade(Grade $grade, array $updateData): bool
    {
        if (isset($updateData['points_earned'])) {
            $assignment = $this->assignmentRepository->findById($grade->getAssignmentId());
            if ($updateData['points_earned'] < 0 || $updateData['points_earned'] > $assignment->getMaxPoints()) {
                return false;
            }
        }

        $updatedGrade = new Grade(
            $updateData['assignment_id'] ?? $grade->getAssignmentId(),
            $updateData['student_id'] ?? $grade->getStudentId(),
            isset($updateData['points_earned']) ? (float) $updateData['points_earned'] : $grade->getPointsEarned(),
            $updateData['feedback'] ?? $grade->getFeedback(),
            $grade->getGradeId(),
            $grade->getGradedAt(),
            $grade->getUpdatedAt()
        );

        return $this->gradeRepository->update($updatedGrade);
    }

    // Remove a grade from the system
    public function deleteGrade(int $gradeId): bool
    {
        return $this->gradeRepository->delete($gradeId);
    }

    // Calculate the average percentage for a student in a specific course
    public function calculateCourseAverage(int $courseId, int $studentId): ?float
    {
        $gradeData = $this->gradeRepository->getGradeDataForCourseAndStudent($courseId, $studentId);

        if (empty($gradeData)) {
            return null;
        }

        $totalPercentage = 0;
        $count = 0;

        foreach ($gradeData as $grade) {
            if ($grade['max_points'] > 0) {
                $percentage = ($grade['points_earned'] / $grade['max_points']) * 100;
                $totalPercentage += $percentage;
                $count++;
            }
        }

        return $count > 0 ? $totalPercentage / $count : null;
    }

    // Calculate a student's overall GPA using credit-weighted averages
    public function calculateOverallGPA(int $studentId): float
    {
        $courses = $this->courseRepository->findByStudentId($studentId);

        if (empty($courses)) {
            return 0.0;
        }

        $totalPoints = 0.0;
        $totalCredits = 0.0;

        foreach ($courses as $course) {
            $courseAverage = $this->calculateCourseAverage($course->getCourseId(), $studentId);

            if ($courseAverage !== null) {
                $courseGPA = $this->percentageToGPA($courseAverage);
                $credits = $course->getCredits() ?? GradeConfig::DEFAULT_COURSE_CREDITS;

                $totalPoints += $courseGPA * $credits;
                $totalCredits += $credits;
            }
        }

        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;
    }

    // Convert a percentage score to a letter grade
    public function percentageToLetterGrade(float $percentage): string
    {
        if ($percentage >= GradeConfig::GRADE_A_THRESHOLD) return 'A';
        if ($percentage >= GradeConfig::GRADE_B_THRESHOLD) return 'B';
        if ($percentage >= GradeConfig::GRADE_C_THRESHOLD) return 'C';
        if ($percentage >= GradeConfig::GRADE_D_THRESHOLD) return 'D';
        return 'F';
    }

    // Convert a percentage score to GPA on a 4.0 scale
    public function percentageToGPA(float $percentage): float
    {
        if ($percentage >= GradeConfig::GRADE_A_THRESHOLD) return GradeConfig::GPA_A;
        if ($percentage >= GradeConfig::GRADE_B_THRESHOLD) return GradeConfig::GPA_B;
        if ($percentage >= GradeConfig::GRADE_C_THRESHOLD) return GradeConfig::GPA_C;
        if ($percentage >= GradeConfig::GRADE_D_THRESHOLD) return GradeConfig::GPA_D;
        return GradeConfig::GPA_F;
    }

    // Get comprehensive grade statistics for a student
    public function getStudentStatistics(int $studentId): array
    {
        $courses = $this->courseRepository->findByStudentId($studentId);
        $courseStats = [];
        $gradeDistribution = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
        $totalCredits = 0.0;

        foreach ($courses as $course) {
            $average = $this->calculateCourseAverage($course->getCourseId(), $studentId);

            if ($average !== null) {
                $letterGrade = $this->percentageToLetterGrade($average);
                $gpa = $this->percentageToGPA($average);
                $credits = $course->getCredits() ?? GradeConfig::DEFAULT_COURSE_CREDITS;

                $courseStats[] = [
                    'course' => $course,
                    'average' => round($average, 2),
                    'gpa' => $gpa,
                    'letter' => $letterGrade,
                    'credits' => $credits
                ];

                $gradeDistribution[$letterGrade]++;
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
