<?php

namespace App\Controllers;

use App\Config;
use App\Repositories\UserRepository;

class AuthController extends BaseController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new UserRepository();
    }

    // Render the login page or redirect if user is already logged in
    public function showLogin(): void
    {
        // If already logged in, redirect to appropriate dashboard
        if ($this->getAuthService()->isAuthenticated()) {
            if ($this->getAuthService()->isTeacher()) {
                $this->redirect('/teacher/dashboard');
            } else {
                $this->redirect('/student/dashboard');
            }
        }

        $error = '';
        $success = '';
        $email = '';

        // Check for success message
        if (isset($_GET['message'])) {
            if ($_GET['message'] === 'logout_success') {
                $success = 'You have been successfully logged out.';
            } elseif ($_GET['message'] === 'registration_success') {
                $success = 'Registration successful! Please login with your credentials.';
            }
        }

        $this->render('auth/login', [
            'error' => $error,
            'success' => $success,
            'email' => $email
        ]);
    }

    // Process login form and authenticate user
    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $error = '';

        // Validate input
        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
            // Attempt login
            if ($this->getAuthService()->login($email, $password)) {
                // Redirect based on role
                if ($this->getAuthService()->isTeacher()) {
                    $this->redirect('/teacher/dashboard');
                } else {
                    $this->redirect('/student/dashboard');
                }
            } else {
                $error = 'Invalid email or password.';
            }
        }

        // If we get here, there was an error - re-render login form
        $this->render('auth/login', [
            'error' => $error,
            'success' => '',
            'email' => $email
        ]);
    }

    // Display the registration form or redirect if already authenticated
    public function showRegister(): void
    {
        // If already logged in, redirect to appropriate dashboard
        if ($this->getAuthService()->isAuthenticated()) {
            if ($this->getAuthService()->isTeacher()) {
                $this->redirect('/teacher/dashboard');
            } else {
                $this->redirect('/student/dashboard');
            }
        }

        $errors = [];
        $formData = [
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'role' => 'student',
            'student_number' => ''
        ];

        $this->render('auth/register', [
            'errors' => $errors,
            'formData' => $formData
        ]);
    }

    // Process registration form, validate input, and create new user account
    public function register(): void
    {
        // Get form data
        $formData = [
            'email' => trim($_POST['email'] ?? ''),
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'role' => $_POST['role'] ?? 'student',
            'student_number' => trim($_POST['student_number'] ?? '')
        ];
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $errors = [];

        // Validation
        if (empty($formData['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif ($this->userRepository->findByEmail($formData['email'])) {
            $errors['email'] = 'Email already registered';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < Config::getPasswordMinLength()) {
            $errors['password'] = 'Password must be at least ' . Config::getPasswordMinLength() . ' characters';
        }

        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (empty($formData['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }

        if (empty($formData['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }

        if (!in_array($formData['role'], ['student', 'teacher'])) {
            $errors['role'] = 'Invalid role selected';
        }

        if ($formData['role'] === 'student' && empty($formData['student_number'])) {
            $errors['student_number'] = 'Student number is required for students';
        }

        // If no errors, attempt to register
        if (empty($errors)) {
            $formData['password'] = $password;
            $user = $this->getAuthService()->register($formData);

            if ($user) {
                // User is auto-logged in, redirect to appropriate dashboard
                if ($user->getRole() === 'teacher') {
                    $this->redirect('/teacher/dashboard');
                } else {
                    $this->redirect('/student/dashboard');
                }
            } else {
                $errors['general'] = 'Registration failed. Email may already be registered.';
            }
        }

        // If we get here, there were errors - re-render registration form
        $this->render('auth/register', [
            'errors' => $errors,
            'formData' => $formData
        ]);
    }

    // Log out the current user and redirect to login page
    public function logout(): void
    {
        $this->getAuthService()->logout();
        $this->redirect('/login?message=logout_success');
    }
}
