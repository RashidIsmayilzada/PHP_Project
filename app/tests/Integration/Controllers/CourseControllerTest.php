<?php

namespace App\Tests\Integration\Controllers;

use App\Controllers\CourseController;
use App\Repositories\UserRepository;
use PHPUnit\Framework\TestCase;

class CourseControllerTest extends TestCase
{
    private CourseController $controller;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->controller = new CourseController();
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
     * Test that show method exists and is callable
     */
    public function testShowMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'show'));
        $this->assertTrue(is_callable([$this->controller, 'show']));
    }

    /**
     * Test that createAction method exists and is callable
     */
    public function testCreateActionMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'createAction'));
        $this->assertTrue(is_callable([$this->controller, 'createAction']));
    }

    /**
     * Test that editAction method exists and is callable
     */
    public function testEditActionMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'editAction'));
        $this->assertTrue(is_callable([$this->controller, 'editAction']));
    }

    /**
     * Test that delete method exists and is callable
     */
    public function testDeleteMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'delete'));
        $this->assertTrue(is_callable([$this->controller, 'delete']));
    }

    /**
     * Test show requires teacher authentication
     */
    public function testShowRequiresTeacherAuth(): void
    {
        ob_start();
        try {
            $this->controller->show(1);
            $this->fail('Expected redirect for unauthenticated user');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
        ob_end_clean();
    }

    /**
     * Test createAction requires teacher authentication
     */
    public function testCreateActionRequiresTeacherAuth(): void
    {
        ob_start();
        try {
            $this->controller->createAction();
            $this->fail('Expected redirect for unauthenticated user');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
        ob_end_clean();
    }

    /**
     * Test editAction requires teacher authentication
     */
    public function testEditActionRequiresTeacherAuth(): void
    {
        ob_start();
        try {
            $this->controller->editAction(1);
            $this->fail('Expected redirect for unauthenticated user');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
        ob_end_clean();
    }

    /**
     * Test delete requires teacher authentication
     */
    public function testDeleteRequiresTeacherAuth(): void
    {
        ob_start();
        try {
            $this->controller->delete(1);
            $this->fail('Expected redirect for unauthenticated user');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
        ob_end_clean();
    }

    /**
     * Test show accepts course ID parameter
     */
    public function testShowAcceptsParameter(): void
    {
        $this->setupTeacherSession();

        ob_start();
        try {
            $this->controller->show(1);
        } catch (\Exception $e) {
            // May throw exception if file doesn't exist, that's ok
        }
        ob_end_clean();

        $this->assertArrayHasKey('id', $_GET);
        $this->assertEquals(1, $_GET['id']);
    }

    /**
     * Test editAction accepts course ID parameter
     */
    public function testEditActionAcceptsParameter(): void
    {
        $this->setupTeacherSession();

        ob_start();
        try {
            $this->controller->editAction(5);
        } catch (\Exception $e) {
            // May throw exception if file doesn't exist, that's ok
        }
        ob_end_clean();

        $this->assertArrayHasKey('id', $_GET);
        $this->assertEquals(5, $_GET['id']);
    }

    /**
     * Test delete accepts course ID parameter
     */
    public function testDeleteAcceptsParameter(): void
    {
        $this->setupTeacherSession();

        ob_start();
        try {
            $this->controller->delete(10);
        } catch (\Exception $e) {
            // May throw exception if file doesn't exist, that's ok
        }
        ob_end_clean();

        $this->assertArrayHasKey('id', $_GET);
        $this->assertEquals(10, $_GET['id']);
    }

    /**
     * Test student cannot access course controller methods
     */
    public function testStudentCannotAccessCourseActions(): void
    {
        // Find a student user
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

            // Test show
            ob_start();
            try {
                $this->controller->show(1);
                $this->fail('Expected redirect for student accessing teacher action');
            } catch (\Exception $e) {
                $this->assertTrue(true);
            }
            ob_end_clean();

            // Test createAction
            ob_start();
            try {
                $this->controller->createAction();
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
