<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\AuthService;
use App\Services\Interfaces\PasswordHasherInterface;
use App\Services\Interfaces\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private UserRepositoryInterface|MockObject $userRepository;
    private PasswordHasherInterface|MockObject $passwordHasher;
    private SessionInterface|MockObject $session;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->session = $this->createMock(SessionInterface::class);

        $this->authService = new AuthService(
            $this->userRepository,
            $this->passwordHasher,
            $this->session
        );
    }

    public function testAuthenticateReturnsUserForValidCredentials(): void
    {
        $user = new User(
            'student@example.com',
            password_hash('secret123', PASSWORD_DEFAULT),
            'Jane',
            'Student',
            'student',
            'S001',
            7
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('student@example.com')
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('verify')
            ->with('secret123', $user->getPassword())
            ->willReturn(true);

        $authenticatedUser = $this->authService->authenticate('student@example.com', 'secret123');

        $this->assertSame($user, $authenticatedUser);
    }

    public function testAuthenticateReturnsNullForInvalidCredentials(): void
    {
        $user = new User(
            'student@example.com',
            password_hash('secret123', PASSWORD_DEFAULT),
            'Jane',
            'Student',
            'student'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('student@example.com')
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('verify')
            ->with('wrong-password', $user->getPassword())
            ->willReturn(false);

        $this->assertNull($this->authService->authenticate('student@example.com', 'wrong-password'));
    }

    public function testRegisterHashesPasswordBeforeCreatingUser(): void
    {
        $userData = [
            'email' => 'teacher@example.com',
            'password' => 'plain-password',
            'first_name' => 'Alex',
            'last_name' => 'Teacher',
            'role' => 'teacher',
        ];

        $createdUser = new User(
            'teacher@example.com',
            password_hash('plain-password', PASSWORD_DEFAULT),
            'Alex',
            'Teacher',
            'teacher',
            null,
            10
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('teacher@example.com')
            ->willReturn(null);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hash')
            ->with('plain-password')
            ->willReturn($createdUser->getPassword());

        $this->userRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (User $user): bool {
                $this->assertSame('teacher@example.com', $user->getEmail());
                $this->assertTrue(password_verify('plain-password', $user->getPassword()));
                $this->assertSame('teacher', $user->getRole());

                return true;
            }))
            ->willReturn($createdUser);

        $result = $this->authService->register($userData);

        $this->assertSame($createdUser, $result);
    }

    public function testRoleHelpersArePureUserChecks(): void
    {
        $teacher = new User('teacher@example.com', 'hash', 'Alex', 'Teacher', 'teacher');
        $student = new User('student@example.com', 'hash', 'Jane', 'Student', 'student', 'S001');

        $this->assertTrue($this->authService->isTeacher($teacher));
        $this->assertFalse($this->authService->isTeacher($student));
        $this->assertTrue($this->authService->isStudent($student));
        $this->assertFalse($this->authService->isStudent($teacher));
    }
}