<?php

namespace App\Controllers;

use App\Services\CourseService;

class CourseController
{
    private CourseService $courseService;

    public function __construct()
    {
        $this->courseService = new CourseService();
    }

    public function index(): void
    {
        header('Content-Type: application/json');
        try {
            $courses = $this->courseService->findAll();
            $coursesData = array_map(function($course) {
                return [
                    'course_id' => $course->getCourseId(),
                    'course_code' => $course->getCourseCode(),
                    'course_name' => $course->getCourseName(),
                    'description' => $course->getDescription(),
                    'teacher_id' => $course->getTeacherId(),
                    'credits' => $course->getCredits(),
                    'semester' => $course->getSemester(),
                    'created_at' => $course->getCreatedAt(),
                    'updated_at' => $course->getUpdatedAt()
                ];
            }, $courses);
            echo json_encode(['success' => true, 'data' => $coursesData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function show(int $id): void
    {
        header('Content-Type: application/json');
        try {
            $course = $this->courseService->findById($id);
            if ($course === null) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Course not found']);
                return;
            }

            $courseData = [
                'course_id' => $course->getCourseId(),
                'course_code' => $course->getCourseCode(),
                'course_name' => $course->getCourseName(),
                'description' => $course->getDescription(),
                'teacher_id' => $course->getTeacherId(),
                'credits' => $course->getCredits(),
                'semester' => $course->getSemester(),
                'created_at' => $course->getCreatedAt(),
                'updated_at' => $course->getUpdatedAt()
            ];
            echo json_encode(['success' => true, 'data' => $courseData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function byTeacher(int $teacherId): void
    {
        header('Content-Type: application/json');
        try {
            $courses = $this->courseService->findByTeacherId($teacherId);
            $coursesData = array_map(function($course) {
                return [
                    'course_id' => $course->getCourseId(),
                    'course_code' => $course->getCourseCode(),
                    'course_name' => $course->getCourseName(),
                    'description' => $course->getDescription(),
                    'teacher_id' => $course->getTeacherId(),
                    'credits' => $course->getCredits(),
                    'semester' => $course->getSemester(),
                    'created_at' => $course->getCreatedAt()
                ];
            }, $courses);
            echo json_encode(['success' => true, 'data' => $coursesData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function bySemester(string $semester): void
    {
        header('Content-Type: application/json');
        try {
            $courses = $this->courseService->findBySemester($semester);
            $coursesData = array_map(function($course) {
                return [
                    'course_id' => $course->getCourseId(),
                    'course_code' => $course->getCourseCode(),
                    'course_name' => $course->getCourseName(),
                    'description' => $course->getDescription(),
                    'teacher_id' => $course->getTeacherId(),
                    'credits' => $course->getCredits(),
                    'semester' => $course->getSemester(),
                    'created_at' => $course->getCreatedAt()
                ];
            }, $courses);
            echo json_encode(['success' => true, 'data' => $coursesData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
