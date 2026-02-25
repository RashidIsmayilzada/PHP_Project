<?php
declare(strict_types=1);

namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Framework\Container;
use App\Framework\Router;
use App\Controllers\UserController;
use App\Services\Interfaces\UserServiceInterface;
use App\Models\User;
use App\Framework\Auth;

class ApiTest extends TestCase
{
    private $container;
    private $router;
    private $userService;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->userService = $this->createMock(UserServiceInterface::class);
        
        // Bind the mock service to the container
        $this->container->set(UserServiceInterface::class, fn() => $this->userService);
        $this->container->set(UserController::class, fn($c) => new UserController($c->get(UserServiceInterface::class)));

        $this->router = new Router($this->container);
        
        // Define the API route for testing
        $this->router->get('/api/students', [UserController::class, 'students']);
    }

    public function testStudentsApiReturnsJsonResponse(): void
    {
        // Mock session for Auth (since UserController calls Auth::requireRole)
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'teacher';

        // Prepare mock data
        $student = $this->createMock(User::class);
        $student->method('getUserId')->willReturn(101);
        $student->method('getFullName')->willReturn('John Doe');
        $student->method('getStudentNumber')->willReturn('S12345');

        $this->userService->method('findAllStudents')->willReturn([$student]);

        // Capture output
        ob_start();
        $this->router->dispatch('GET', '/api/students');
        $output = ob_get_clean();

        // Verify response
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals('John Doe', $data[0]['name']);
        $this->assertEquals('S12345', $data[0]['student_number']);
    }
}
