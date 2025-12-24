<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\AuthService;
use App\Repositories\UserRepository;

$userRepository = new UserRepository();
$authService = new AuthService($userRepository);

// Require teacher authentication
$authService->requireRole('teacher');
$currentUser = $authService->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
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
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
        }

        .user-info {
            color: #666;
        }

        .user-info a {
            color: #667eea;
            text-decoration: none;
            margin-left: 15px;
        }

        .user-info a:hover {
            text-decoration: underline;
        }

        .dashboard-content {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .dashboard-content h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .dashboard-content p {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
        }

        .quick-links {
            margin-top: 30px;
        }

        .quick-links a {
            display: inline-block;
            margin: 10px;
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: transform 0.2s;
        }

        .quick-links a:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Teacher Dashboard</h1>
            <div class="user-info">
                Welcome, <strong><?php echo htmlspecialchars($currentUser->getFullName()); ?></strong>
                <a href="/logout.php">Logout</a>
            </div>
        </div>

        <div class="dashboard-content">
            <h2>Welcome to Your Teacher Dashboard!</h2>
            <p>Your registration was successful. Full dashboard features will be available soon.</p>
            <p>For now, you can access the existing pages:</p>

            <div class="quick-links">
                <a href="/users.php">Users</a>
                <a href="/courses.php">Courses</a>
                <a href="/assignments.php">Assignments</a>
                <a href="/grades.php">Grades</a>
                <a href="/enrollments.php">Enrollments</a>
            </div>
        </div>
    </div>
</body>
</html>
