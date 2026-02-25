<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Controller;
use App\Services\Interfaces\UserServiceInterface;
use App\Config;

class AuthController extends Controller
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::role() === 'teacher' ? '/teacher/dashboard' : '/student/dashboard');
        }

        $success = '';
        if (isset($_GET['message'])) {
            if ($_GET['message'] === 'logout_success') $success = 'You have been successfully logged out.';
            if ($_GET['message'] === 'registration_success') $success = 'Registration successful! Please login.';
            if ($_GET['message'] === 'session_expired') $success = 'Your session has expired due to inactivity.';
        }

        $this->render('auth/login', [
            'pageTitle' => 'Login',
            'pageCss' => 'auth',
            'error' => $_SESSION['error'] ?? '',
            'success' => $success,
            'email' => $_SESSION['old_email'] ?? ''
        ]);
        unset($_SESSION['error'], $_SESSION['old_email']);
    }

    public function login(): void
    {
        $email = $this->request('email', '');
        $password = $this->request('password', '');

        $user = $this->userService->findByEmail($email);

        if ($user && password_verify($password, $user->getPassword())) {
            Auth::login($user->getUserId(), $user->getRole());
            $this->redirect($user->getRole() === 'teacher' ? '/teacher/dashboard' : '/student/dashboard');
        }

        $_SESSION['error'] = 'Invalid email or password.';
        $_SESSION['old_email'] = $email;
        $this->redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login?message=logout_success');
    }

    public function showRegister(): void
    {
        $this->render('auth/register', [
            'pageTitle' => 'Register',
            'pageCss' => 'auth',
            'errors' => $_SESSION['errors'] ?? [],
            'formData' => $_SESSION['old_data'] ?? ['email' => '', 'first_name' => '', 'last_name' => '', 'role' => 'student', 'student_number' => '']
        ]);
        unset($_SESSION['errors'], $_SESSION['old_data']);
    }

    public function register(): void
    {
        $userData = [
            'first_name' => $this->request('first_name', ''),
            'last_name' => $this->request('last_name', ''),
            'email' => $this->request('email', ''),
            'password' => $this->request('password', ''),
            'role' => $this->request('role', 'student'),
            'student_number' => $this->request('student_number', '')
        ];

        $errors = [];
        if (empty($userData['first_name'])) $errors['first_name'] = 'First name is required.';
        if (empty($userData['last_name'])) $errors['last_name'] = 'Last name is required.';
        if (empty($userData['email'])) $errors['email'] = 'Email is required.';
        if (empty($userData['password'])) $errors['password'] = 'Password is required.';
        if ($userData['role'] === 'student' && empty($userData['student_number'])) {
            $errors['student_number'] = 'Student number is required for students.';
        }

        if (empty($errors)) {
            $user = $this->userService->createUser($userData);
            if ($user) {
                $this->redirect('/login?message=registration_success');
                return;
            }
            $errors['email'] = 'Email already exists or registration failed.';
        }

        $_SESSION['errors'] = $errors;
        $_SESSION['old_data'] = $userData;
        $this->redirect('/register');
    }
}
