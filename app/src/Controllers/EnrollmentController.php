<?php

namespace App\Controllers;

use App\Services\EnrollmentService;

class EnrollmentController
{
    private EnrollmentService $enrollmentService;

    public function __construct()
    {
        $this->enrollmentService = new EnrollmentService();
    }

    public function index(): void
    {
        header('Content-Type: application/json');
        try {
            $enrollments = $this->enrollmentService->findAll();
            $enrollmentsData = array_map(function($enrollment) {
                return [
                    'enrollment_id' => $enrollment->getEnrollmentId(),
                    'student_id' => $enrollment->getStudentId(),
                    'course_id' => $enrollment->getCourseId(),
                    'status' => $enrollment->getStatus(),
                    'enrollment_date' => $enrollment->getEnrollmentDate()
                ];
            }, $enrollments);
            echo json_encode(['success' => true, 'data' => $enrollmentsData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function show(int $id): void
    {
        header('Content-Type: application/json');
        try {
            $enrollment = $this->enrollmentService->findById($id);
            if ($enrollment === null) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Enrollment not found']);
                return;
            }

            $enrollmentData = [
                'enrollment_id' => $enrollment->getEnrollmentId(),
                'student_id' => $enrollment->getStudentId(),
                'course_id' => $enrollment->getCourseId(),
                'status' => $enrollment->getStatus(),
                'enrollment_date' => $enrollment->getEnrollmentDate()
            ];
            echo json_encode(['success' => true, 'data' => $enrollmentData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function byStudent(int $studentId): void
    {
        header('Content-Type: application/json');
        try {
            $enrollments = $this->enrollmentService->findByStudentId($studentId);
            $enrollmentsData = array_map(function($enrollment) {
                return [
                    'enrollment_id' => $enrollment->getEnrollmentId(),
                    'student_id' => $enrollment->getStudentId(),
                    'course_id' => $enrollment->getCourseId(),
                    'status' => $enrollment->getStatus(),
                    'enrollment_date' => $enrollment->getEnrollmentDate()
                ];
            }, $enrollments);
            echo json_encode(['success' => true, 'data' => $enrollmentsData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function byCourse(int $courseId): void
    {
        header('Content-Type: application/json');
        try {
            $enrollments = $this->enrollmentService->findByCourseId($courseId);
            $enrollmentsData = array_map(function($enrollment) {
                return [
                    'enrollment_id' => $enrollment->getEnrollmentId(),
                    'student_id' => $enrollment->getStudentId(),
                    'course_id' => $enrollment->getCourseId(),
                    'status' => $enrollment->getStatus(),
                    'enrollment_date' => $enrollment->getEnrollmentDate()
                ];
            }, $enrollments);
            echo json_encode(['success' => true, 'data' => $enrollmentsData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function activeByStudent(int $studentId): void
    {
        header('Content-Type: application/json');
        try {
            $enrollments = $this->enrollmentService->findActiveEnrollmentsByStudentId($studentId);
            $enrollmentsData = array_map(function($enrollment) {
                return [
                    'enrollment_id' => $enrollment->getEnrollmentId(),
                    'student_id' => $enrollment->getStudentId(),
                    'course_id' => $enrollment->getCourseId(),
                    'status' => $enrollment->getStatus(),
                    'enrollment_date' => $enrollment->getEnrollmentDate()
                ];
            }, $enrollments);
            echo json_encode(['success' => true, 'data' => $enrollmentsData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
