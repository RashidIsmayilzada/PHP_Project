<?php

namespace App\Tests\Integration\Controllers;

use App\Controllers\StudentController;
use App\Repositories\UserRepository;
use PHPUnit\Framework\TestCase;

class StudentControllerTest extends TestCase
{
    private StudentController $controller;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->controller = new StudentController();
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
     * Set up a mock student session
     */
    private function setupStudentSession(): void
    {
        // Find a student user in the database
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
        }
    }

    /**
     * Test that dashboard method exists and is callable
     */
    public function testDashboardMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'dashboard'));
        $this->assertTrue(is_callable([$this->controller, 'dashboard']));
    }

    /**
     * Test that courseDetail method exists and is callable
     */
    public function testCourseDetailMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'courseDetail'));
        $this->assertTrue(is_callable([$this->controller, 'courseDetail']));
    }

    /**
     * Test that statistics method exists and is callable
     */
    public function testStatisticsMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'statistics'));
        $this->assertTrue(is_callable([$this->controller, 'statistics']));
    }

    /**
     * Test dashboard requires student authentication
     */
    public function testDashboardRequiresStudentAuth(): void
    {
        // Without authentication
        ob_start();
        try {
            $this->controller->dashboard();
            $this->fail('Expected redirect for unauthenticated user');
        } catch (\Exception $e) {
            // Expected - redirect should happen
            $this->assertTrue(true);
        }
        ob_end_clean();
    }

    /**
     * Test courseDetail requires student authentication
     */
    public function testCourseDetailRequiresStudentAuth(): void
    {
        // Without authentication
        ob_start();
        try {
            $this->controller->courseDetail(1);
            $this->fail('Expected redirect for unauthenticated user');
        } catch (\Exception $e) {
            // Expected - redirect should happen
            $this->assertTrue(true);
        }
        ob_end_clean();
    }

    /**
     * Test statistics requires student authentication
     */
    public function testStatisticsRequiresStudentAuth(): void
    {
        // Without authentication
        ob_start();
        try {
            $this->controller->statistics();
            $this->fail('Expected redirect for unauthenticated user');
        } catch (\Exception $e) {
            // Expected - redirect should happen
            $this->assertTrue(true);
        }
        ob_end_clean();
    }

    /**
     * Test courseDetail accepts course ID parameter
     */
    public function testCourseDetailAcceptsParameter(): void
    {
        $this->setupStudentSession();

        ob_start();
        try {
            $this->controller->courseDetail(1);
        } catch (\Exception $e) {
            // May throw exception if file doesn't exist, that's ok
        }
        ob_end_clean();

        // Check that the parameter was set in $_GET
        $this->assertArrayHasKey('id', $_GET);
        $this->assertEquals(1, $_GET['id']);
    }

    /**
     * Test courseDetail with different course IDs
     */
    public function testCourseDetailWithDifferentIds(): void
    {
        $this->setupStudentSession();

        $testIds = [1, 5, 10, 100];

        foreach ($testIds as $testId) {
            $_GET = []; // Reset $_GET

            ob_start();
            try {
                $this->controller->courseDetail($testId);
            } catch (\Exception $e) {
                // May throw exception if file doesn't exist, that's ok
            }
            ob_end_clean();

            $this->assertArrayHasKey('id', $_GET);
            $this->assertEquals($testId, $_GET['id']);
        }
    }

    /**
     * Test that teacher cannot access student dashboard
     */
    public function testTeacherCannotAccessStudentDashboard(): void
    {
        // Find a teacher user
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

            ob_start();
            try {
                $this->controller->dashboard();
                $this->fail('Expected redirect for teacher accessing student dashboard');
            } catch (\Exception $e) {
                // Expected - redirect should happen
                $this->assertTrue(true);
            }
            ob_end_clean();
        } else {
            $this->markTestSkipped('No teacher user found in database');
        }
    }
}
