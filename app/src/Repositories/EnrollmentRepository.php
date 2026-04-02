<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Enums\EnrollmentStatus;
use App\Framework\Repository;
use App\Models\Enrollment;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;

class EnrollmentRepository extends Repository implements EnrollmentRepositoryInterface
{
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $rows = $this->fetchAll(
            "SELECT * FROM enrollments ORDER BY enrollment_date DESC, enrollment_id DESC LIMIT :limit OFFSET :offset",
            $this->paginationParams($limit, $offset)
        );

        return array_map([$this, 'mapRowToEnrollment'], $rows);
    }

    public function findById(int $id): ?Enrollment
    {
        $row = $this->fetch("SELECT * FROM enrollments WHERE enrollment_id = :id", ['id' => $id]);
        return $row ? $this->mapRowToEnrollment($row) : null;
    }

    public function findByStudentId(int $studentId, int $limit = 100, int $offset = 0): array
    {
        $rows = $this->fetchAll(
            "SELECT * FROM enrollments WHERE student_id = :student_id ORDER BY enrollment_date DESC, enrollment_id DESC LIMIT :limit OFFSET :offset",
            ['student_id' => $studentId] + $this->paginationParams($limit, $offset)
        );

        return array_map([$this, 'mapRowToEnrollment'], $rows);
    }

    public function findByCourseId(int $courseId, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT e.*, u.first_name, u.last_name, u.student_number 
                FROM enrollments e 
                JOIN users u ON e.student_id = u.user_id 
                WHERE e.course_id = :course_id 
                ORDER BY u.last_name, u.first_name
                LIMIT :limit OFFSET :offset";
        $rows = $this->fetchAll($sql, ['course_id' => $courseId] + $this->paginationParams($limit, $offset));

        return array_map([$this, 'mapRowToEnrollment'], $rows);
    }

    public function findByStudentIdAndStatus(
        int $studentId,
        EnrollmentStatus $status,
        int $limit = 100,
        int $offset = 0
    ): array
    {
        $rows = $this->fetchAll(
            "SELECT * FROM enrollments
             WHERE student_id = :student_id AND status = :status
             ORDER BY enrollment_date DESC, enrollment_id DESC
             LIMIT :limit OFFSET :offset",
            ['student_id' => $studentId, 'status' => $status->value] + $this->paginationParams($limit, $offset)
        );

        return array_map([$this, 'mapRowToEnrollment'], $rows);
    }

    public function findActiveEnrollmentsByStudentId(int $studentId, int $limit = 100, int $offset = 0): array
    {
        return $this->findByStudentIdAndStatus($studentId, EnrollmentStatus::ACTIVE, $limit, $offset);
    }

    public function create(Enrollment $enrollment): ?Enrollment
    {
        $sql = "INSERT INTO enrollments (student_id, course_id, status, enrollment_date)
                VALUES (:student_id, :course_id, :status, :enrollment_date)";
        
        $success = $this->execute($sql, [
            'student_id' => $enrollment->getStudentId(),
            'course_id' => $enrollment->getCourseId(),
            'status' => $enrollment->getStatus(),
            'enrollment_date' => $enrollment->getEnrollmentDate()
        ]);

        if (!$success) return null;

        return $this->findById($this->lastInsertId());
    }

    public function update(Enrollment $enrollment): bool
    {
        $sql = "UPDATE enrollments 
                SET student_id = :student_id,
                    course_id = :course_id,
                    status = :status,
                    enrollment_date = :enrollment_date
                WHERE enrollment_id = :enrollment_id";

        return $this->execute($sql, [
            'student_id' => $enrollment->getStudentId(),
            'course_id' => $enrollment->getCourseId(),
            'status' => $enrollment->getStatus(),
            'enrollment_date' => $enrollment->getEnrollmentDate(),
            'enrollment_id' => $enrollment->getEnrollmentId()
        ]);
    }

    public function delete(int $enrollmentId): bool
    {
        return $this->execute("DELETE FROM enrollments WHERE enrollment_id = :enrollment_id", ['enrollment_id' => $enrollmentId]);
    }

    private function mapRowToEnrollment(array $row): Enrollment
    {
        $enrollment = new Enrollment(
            (int)$row['student_id'],
            (int)$row['course_id'],
            $row['status'],
            (int)$row['enrollment_id'],
            $row['enrollment_date']
        );

        // Joined queries include denormalized student details for teacher-facing rosters.
        $enrollment->setStudentFirstName($row['first_name'] ?? null);
        $enrollment->setStudentLastName($row['last_name'] ?? null);
        $enrollment->setStudentNumber($row['student_number'] ?? null);

        return $enrollment;
    }

    private function paginationParams(int $limit, int $offset): array
    {
        return [
            'limit' => max(1, $limit),
            'offset' => max(0, $offset),
        ];
    }
}
