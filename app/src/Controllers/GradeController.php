<?php

namespace App\Controllers;

use App\Services\GradeService;

class GradeController
{
    private GradeService $gradeService;

    public function __construct()
    {
        $this->gradeService = new GradeService();
    }

    public function index(): void
    {
        header('Content-Type: application/json');
        try {
            $grades = $this->gradeService->findAll();
            $gradesData = array_map(function($grade) {
                return [
                    'grade_id' => $grade->getGradeId(),
                    'assignment_id' => $grade->getAssignmentId(),
                    'student_id' => $grade->getStudentId(),
                    'points_earned' => $grade->getPointsEarned(),
                    'feedback' => $grade->getFeedback(),
                    'graded_at' => $grade->getGradedAt(),
                    'updated_at' => $grade->getUpdatedAt()
                ];
            }, $grades);
            echo json_encode(['success' => true, 'data' => $gradesData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function show(int $id): void
    {
        header('Content-Type: application/json');
        try {
            $grade = $this->gradeService->findById($id);
            if ($grade === null) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Grade not found']);
                return;
            }

            $gradeData = [
                'grade_id' => $grade->getGradeId(),
                'assignment_id' => $grade->getAssignmentId(),
                'student_id' => $grade->getStudentId(),
                'points_earned' => $grade->getPointsEarned(),
                'feedback' => $grade->getFeedback(),
                'graded_at' => $grade->getGradedAt(),
                'updated_at' => $grade->getUpdatedAt()
            ];
            echo json_encode(['success' => true, 'data' => $gradeData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function byStudent(int $studentId): void
    {
        header('Content-Type: application/json');
        try {
            $grades = $this->gradeService->findByStudentId($studentId);
            $gradesData = array_map(function($grade) {
                return [
                    'grade_id' => $grade->getGradeId(),
                    'assignment_id' => $grade->getAssignmentId(),
                    'student_id' => $grade->getStudentId(),
                    'points_earned' => $grade->getPointsEarned(),
                    'feedback' => $grade->getFeedback(),
                    'graded_at' => $grade->getGradedAt()
                ];
            }, $grades);
            echo json_encode(['success' => true, 'data' => $gradesData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function byAssignment(int $assignmentId): void
    {
        header('Content-Type: application/json');
        try {
            $grades = $this->gradeService->findByAssignmentId($assignmentId);
            $gradesData = array_map(function($grade) {
                return [
                    'grade_id' => $grade->getGradeId(),
                    'assignment_id' => $grade->getAssignmentId(),
                    'student_id' => $grade->getStudentId(),
                    'points_earned' => $grade->getPointsEarned(),
                    'feedback' => $grade->getFeedback(),
                    'graded_at' => $grade->getGradedAt()
                ];
            }, $grades);
            echo json_encode(['success' => true, 'data' => $gradesData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function byStudentAndAssignment(int $studentId, int $assignmentId): void
    {
        header('Content-Type: application/json');
        try {
            $grade = $this->gradeService->findByStudentAndAssignment($studentId, $assignmentId);
            if ($grade === null) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Grade not found']);
                return;
            }

            $gradeData = [
                'grade_id' => $grade->getGradeId(),
                'assignment_id' => $grade->getAssignmentId(),
                'student_id' => $grade->getStudentId(),
                'points_earned' => $grade->getPointsEarned(),
                'feedback' => $grade->getFeedback(),
                'graded_at' => $grade->getGradedAt(),
                'updated_at' => $grade->getUpdatedAt()
            ];
            echo json_encode(['success' => true, 'data' => $gradeData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
