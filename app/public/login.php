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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Attempt login
        if ($authService->login($email, $password)) {
            // Redirect based on role
            if ($authService->isTeacher()) {
                header('Location: /teacher/dashboard.php');
            } else {
                header('Location: /student/dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Grade Management</title>
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
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .login-header p {
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

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .error-message {
            background-color: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .success-message {
            background-color: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .login-footer {
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .credentials-hint {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 5px;
            font-size: 12px;
            color: #666;
        }

        .credentials-hint strong {
            color: #333;
        }

        .credentials-hint ul {
            margin-top: 10px;
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Student Grade Management System</p>
        </div>

        <?php if ($success): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($email); ?>"
                    required
                    placeholder="Enter your email"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Enter your password"
                >
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="login-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>

        <div class="credentials-hint">
            <strong>Demo Credentials:</strong>
            <ul>
                <li><strong>Teacher:</strong> john.doe@university.edu</li>
                <li><strong>Student:</strong> alice.student@university.edu</li>
                <li><strong>Password:</strong> password123</li>
            </ul>
        </div>
    </div>
</body>
</html>
