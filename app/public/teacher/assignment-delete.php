<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\AuthService;
use App\Services\AssignmentService;
use App\Services\CourseService;
use App\Repositories\UserRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\CourseRepository;
use App\Repositories\GradeRepository;

$userRepository = new UserRepository();
$assignmentRepository = new AssignmentRepository();
$courseRepository = new CourseRepository();
$gradeRepository = new GradeRepository();

$authService = new AuthService($userRepository);
$assignmentService = new AssignmentService($assignmentRepository, $courseRepository);
$courseService = new CourseService($courseRepository, $userRepository);

// Require teacher authentication
$authService->requireRole('teacher');
$currentUser = $authService->getCurrentUser();

// Get assignment ID from URL
$assignmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$assignmentId) {
    header('Location: /teacher/dashboard.php');
    exit;
}

// Load assignment
$assignment = $assignmentService->findById($assignmentId);
if (!$assignment) {
    header('Location: /teacher/dashboard.php');
    exit;
}

// Load course and verify ownership
$course = $courseService->findById($assignment->getCourseId());
if (!$course || $course->getTeacherId() !== $currentUser->getUserId()) {
    header('Location: /403.php');
    exit;
}

// Check for existing grades
$grades = $gradeRepository->findByAssignmentId($assignmentId);
$hasGrades = count($grades) > 0;

// Handle delete confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === '1') {
    $success = $assignmentService->deleteAssignment($assignmentId);
    if ($success) {
        header('Location: /teacher/course-detail.php?id=' . $course->getCourseId());
        exit;
    } else {
        $error = 'Failed to delete assignment. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Assignment</title>
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

        .assignment-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .assignment-info h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .assignment-info p {
            color: #666;
            margin-bottom: 5px;
        }

        .course-info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .course-info p {
            color: #004085;
            margin: 0;
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
            <h1>Delete Assignment</h1>
            <a href="/teacher/course-detail.php?id=<?php echo $course->getCourseId(); ?>">← Back to Course</a>
        </div>

        <div class="delete-container">
            <h2>⚠️ Delete Assignment</h2>

            <?php if (isset($error)): ?>
                <div class="alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="course-info">
                <p><strong>Course:</strong> <?php echo htmlspecialchars($course->getCourseCode()); ?> - <?php echo htmlspecialchars($course->getCourseName()); ?></p>
            </div>

            <div class="assignment-info">
                <h3><?php echo htmlspecialchars($assignment->getAssignmentName()); ?></h3>
                <p><strong>Max Points:</strong> <?php echo htmlspecialchars($assignment->getMaxPoints()); ?></p>
                <?php if ($assignment->getDueDate()): ?>
                    <p><strong>Due Date:</strong> <?php echo htmlspecialchars($assignment->getDueDate()); ?></p>
                <?php endif; ?>
                <?php if ($assignment->getDescription()): ?>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($assignment->getDescription()); ?></p>
                <?php endif; ?>
            </div>

            <?php if ($hasGrades): ?>
                <div class="warning">
                    <h4>⚠️ Warning: This assignment has existing grades</h4>
                    <p><strong><?php echo count($grades); ?> student(s)</strong> have been graded for this assignment. All grades will be permanently deleted.</p>
                </div>
            <?php endif; ?>

            <div class="confirmation">
                <p><strong>Are you absolutely sure you want to delete this assignment?</strong></p>
                <p>This action cannot be undone. <?php echo $hasGrades ? 'All associated grades will be permanently deleted.' : 'This assignment will be permanently removed.'; ?></p>

                <form method="POST" action="">
                    <input type="hidden" name="confirm" value="1">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">Yes, Delete Assignment</button>
                        <a href="/teacher/course-detail.php?id=<?php echo $course->getCourseId(); ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
