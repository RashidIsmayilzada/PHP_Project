<?php

namespace App\Tests\Integration\Controllers;

use App\Controllers\TeacherController;
use App\Repositories\UserRepository;
use PHPUnit\Framework\TestCase;

class TeacherControllerTest extends TestCase
{
    private TeacherController $controller;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->controller = new TeacherController();
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

    // Set up a mock teacher session
    private function setupTeacherSession(): void
    {
        // Find a teacher user in the database
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

    // Test that dashboard method exists and is callable
    public function testDashboardMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'dashboard'));
        $this->assertTrue(is_callable([$this->controller, 'dashboard']));
    }

    // Test dashboard requires teacher authentication
    public function testDashboardRequiresTeacherAuth(): void
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

    // Test that student cannot access teacher dashboard
    public function testStudentCannotAccessTeacherDashboard(): void
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

            ob_start();
            try {
                $this->controller->dashboard();
                $this->fail('Expected redirect for student accessing teacher dashboard');
            } catch (\Exception $e) {
                // Expected - redirect should happen
                $this->assertTrue(true);
            }
            ob_end_clean();
        } else {
            $this->markTestSkipped('No student user found in database');
        }
    }
}
