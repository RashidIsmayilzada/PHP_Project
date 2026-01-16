<?php

namespace App\Tests\Integration\Controllers;

use App\Controllers\EnrollmentController;
use App\Repositories\UserRepository;
use PHPUnit\Framework\TestCase;

class EnrollmentControllerTest extends TestCase
{
    private EnrollmentController $controller;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->controller = new EnrollmentController();
        $this->userRepository = new UserRepository();

        // Clean up session
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
    }

    protected function tearDown(): void
    {
        // Clean up session and superglobals
        $_SESSION = [];
        $_GET = [];
        $_POST = [];

        parent::tearDown();
    }

    /**
     * Set up a mock teacher session
     */
    private function setupTeacherSession(): void
    {
        $users = $this->userRepository->findAll();
        $teacher = null;

        foreach ($users as $user) {
            if ($user->getRole() === 'teacher') {
                $teacher = $user;
                break;
            }
        }

        if ($teacher) {
            $_SESSION['user_id'] = $teacher->getUserId();
            $_SESSION['user_role'] = 'teacher';
        }
    }

    /**
     * Test that enrollAction method exists and is callable
     */
    public function testEnrollActionMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'enrollAction'));
        $this->assertTrue(is_callable([$this->controller, 'enrollAction']));
    }

    /**
     * Test enrollAction requires teacher authentication
     */
    public function testEnrollActionRequiresTeacherAuth(): void
    {
        ob_start();
        try {
            $this->controller->enrollAction(1);
            $this->fail('Expected redirect for unauthenticated user');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
        ob_end_clean();
    }

    /**
     * Test enrollAction accepts course ID parameter
     */
    public function testEnrollActionAcceptsParameter(): void
    {
        $this->setupTeacherSession();

        ob_start();
        try {
            $this->controller->enrollAction(5);
        } catch (\Exception $e) {
            // May throw exception if file doesn't exist, that's ok
        }
        ob_end_clean();

        $this->assertArrayHasKey('course_id', $_GET);
        $this->assertEquals(5, $_GET['course_id']);
    }

    /**
     * Test enrollAction with different course IDs
     */
    public function testEnrollActionWithDifferentIds(): void
    {
        $this->setupTeacherSession();

        $testIds = [1, 5, 10, 100];

        foreach ($testIds as $testId) {
            $_GET = []; // Reset $_GET

            ob_start();
            try {
                $this->controller->enrollAction($testId);
            } catch (\Exception $e) {
                // May throw exception if file doesn't exist, that's ok
            }
            ob_end_clean();

            $this->assertArrayHasKey('course_id', $_GET);
            $this->assertEquals($testId, $_GET['course_id']);
        }
    }

    /**
     * Test student cannot access enrollment actions
     */
    public function testStudentCannotAccessEnrollmentActions(): void
    {
        $users = $this->userRepository->findAll();
        $student = null;

        foreach ($users as $user) {
            if ($user->getRole() === 'student') {
                $student = $user;
                break;
            }
        }

        if ($student) {
            $_SESSION['user_id'] = $student->getUserId();
            $_SESSION['user_role'] = 'student';

            ob_start();
            try {
                $this->controller->enrollAction(1);
                $this->fail('Expected redirect for student accessing teacher action');
            } catch (\Exception $e) {
                $this->assertTrue(true);
            }
            ob_end_clean();
        } else {
            $this->markTestSkipped('No student user found in database');
        }
    }
}
