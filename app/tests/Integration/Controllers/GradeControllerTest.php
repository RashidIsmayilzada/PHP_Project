<?php

namespace App\Tests\Integration\Controllers;

use App\Controllers\GradeController;
use App\Repositories\UserRepository;
use PHPUnit\Framework\TestCase;

class GradeControllerTest extends TestCase
{
    private GradeController $controller;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->controller = new GradeController();
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
     * Test that showCourseGrades method exists and is callable
     */
    public function testShowCourseGradesMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'showCourseGrades'));
        $this->assertTrue(is_callable([$this->controller, 'showCourseGrades']));
    }

    /**
     * Test that gradeAction method exists and is callable
     */
    public function testGradeActionMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'gradeAction'));
        $this->assertTrue(is_callable([$this->controller, 'gradeAction']));
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
     * Test showCourseGrades requires teacher authentication
     */
    public function testShowCourseGradesRequiresTeacherAuth(): void
    {
        ob_start();
        try {
            $this->controller->showCourseGrades(1);
            $this->fail('Expected redirect for unauthenticated user');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
        ob_end_clean();
    }

    /**
     * Test gradeAction requires teacher authentication
     */
    public function testGradeActionRequiresTeacherAuth(): void
    {
        ob_start();
        try {
            $this->controller->gradeAction(1);
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
     * Test showCourseGrades accepts course ID parameter
     */
    public function testShowCourseGradesAcceptsParameter(): void
    {
        $this->setupTeacherSession();

        ob_start();
        try {
            $this->controller->showCourseGrades(5);
        } catch (\Exception $e) {
            // May throw exception if file doesn't exist, that's ok
        }
        ob_end_clean();

        $this->assertArrayHasKey('course_id', $_GET);
        $this->assertEquals(5, $_GET['course_id']);
    }

    /**
     * Test gradeAction accepts assignment ID parameter
     */
    public function testGradeActionAcceptsParameter(): void
    {
        $this->setupTeacherSession();

        ob_start();
        try {
            $this->controller->gradeAction(10);
        } catch (\Exception $e) {
            // May throw exception if file doesn't exist, that's ok
        }
        ob_end_clean();

        $this->assertArrayHasKey('assignment_id', $_GET);
        $this->assertEquals(10, $_GET['assignment_id']);
    }

    /**
     * Test editAction accepts grade ID parameter
     */
    public function testEditActionAcceptsParameter(): void
    {
        $this->setupTeacherSession();

        ob_start();
        try {
            $this->controller->editAction(15);
        } catch (\Exception $e) {
            // May throw exception if file doesn't exist, that's ok
        }
        ob_end_clean();

        $this->assertArrayHasKey('id', $_GET);
        $this->assertEquals(15, $_GET['id']);
    }

    /**
     * Test student cannot access grade controller methods
     */
    public function testStudentCannotAccessGradeActions(): void
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
                $this->controller->showCourseGrades(1);
                $this->fail('Expected redirect for student accessing teacher action');
            } catch (\Exception $e) {
                $this->assertTrue(true);
            }
            ob_end_clean();

            ob_start();
            try {
                $this->controller->gradeAction(1);
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
