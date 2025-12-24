<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\AuthService;
use App\Repositories\UserRepository;

$userRepository = new UserRepository();
$authService = new AuthService($userRepository);

// If already logged in, redirect to appropriate dashboard
if ($authService->isAuthenticated()) {
    if ($authService->isTeacher()) {
        header('Location: /teacher/dashboard.php');
    } else {
        header('Location: /student/dashboard.php');
    }
    exit;
}

$errors = [];
$formData = [
    'email' => '',
    'first_name' => '',
    'last_name' => '',
    'role' => 'student',
    'student_number' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Validation
    if (empty($formData['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } elseif ($userRepository->findByEmail($formData['email'])) {
        $errors['email'] = 'Email already registered';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
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
        $user = $authService->register($formData);

        if ($user) {
            // User is auto-logged in, redirect to appropriate dashboard
            if ($user->getRole() === 'teacher') {
                header('Location: /teacher/dashboard.php');
            } else {
                header('Location: /student/dashboard.php');
            }
            exit;
        } else {
            $errors['general'] = 'Registration failed. Email may already be registered.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student Grade Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .register-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group.error input,
        .form-group.error select {
            border-color: #c33;
        }

        .error-text {
            color: #c33;
            font-size: 12px;
            margin-top: 5px;
        }

        .general-error {
            background-color: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-register:hover {
            transform: translateY(-2px);
        }

        .register-footer {
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .register-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        #student_number_group {
            display: none;
        }

        #student_number_group.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Create Account</h1>
            <p>Student Grade Management System</p>
        </div>

        <?php if (isset($errors['general'])): ?>
            <div class="general-error">
                <?php echo htmlspecialchars($errors['general']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" id="registerForm">
            <div class="form-group <?php echo isset($errors['email']) ? 'error' : ''; ?>">
                <label for="email">Email Address *</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($formData['email']); ?>"
                    required
                    placeholder="Enter your email"
                >
                <?php if (isset($errors['email'])): ?>
                    <div class="error-text"><?php echo htmlspecialchars($errors['email']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group <?php echo isset($errors['first_name']) ? 'error' : ''; ?>">
                    <label for="first_name">First Name *</label>
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        value="<?php echo htmlspecialchars($formData['first_name']); ?>"
                        required
                        placeholder="First name"
                    >
                    <?php if (isset($errors['first_name'])): ?>
                        <div class="error-text"><?php echo htmlspecialchars($errors['first_name']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group <?php echo isset($errors['last_name']) ? 'error' : ''; ?>">
                    <label for="last_name">Last Name *</label>
                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        value="<?php echo htmlspecialchars($formData['last_name']); ?>"
                        required
                        placeholder="Last name"
                    >
                    <?php if (isset($errors['last_name'])): ?>
                        <div class="error-text"><?php echo htmlspecialchars($errors['last_name']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group <?php echo isset($errors['role']) ? 'error' : ''; ?>">
                <label for="role">I am a *</label>
                <select id="role" name="role" required>
                    <option value="student" <?php echo $formData['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                    <option value="teacher" <?php echo $formData['role'] === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                </select>
                <?php if (isset($errors['role'])): ?>
                    <div class="error-text"><?php echo htmlspecialchars($errors['role']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?php echo isset($errors['student_number']) ? 'error' : ''; ?> <?php echo $formData['role'] === 'student' ? 'show' : ''; ?>" id="student_number_group">
                <label for="student_number">Student Number *</label>
                <input
                    type="text"
                    id="student_number"
                    name="student_number"
                    value="<?php echo htmlspecialchars($formData['student_number']); ?>"
                    placeholder="Enter student number"
                >
                <?php if (isset($errors['student_number'])): ?>
                    <div class="error-text"><?php echo htmlspecialchars($errors['student_number']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?php echo isset($errors['password']) ? 'error' : ''; ?>">
                <label for="password">Password *</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="At least 8 characters"
                >
                <?php if (isset($errors['password'])): ?>
                    <div class="error-text"><?php echo htmlspecialchars($errors['password']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>">
                <label for="confirm_password">Confirm Password *</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    required
                    placeholder="Re-enter password"
                >
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="error-text"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-register">Create Account</button>
        </form>

        <div class="register-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        // Show/hide student number field based on role
        document.getElementById('role').addEventListener('change', function() {
            const studentNumberGroup = document.getElementById('student_number_group');
            if (this.value === 'student') {
                studentNumberGroup.classList.add('show');
                document.getElementById('student_number').required = true;
            } else {
                studentNumberGroup.classList.remove('show');
                document.getElementById('student_number').required = false;
            }
        });
    </script>
</body>
</html>
