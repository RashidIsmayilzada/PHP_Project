<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\Course;
use App\Repositories\Interfaces\CourseRepositoryInterface;

class CourseRepository extends Repository implements CourseRepositoryInterface
{
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $rows = $this->fetchAll(
            "SELECT * FROM courses ORDER BY course_name LIMIT :limit OFFSET :offset",
            $this->paginationParams($limit, $offset)
        );

        return array_map([$this, 'mapRowToCourse'], $rows);
    }

    public function findById(int $id): ?Course
    {
        $row = $this->fetch("SELECT * FROM courses WHERE course_id = :id", ['id' => $id]);
        return $row ? $this->mapRowToCourse($row) : null;
    }

    public function findByTeacherId(int $teacherId, int $limit = 100, int $offset = 0): array
    {
        $rows = $this->fetchAll(
            "SELECT * FROM courses WHERE teacher_id = :teacher_id ORDER BY course_name LIMIT :limit OFFSET :offset",
            ['teacher_id' => $teacherId] + $this->paginationParams($limit, $offset)
        );

        return array_map([$this, 'mapRowToCourse'], $rows);
    }

    public function findBySemester(string $semester, int $limit = 100, int $offset = 0): array
    {
        $rows = $this->fetchAll(
            "SELECT * FROM courses WHERE semester = :semester ORDER BY course_name LIMIT :limit OFFSET :offset",
            ['semester' => $semester] + $this->paginationParams($limit, $offset)
        );

        return array_map([$this, 'mapRowToCourse'], $rows);
    }

    public function findByStudentId(int $studentId, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT c.* FROM courses c 
                JOIN enrollments e ON c.course_id = e.course_id 
                WHERE e.student_id = :student_id 
                ORDER BY c.course_name
                LIMIT :limit OFFSET :offset";
        $rows = $this->fetchAll($sql, ['student_id' => $studentId] + $this->paginationParams($limit, $offset));

        return array_map([$this, 'mapRowToCourse'], $rows);
    }

    public function create(Course $course): ?Course
    {
        $sql = "INSERT INTO courses (teacher_id, course_code, course_name, description, credits, semester)
                VALUES (:teacher_id, :course_code, :course_name, :description, :credits, :semester)";
        
        $success = $this->execute($sql, [
            'teacher_id' => $course->getTeacherId(),
            'course_code' => $course->getCourseCode(),
            'course_name' => $course->getCourseName(),
            'description' => $course->getDescription(),
            'credits' => $course->getCredits(),
            'semester' => $course->getSemester()
        ]);

        if (!$success) return null;

        return $this->findById($this->lastInsertId());
    }

    public function update(Course $course): bool
    {
        $sql = "UPDATE courses 
                SET teacher_id = :teacher_id,
                    course_code = :course_code,
                    course_name = :course_name,
                    description = :description,
                    credits = :credits,
                    semester = :semester
                WHERE course_id = :course_id";

        return $this->execute($sql, [
            'teacher_id' => $course->getTeacherId(),
            'course_code' => $course->getCourseCode(),
            'course_name' => $course->getCourseName(),
            'description' => $course->getDescription(),
            'credits' => $course->getCredits(),
            'semester' => $course->getSemester(),
            'course_id' => $course->getCourseId()
        ]);
    }

    public function delete(int $courseId): bool
    {
        return $this->execute("DELETE FROM courses WHERE course_id = :course_id", ['course_id' => $courseId]);
    }

    private function mapRowToCourse(array $row): Course
    {
        return new Course(
            $row['course_code'],
            $row['course_name'],
            (int)$row['teacher_id'],
            $row['description'],
            $row['credits'] ? (float)$row['credits'] : null,
            $row['semester'],
            (int)$row['course_id'],
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }

    private function paginationParams(int $limit, int $offset): array
    {
        return [
            'limit' => max(1, $limit),
            'offset' => max(0, $offset),
        ];
    }
}
