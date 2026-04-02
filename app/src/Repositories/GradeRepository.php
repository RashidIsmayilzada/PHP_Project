<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\Grade;
use App\Repositories\Data\CourseGradeData;
use App\Repositories\Interfaces\GradeRepositoryInterface;

class GradeRepository extends Repository implements GradeRepositoryInterface
{
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $rows = $this->fetchAll(
            "SELECT * FROM grades ORDER BY graded_at DESC, grade_id DESC LIMIT :limit OFFSET :offset",
            $this->paginationParams($limit, $offset)
        );

        return array_map([$this, 'mapRowToGrade'], $rows);
    }

    public function findById(int $id): ?Grade
    {
        $row = $this->fetch("SELECT * FROM grades WHERE grade_id = :id", ['id' => $id]);
        return $row ? $this->mapRowToGrade($row) : null;
    }

    public function findByStudentId(int $studentId, int $limit = 100, int $offset = 0): array
    {
        $rows = $this->fetchAll(
            "SELECT * FROM grades WHERE student_id = :student_id ORDER BY graded_at DESC, grade_id DESC LIMIT :limit OFFSET :offset",
            ['student_id' => $studentId] + $this->paginationParams($limit, $offset)
        );

        return array_map([$this, 'mapRowToGrade'], $rows);
    }

    public function findByAssignmentId(int $assignmentId, int $limit = 100, int $offset = 0): array
    {
        $rows = $this->fetchAll(
            "SELECT * FROM grades WHERE assignment_id = :assignment_id ORDER BY graded_at DESC, grade_id DESC LIMIT :limit OFFSET :offset",
            ['assignment_id' => $assignmentId] + $this->paginationParams($limit, $offset)
        );

        return array_map([$this, 'mapRowToGrade'], $rows);
    }

    public function findByStudentAndAssignment(int $studentId, int $assignmentId): ?Grade
    {
        $sql = "SELECT * FROM grades WHERE student_id = :student_id AND assignment_id = :assignment_id";
        $row = $this->fetch($sql, [
            'student_id' => $studentId,
            'assignment_id' => $assignmentId
        ]);
        return $row ? $this->mapRowToGrade($row) : null;
    }

    public function findByCourseId(int $courseId, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT g.* FROM grades g 
                JOIN assignments a ON g.assignment_id = a.assignment_id 
                WHERE a.course_id = :course_id
                ORDER BY g.graded_at DESC, g.grade_id DESC
                LIMIT :limit OFFSET :offset";
        $rows = $this->fetchAll($sql, ['course_id' => $courseId] + $this->paginationParams($limit, $offset));

        return array_map([$this, 'mapRowToGrade'], $rows);
    }

    public function findGradeDataForCourseAndStudent(
        int $courseId,
        int $studentId,
        int $limit = 100,
        int $offset = 0
    ): array
    {
        $sql = "SELECT g.*, a.max_points FROM grades g 
                JOIN assignments a ON g.assignment_id = a.assignment_id 
                WHERE a.course_id = :course_id AND g.student_id = :student_id
                ORDER BY g.graded_at DESC, g.grade_id DESC
                LIMIT :limit OFFSET :offset";
        $rows = $this->fetchAll($sql, [
            'course_id' => $courseId,
            'student_id' => $studentId,
        ] + $this->paginationParams($limit, $offset));

        return array_map([$this, 'mapRowToCourseGradeData'], $rows);
    }

    public function create(Grade $grade): ?Grade
    {
        $sql = "INSERT INTO grades (assignment_id, student_id, points_earned, feedback, graded_at)
                VALUES (:assignment_id, :student_id, :points_earned, :feedback, :graded_at)";
        
        $success = $this->execute($sql, [
            'assignment_id' => $grade->getAssignmentId(),
            'student_id' => $grade->getStudentId(),
            'points_earned' => $grade->getPointsEarned(),
            'feedback' => $grade->getFeedback(),
            'graded_at' => $grade->getGradedAt()
        ]);

        if (!$success) return null;

        return $this->findById($this->lastInsertId());
    }

    public function update(Grade $grade): bool
    {
        $sql = "UPDATE grades 
                SET points_earned = :points_earned,
                    feedback = :feedback
                WHERE grade_id = :grade_id";

        return $this->execute($sql, [
            'points_earned' => $grade->getPointsEarned(),
            'feedback' => $grade->getFeedback(),
            'grade_id' => $grade->getGradeId()
        ]);
    }

    public function delete(int $gradeId): bool
    {
        return $this->execute("DELETE FROM grades WHERE grade_id = :grade_id", ['grade_id' => $gradeId]);
    }

    private function mapRowToGrade(array $row): Grade
    {
        return new Grade(
            (int)$row['assignment_id'],
            (int)$row['student_id'],
            (float)$row['points_earned'],
            $row['feedback'],
            (int)$row['grade_id'],
            $row['graded_at'],
            $row['updated_at'] ?? null
        );
    }

    private function mapRowToCourseGradeData(array $row): CourseGradeData
    {
        return new CourseGradeData(
            (float)$row['points_earned'],
            (float)$row['max_points']
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
