<?php

namespace App\Tests\Integration\Controllers;

use App\Controllers\AuthController;
use App\Repositories\UserRepository;
use App\Utils\PasswordHelper;
use PHPUnit\Framework\TestCase;

class AuthControllerTest extends TestCase
{
    private AuthController $controller;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->controller = new AuthController();
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
     * Test that showLogin method exists and is callable
     */
    public function testShowLoginMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'showLogin'));
        $this->assertTrue(is_callable([$this->controller, 'showLogin']));
    }

    /**
     * Test that login method exists and is callable
     */
    public function testLoginMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'login'));
        $this->assertTrue(is_callable([$this->controller, 'login']));
    }

    /**
     * Test that showRegister method exists and is callable
     */
    public function testShowRegisterMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'showRegister'));
        $this->assertTrue(is_callable([$this->controller, 'showRegister']));
    }

    /**
     * Test that register method exists and is callable
     */
    public function testRegisterMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'register'));
        $this->assertTrue(is_callable([$this->controller, 'register']));
    }

    /**
     * Test that logout method exists and is callable
     */
    public function testLogoutMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'logout'));
        $this->assertTrue(is_callable([$this->controller, 'logout']));
    }

    /**
     * Test login with empty credentials
     */
    public function testLoginWithEmptyCredentials(): void
    {
        $_POST = [
            'email' => '',
            'password' => ''
        ];

        ob_start();
        try {
            $this->controller->login();
        } catch (\Exception $e) {
            // Catch redirect or header exceptions
        }
        ob_end_clean();

        // Should not be authenticated
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    /**
     * Test login with invalid credentials
     */
    public function testLoginWithInvalidCredentials(): void
    {
        $_POST = [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ];

        ob_start();
        try {
            $this->controller->login();
        } catch (\Exception $e) {
            // Catch redirect or header exceptions
        }
        ob_end_clean();

        // Should not be authenticated
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    /**
     * Test register with empty email
     */
    public function testRegisterWithEmptyEmail(): void
    {
        $_POST = [
            'email' => '',
            'password' => 'password123',
            'confirm_password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => 'student',
            'student_number' => 'S12345'
        ];

        ob_start();
        try {
            $this->controller->register();
        } catch (\Exception $e) {
            // Catch redirect or header exceptions
        }
        ob_end_clean();

        // Should not be authenticated due to validation error
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    /**
     * Test register with invalid email format
     */
    public function testRegisterWithInvalidEmailFormat(): void
    {
        $_POST = [
            'email' => 'not-an-email',
            'password' => 'password123',
            'confirm_password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => 'student',
            'student_number' => 'S12345'
        ];

        ob_start();
        try {
            $this->controller->register();
        } catch (\Exception $e) {
            // Catch redirect or header exceptions
        }
        ob_end_clean();

        // Should not be authenticated due to validation error
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    /**
     * Test register with short password
     */
    public function testRegisterWithShortPassword(): void
    {
        $_POST = [
            'email' => 'test@example.com',
            'password' => 'short',
            'confirm_password' => 'short',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => 'student',
            'student_number' => 'S12345'
        ];

        ob_start();
        try {
            $this->controller->register();
        } catch (\Exception $e) {
            // Catch redirect or header exceptions
        }
        ob_end_clean();

        // Should not be authenticated due to validation error
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    /**
     * Test register with mismatched passwords
     */
    public function testRegisterWithMismatchedPasswords(): void
    {
        $_POST = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'confirm_password' => 'different123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => 'student',
            'student_number' => 'S12345'
        ];

        ob_start();
        try {
            $this->controller->register();
        } catch (\Exception $e) {
            // Catch redirect or header exceptions
        }
        ob_end_clean();

        // Should not be authenticated due to validation error
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    /**
     * Test register with empty first name
     */
    public function testRegisterWithEmptyFirstName(): void
    {
        $_POST = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123',
            'first_name' => '',
            'last_name' => 'Doe',
            'role' => 'student',
            'student_number' => 'S12345'
        ];

        ob_start();
        try {
            $this->controller->register();
        } catch (\Exception $e) {
            // Catch redirect or header exceptions
        }
        ob_end_clean();

        // Should not be authenticated due to validation error
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    /**
     * Test register with invalid role
     */
    public function testRegisterWithInvalidRole(): void
    {
        $_POST = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => 'admin',
            'student_number' => 'S12345'
        ];

        ob_start();
        try {
            $this->controller->register();
        } catch (\Exception $e) {
            // Catch redirect or header exceptions
        }
        ob_end_clean();

        // Should not be authenticated due to validation error
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    /**
     * Test register as student without student number
     */
    public function testRegisterAsStudentWithoutStudentNumber(): void
    {
        $_POST = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => 'student',
            'student_number' => ''
        ];

        ob_start();
        try {
            $this->controller->register();
        } catch (\Exception $e) {
            // Catch redirect or header exceptions
        }
        ob_end_clean();

        // Should not be authenticated due to validation error
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    /**
     * Test logout clears session
     */
    public function testLogoutClearsSession(): void
    {
        // Set up a fake session
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'student';

        $this->assertArrayHasKey('user_id', $_SESSION);

        ob_start();
        try {
            $this->controller->logout();
        } catch (\Exception $e) {
            // Catch redirect exceptions (expected)
        }
        ob_end_clean();

        // Session should be cleared
        $this->assertEmpty($_SESSION);
    }
}
