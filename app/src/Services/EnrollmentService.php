<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Repositories\EnrollmentRepository;
use App\Repositories\UserRepository;
use App\Repositories\CourseRepository;
use App\Services\Interfaces\EnrollmentServiceInterface;

class EnrollmentService implements EnrollmentServiceInterface
{

    private EnrollmentRepository $enrollmentRepository;
    private UserRepository $userRepository;
    private CourseRepository $courseRepository;

    public function __construct()
    {
        $this->enrollmentRepository = new EnrollmentRepository();
        $this->userRepository = new UserRepository();
        $this->courseRepository = new CourseRepository();
    }

    // Grab every enrollment record from the database
    public function findAll(): array
    {
        return $this->enrollmentRepository->findAll();
    }

    // Look up a specific enrollment by its ID
    public function findById(int $id): ?Enrollment
    {
        return $this->enrollmentRepository->findById($id);
    }

    // Get all courses a student has enrolled in
    public function findByStudentId(int $studentId): array
    {
        return $this->enrollmentRepository->findByStudentId($studentId);
    }

    // Find all students enrolled in a particular course
    public function findByCourseId(int $courseId): array
    {
        return $this->enrollmentRepository->findByCourseId($courseId);
    }

    // Pull only the active enrollments for a student
    public function findActiveEnrollmentsByStudentId(int $studentId): array
    {
        return $this->enrollmentRepository->findActiveEnrollmentsByStudentId($studentId);
    }

    // Sign up a student for a course after making sure they're eligible
    public function enrollStudent(int $studentId, int $courseId): ?Enrollment
    {
        // Validate student exists and is a student
        $student = $this->userRepository->findById($studentId);
        if (!$student || $student->getRole() !== 'student') {
            return null;
        }

        // Validate course exists
        $course = $this->courseRepository->findById($courseId);
        if (!$course) {
            return null;
        }

        // Check if student is already enrolled in this course
        $existingEnrollments = $this->enrollmentRepository->findByStudentId($studentId);
        foreach ($existingEnrollments as $enrollment) {
            if ($enrollment->getCourseId() === $courseId) {
                // Student is already enrolled
                return null;
            }
        }

        // Create new enrollment with 'active' status
        $enrollment = new Enrollment(
            $studentId,
            $courseId,
            'active'
        );

        return $this->enrollmentRepository->create($enrollment);
    }

    // Change the status of an enrollment like marking it completed or dropped
    public function updateEnrollmentStatus(int $enrollmentId, string $status): bool
    {
        // Validate status is a valid enum value
        $validStatuses = ['active', 'inactive', 'completed', 'dropped'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        // Get existing enrollment
        $enrollment = $this->enrollmentRepository->findById($enrollmentId);
        if (!$enrollment) {
            return false;
        }

        // Create updated enrollment object
        $updatedEnrollment = new Enrollment(
            $enrollment->getStudentId(),
            $enrollment->getCourseId(),
            $status,
            $enrollment->getEnrollmentId(),
            $enrollment->getEnrollmentDate()
        );

        return $this->enrollmentRepository->update($updatedEnrollment);
    }

    // Let a student drop a course they're enrolled in
    public function dropCourse(int $enrollmentId): bool
    {
        return $this->updateEnrollmentStatus($enrollmentId, 'dropped');
    }

    // Remove an enrollment record completely from the database
    public function deleteEnrollment(int $enrollmentId): bool
    {
        return $this->enrollmentRepository->delete($enrollmentId);
    }
}
