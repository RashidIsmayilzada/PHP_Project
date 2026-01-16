<?php

namespace App\Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserModelTest extends TestCase
{
    // Test User model construction with minimum required fields (student)
    public function testUserConstructionAsStudent(): void
    {
        $user = new User(
            email: 'student@example.com',
            password: 'hashedPassword123',
            firstName: 'John',
            lastName: 'Doe',
            role: 'student',
            studentNumber: 'S12345'
        );

        $this->assertNull($user->getUserId());
        $this->assertEquals('student@example.com', $user->getEmail());
        $this->assertEquals('hashedPassword123', $user->getPassword());
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals('student', $user->getRole());
        $this->assertEquals('S12345', $user->getStudentNumber());
        $this->assertTrue($user->isStudent());
        $this->assertFalse($user->isTeacher());
    }

    // Test User model construction as teacher
    public function testUserConstructionAsTeacher(): void
    {
        $user = new User(
            email: 'teacher@example.com',
            password: 'hashedPassword456',
            firstName: 'Jane',
            lastName: 'Smith',
            role: 'teacher'
        );

        $this->assertEquals('teacher', $user->getRole());
        $this->assertNull($user->getStudentNumber());
        $this->assertTrue($user->isTeacher());
        $this->assertFalse($user->isStudent());
    }

    // Test User model construction with all fields
    public function testUserConstructionWithAllFields(): void
    {
        $user = new User(
            email: 'user@example.com',
            password: 'hashedPassword789',
            firstName: 'Alice',
            lastName: 'Johnson',
            role: 'student',
            studentNumber: 'S67890',
            userId: 10,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-15 14:30:00'
        );

        $this->assertEquals(10, $user->getUserId());
        $this->assertEquals('user@example.com', $user->getEmail());
        $this->assertEquals('hashedPassword789', $user->getPassword());
        $this->assertEquals('Alice', $user->getFirstName());
        $this->assertEquals('Johnson', $user->getLastName());
        $this->assertEquals('student', $user->getRole());
        $this->assertEquals('S67890', $user->getStudentNumber());
        $this->assertEquals('2025-01-01 10:00:00', $user->getCreatedAt());
        $this->assertEquals('2025-01-15 14:30:00', $user->getUpdatedAt());
    }

    // Test getFullName method
    public function testGetFullName(): void
    {
        $user = new User(
            'john.doe@example.com',
            'hashedPassword',
            'John',
            'Doe',
            'student'
        );

        $this->assertEquals('John Doe', $user->getFullName());
    }

    // Test getFullName with different names
    public function testGetFullNameVariations(): void
    {
        $user1 = new User('test@test.com', 'pass', 'Alice', 'Smith', 'student');
        $this->assertEquals('Alice Smith', $user1->getFullName());

        $user2 = new User('test@test.com', 'pass', 'Bob', 'Johnson-Lee', 'teacher');
        $this->assertEquals('Bob Johnson-Lee', $user2->getFullName());
    }

    // Test setting user ID
    public function testSetUserId(): void
    {
        $user = new User('test@test.com', 'pass', 'John', 'Doe', 'student');
        $this->assertNull($user->getUserId());

        $user->setUserId(5);
        $this->assertEquals(5, $user->getUserId());
    }

    // Test role validation methods
    public function testRoleValidation(): void
    {
        $student = new User('student@test.com', 'pass', 'John', 'Doe', 'student');
        $this->assertTrue($student->isStudent());
        $this->assertFalse($student->isTeacher());

        $teacher = new User('teacher@test.com', 'pass', 'Jane', 'Smith', 'teacher');
        $this->assertTrue($teacher->isTeacher());
        $this->assertFalse($teacher->isStudent());
    }

    // Test student without student number
    public function testStudentWithoutStudentNumber(): void
    {
        $user = new User(
            'student@example.com',
            'hashedPassword',
            'John',
            'Doe',
            'student'
        );

        $this->assertNull($user->getStudentNumber());
        $this->assertTrue($user->isStudent());
    }

    // Test email validation (format checking)
    public function testEmailFormat(): void
    {
        $user = new User(
            'user@example.com',
            'pass',
            'John',
            'Doe',
            'student'
        );

        $this->assertEquals('user@example.com', $user->getEmail());
        $this->assertStringContainsString('@', $user->getEmail());
    }
}
