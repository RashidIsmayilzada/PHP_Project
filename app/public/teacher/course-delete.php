<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\AuthService;
use App\Services\CourseService;
use App\Repositories\UserRepository;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\AssignmentRepository;

$userRepository = new UserRepository();
$courseRepository = new CourseRepository();
$enrollmentRepository = new EnrollmentRepository();
$assignmentRepository = new AssignmentRepository();

$authService = new AuthService($userRepository);
$courseService = new CourseService($courseRepository, $userRepository);

// Require teacher authentication
$authService->requireRole('teacher');
$currentUser = $authService->getCurrentUser();

// Get course ID from URL
$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$courseId) {
    header('Location: /teacher/dashboard.php');
    exit;
}

// Load course
$course = $courseService->findById($courseId);
if (!$course) {
    header('Location: /teacher/dashboard.php');
    exit;
}

// Verify teacher owns this course
if ($course->getTeacherId() !== $currentUser->getUserId()) {
    header('Location: /403.php');
    exit;
}

// Check for enrollments and assignments
$enrollments = $enrollmentRepository->findByCourseId($courseId);
$assignments = $assignmentRepository->findByCourseId($courseId);
$hasEnrollments = count($enrollments) > 0;
$hasAssignments = count($assignments) > 0;

// Handle delete confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === '1') {
    $success = $courseService->deleteCourse($courseId);
    if ($success) {
        header('Location: /teacher/dashboard.php');
        exit;
    } else {
        $error = 'Failed to delete course. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Course</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 700px;
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
            font-size: 28px;
        }

        .header a {
            color: #667eea;
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid #667eea;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .header a:hover {
            background: #667eea;
            color: white;
        }

        .delete-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .delete-container h2 {
            color: #dc3545;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .course-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .course-info h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .course-info p {
            color: #666;
            margin-bottom: 5px;
        }

        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .warning h4 {
            margin-bottom: 10px;
        }

        .warning ul {
            margin-left: 20px;
        }

        .warning li {
            margin-bottom: 5px;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .confirmation {
            margin: 30px 0;
            padding: 20px;
            border: 2px solid #dc3545;
            border-radius: 5px;
            background: #fff5f5;
        }

        .confirmation p {
            color: #333;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            flex: 1;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            flex: 1;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Delete Course</h1>
            <a href="/teacher/dashboard.php">← Back to Dashboard</a>
        </div>

        <div class="delete-container">
            <h2>⚠️ Delete Course</h2>

            <?php if (isset($error)): ?>
                <div class="alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="course-info">
                <h3><?php echo htmlspecialchars($course->getCourseCode()); ?> - <?php echo htmlspecialchars($course->getCourseName()); ?></h3>
                <p><strong>Semester:</strong> <?php echo htmlspecialchars($course->getSemester() ?? 'N/A'); ?></p>
                <p><strong>Credits:</strong> <?php echo htmlspecialchars($course->getCredits() ?? 'N/A'); ?></p>
            </div>

            <?php if ($hasEnrollments || $hasAssignments): ?>
                <div class="warning">
                    <h4>⚠️ Warning: This course has associated data</h4>
                    <ul>
                        <?php if ($hasEnrollments): ?>
                            <li><strong><?php echo count($enrollments); ?> enrolled student(s)</strong> - All enrollments will be deleted</li>
                        <?php endif; ?>
                        <?php if ($hasAssignments): ?>
                            <li><strong><?php echo count($assignments); ?> assignment(s)</strong> - All assignments and their grades will be deleted</li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="confirmation">
                <p><strong>Are you absolutely sure you want to delete this course?</strong></p>
                <p>This action cannot be undone. All associated data including enrollments, assignments, and grades will be permanently deleted.</p>

                <form method="POST" action="">
                    <input type="hidden" name="confirm" value="1">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">Yes, Delete Course</button>
                        <a href="/teacher/dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
