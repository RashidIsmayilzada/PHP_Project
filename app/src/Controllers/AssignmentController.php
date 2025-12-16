<?php

namespace App\Controllers;

use App\Services\AssignmentService;

class AssignmentController
{
    private AssignmentService $assignmentService;

    public function __construct()
    {
        $this->assignmentService = new AssignmentService();
    }

    public function index(): void
    {
        header('Content-Type: application/json');
        try {
            $assignments = $this->assignmentService->findAll();
            $assignmentsData = array_map(function($assignment) {
                return [
                    'assignment_id' => $assignment->getAssignmentId(),
                    'course_id' => $assignment->getCourseId(),
                    'assignment_name' => $assignment->getAssignmentName(),
                    'description' => $assignment->getDescription(),
                    'max_points' => $assignment->getMaxPoints(),
                    'due_date' => $assignment->getDueDate(),
                    'created_at' => $assignment->getCreatedAt(),
                    'updated_at' => $assignment->getUpdatedAt()
                ];
            }, $assignments);
            echo json_encode(['success' => true, 'data' => $assignmentsData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function show(int $id): void
    {
        header('Content-Type: application/json');
        try {
            $assignment = $this->assignmentService->findById($id);
            if ($assignment === null) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Assignment not found']);
                return;
            }

            $assignmentData = [
                'assignment_id' => $assignment->getAssignmentId(),
                'course_id' => $assignment->getCourseId(),
                'assignment_name' => $assignment->getAssignmentName(),
                'description' => $assignment->getDescription(),
                'max_points' => $assignment->getMaxPoints(),
                'due_date' => $assignment->getDueDate(),
                'created_at' => $assignment->getCreatedAt(),
                'updated_at' => $assignment->getUpdatedAt()
            ];
            echo json_encode(['success' => true, 'data' => $assignmentData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function byCourse(int $courseId): void
    {
        header('Content-Type: application/json');
        try {
            $assignments = $this->assignmentService->findByCourseId($courseId);
            $assignmentsData = array_map(function($assignment) {
                return [
                    'assignment_id' => $assignment->getAssignmentId(),
                    'course_id' => $assignment->getCourseId(),
                    'assignment_name' => $assignment->getAssignmentName(),
                    'description' => $assignment->getDescription(),
                    'max_points' => $assignment->getMaxPoints(),
                    'due_date' => $assignment->getDueDate(),
                    'created_at' => $assignment->getCreatedAt()
                ];
            }, $assignments);
            echo json_encode(['success' => true, 'data' => $assignmentsData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
