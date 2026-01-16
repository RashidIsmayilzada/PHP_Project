<?php

namespace App\Tests\Unit;

use App\Models\Assignment;
use PHPUnit\Framework\TestCase;

class AssignmentModelTest extends TestCase
{
    /**
     * Test Assignment model construction with minimum required fields
     */
    public function testAssignmentConstructionWithMinimumFields(): void
    {
        $assignment = new Assignment(
            courseId: 1,
            assignmentName: 'Homework 1',
            maxPoints: 100.0
        );

        $this->assertNull($assignment->getAssignmentId());
        $this->assertEquals(1, $assignment->getCourseId());
        $this->assertEquals('Homework 1', $assignment->getAssignmentName());
        $this->assertEquals(100.0, $assignment->getMaxPoints());
        $this->assertNull($assignment->getDescription());
        $this->assertNull($assignment->getDueDate());
        $this->assertNull($assignment->getCreatedAt());
        $this->assertNull($assignment->getUpdatedAt());
    }

    /**
     * Test Assignment model construction with all fields
     */
    public function testAssignmentConstructionWithAllFields(): void
    {
        $assignment = new Assignment(
            courseId: 1,
            assignmentName: 'Final Project',
            maxPoints: 200.0,
            description: 'Complete the final project as described in the syllabus',
            dueDate: '2025-05-15 23:59:59',
            assignmentId: 10,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-15 14:30:00'
        );

        $this->assertEquals(10, $assignment->getAssignmentId());
        $this->assertEquals(1, $assignment->getCourseId());
        $this->assertEquals('Final Project', $assignment->getAssignmentName());
        $this->assertEquals(200.0, $assignment->getMaxPoints());
        $this->assertEquals('Complete the final project as described in the syllabus', $assignment->getDescription());
        $this->assertEquals('2025-05-15 23:59:59', $assignment->getDueDate());
        $this->assertEquals('2025-01-01 10:00:00', $assignment->getCreatedAt());
        $this->assertEquals('2025-01-15 14:30:00', $assignment->getUpdatedAt());
    }

    /**
     * Test setting assignment ID
     */
    public function testSetAssignmentId(): void
    {
        $assignment = new Assignment(1, 'Quiz 1', 50.0);
        $this->assertNull($assignment->getAssignmentId());

        $assignment->setAssignmentId(5);
        $this->assertEquals(5, $assignment->getAssignmentId());
    }

    /**
     * Test setting max points
     */
    public function testSetMaxPoints(): void
    {
        $assignment = new Assignment(1, 'Test', 100.0);
        $this->assertEquals(100.0, $assignment->getMaxPoints());

        $assignment->setMaxPoints(150.0);
        $this->assertEquals(150.0, $assignment->getMaxPoints());
    }

    /**
     * Test assignment with decimal max points
     */
    public function testAssignmentWithDecimalMaxPoints(): void
    {
        $assignment = new Assignment(1, 'Lab 1', 87.5);
        $this->assertEquals(87.5, $assignment->getMaxPoints());
    }

    /**
     * Test assignment with description
     */
    public function testAssignmentWithDescription(): void
    {
        $description = 'Write a program that implements a binary search tree';
        $assignment = new Assignment(1, 'Assignment 2', 100.0, $description);
        $this->assertEquals($description, $assignment->getDescription());
    }

    /**
     * Test assignment without description
     */
    public function testAssignmentWithoutDescription(): void
    {
        $assignment = new Assignment(1, 'Quiz', 50.0);
        $this->assertNull($assignment->getDescription());
    }

    /**
     * Test assignment with due date
     */
    public function testAssignmentWithDueDate(): void
    {
        $assignment = new Assignment(
            1,
            'Homework 1',
            100.0,
            null,
            '2025-03-15 23:59:59'
        );
        $this->assertEquals('2025-03-15 23:59:59', $assignment->getDueDate());
    }

    /**
     * Test assignment without due date
     */
    public function testAssignmentWithoutDueDate(): void
    {
        $assignment = new Assignment(1, 'Practice Problems', 0.0);
        $this->assertNull($assignment->getDueDate());
    }

    /**
     * Test assignment with zero max points
     */
    public function testAssignmentWithZeroMaxPoints(): void
    {
        $assignment = new Assignment(1, 'Ungraded Assignment', 0.0);
        $this->assertEquals(0.0, $assignment->getMaxPoints());
    }

    /**
     * Test multiple assignments with different names
     */
    public function testMultipleAssignmentsWithDifferentNames(): void
    {
        $assignments = [
            new Assignment(1, 'Homework 1', 100.0),
            new Assignment(1, 'Quiz 1', 50.0),
            new Assignment(1, 'Midterm Exam', 200.0),
            new Assignment(1, 'Final Project', 300.0),
        ];

        $this->assertEquals('Homework 1', $assignments[0]->getAssignmentName());
        $this->assertEquals('Quiz 1', $assignments[1]->getAssignmentName());
        $this->assertEquals('Midterm Exam', $assignments[2]->getAssignmentName());
        $this->assertEquals('Final Project', $assignments[3]->getAssignmentName());
    }
}
