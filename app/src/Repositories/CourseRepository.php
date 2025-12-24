<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\Course;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use PDO;
use PDOException;

// Repository for Course database operations
class CourseRepository implements CourseRepositoryInterface
{
    private PDO $db;

    // Constructor initializes database connection
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Test database connection by counting courses
    public function testConnection(): array
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM courses");
            $result = $stmt->fetch();
            return [
                'success' => true,
                'message' => 'Database connection successful',
                'course_count' => $result['count']
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    // Find all courses
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM courses ORDER BY course_code");
        $courses = [];

        while ($row = $stmt->fetch()) {
            $courses[] = $this->mapRowToCourse($row);
        }

        return $courses;
    }

    // Find course by ID
    public function findById(int $id): ?Course
    {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE course_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToCourse($row) : null;
    }

    // Find courses by teacher ID
    public function findByTeacherId(int $teacherId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE teacher_id = :teacher_id ORDER BY course_code");
        $stmt->execute(['teacher_id' => $teacherId]);
        $courses = [];

        while ($row = $stmt->fetch()) {
            $courses[] = $this->mapRowToCourse($row);
        }

        return $courses;
    }

    // Find courses by semester
    public function findBySemester(string $semester): array
    {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE semester = :semester ORDER BY course_code");
        $stmt->execute(['semester' => $semester]);
        $courses = [];

        while ($row = $stmt->fetch()) {
            $courses[] = $this->mapRowToCourse($row);
        }

        return $courses;
    }

    // Find courses by student ID (courses the student is enrolled in)
    public function findByStudentId(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.* FROM courses c
             INNER JOIN enrollments e ON c.course_id = e.course_id
             WHERE e.student_id = :student_id
             ORDER BY c.course_code"
        );
        $stmt->execute(['student_id' => $studentId]);
        $courses = [];

        while ($row = $stmt->fetch()) {
            $courses[] = $this->mapRowToCourse($row);
        }

        return $courses;
    }

    // Create a new course
    public function create(Course $course): ?Course
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO courses (course_code, course_name, description, teacher_id, credits, semester)
                 VALUES (:course_code, :course_name, :description, :teacher_id, :credits, :semester)"
            );

            $stmt->execute([
                'course_code' => $course->getCourseCode(),
                'course_name' => $course->getCourseName(),
                'description' => $course->getDescription(),
                'teacher_id' => $course->getTeacherId(),
                'credits' => $course->getCredits(),
                'semester' => $course->getSemester()
            ]);

            $courseId = (int) $this->db->lastInsertId();
            return $this->findById($courseId);
        } catch (PDOException $e) {
            return null;
        }
    }

    // Update an existing course
    public function update(Course $course): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE courses
                 SET course_code = :course_code,
                     course_name = :course_name,
                     description = :description,
                     teacher_id = :teacher_id,
                     credits = :credits,
                     semester = :semester
                 WHERE course_id = :course_id"
            );

            return $stmt->execute([
                'course_code' => $course->getCourseCode(),
                'course_name' => $course->getCourseName(),
                'description' => $course->getDescription(),
                'teacher_id' => $course->getTeacherId(),
                'credits' => $course->getCredits(),
                'semester' => $course->getSemester(),
                'course_id' => $course->getCourseId()
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Delete a course (cascade deletes assignments and enrollments via FK constraints)
    public function delete(int $courseId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM courses WHERE course_id = :course_id");
            return $stmt->execute(['course_id' => $courseId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Map database row to Course object
    private function mapRowToCourse(array $row): Course
    {
        return new Course(
            $row['course_code'],
            $row['course_name'],
            $row['teacher_id'],
            $row['description'],
            $row['credits'] ? (float)$row['credits'] : null,
            $row['semester'],
            $row['course_id'],
            $row['created_at'],
            $row['updated_at']
        );
    }
}
