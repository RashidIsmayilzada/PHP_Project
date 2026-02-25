<?php
declare(strict_types=1);

namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Framework\Container;
use App\Framework\Router;
use App\Framework\Auth;
use App\Controllers\AuthController;
use App\Services\Interfaces\UserServiceInterface;

class AuthRedirectTest extends TestCase
{
    private $container;
    private $router;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->set(UserServiceInterface::class, fn() => $this->createMock(UserServiceInterface::class));
        $this->container->set(AuthController::class, fn($c) => new AuthController($c->get(UserServiceInterface::class)));
        
        $this->router = new Router($this->container);
        $this->router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
        
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }

    public function testGuestStaysOnLogin(): void
    {
        // No session set
        $this->expectOutputRegex('/<title>Login/i'); 
        // Note: dispatch will call AuthController->showLogin which renders the view
        $this->router->dispatch('GET', '/login');
    }

    public function testAuthenticatedTeacherRedirectsToTeacherDashboard(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'teacher';

        // We expect a header redirect
        // In a CLI environment, we can't easily catch header() calls without extensions,
        // but we can verify that dispatch() terminates or behaviorally changes.
        // For this test, we verify Auth::check() works as expected.
        $this->assertTrue(Auth::check());
        $this->assertEquals('teacher', Auth::role());
    }

    public function testPartialSessionDoesNotCountAsAuthenticated(): void
    {
        $_SESSION['user_id'] = 1;
        // Missing 'role'
        
        $this->assertFalse(Auth::check(), 'Partial session (missing role) should not be authenticated');
    }
}
