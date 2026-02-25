<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\Enrollment;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;

class EnrollmentRepository extends Repository implements EnrollmentRepositoryInterface
{
    public function findAll(): array
    {
        $rows = $this->fetchAll("SELECT * FROM enrollments");
        return array_map([$this, 'mapRowToEnrollment'], $rows);
    }

    public function findById(int $id): ?Enrollment
    {
        $row = $this->fetch("SELECT * FROM enrollments WHERE enrollment_id = :id", ['id' => $id]);
        return $row ? $this->mapRowToEnrollment($row) : null;
    }

    public function findByStudentId(int $studentId): array
    {
        $rows = $this->fetchAll("SELECT * FROM enrollments WHERE student_id = :student_id", ['student_id' => $studentId]);
        return array_map([$this, 'mapRowToEnrollment'], $rows);
    }

    public function findByCourseId(int $courseId): array
    {
        $rows = $this->fetchAll("SELECT * FROM enrollments WHERE course_id = :course_id", ['course_id' => $courseId]);
        return array_map([$this, 'mapRowToEnrollment'], $rows);
    }

    public function findActiveEnrollmentsByStudentId(int $studentId): array
    {
        $rows = $this->fetchAll("SELECT * FROM enrollments WHERE student_id = :student_id AND status = 'active'", ['student_id' => $studentId]);
        return array_map([$this, 'mapRowToEnrollment'], $rows);
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
        return new Enrollment(
            (int)$row['student_id'],
            (int)$row['course_id'],
            $row['status'],
            (int)$row['enrollment_id'],
            $row['enrollment_date']
        );
    }
}
