<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\AuthService;
use App\Services\AssignmentService;
use App\Services\CourseService;
use App\Repositories\UserRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\CourseRepository;

$userRepository = new UserRepository();
$assignmentRepository = new AssignmentRepository();
$courseRepository = new CourseRepository();

$authService = new AuthService($userRepository);
$assignmentService = new AssignmentService($assignmentRepository, $courseRepository);
$courseService = new CourseService($courseRepository, $userRepository);

// Require teacher authentication
$authService->requireRole('teacher');
$currentUser = $authService->getCurrentUser();

// Get course ID from URL
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
if (!$courseId) {
    header('Location: /teacher/dashboard.php');
    exit;
}

// Load course and verify ownership
$course = $courseService->findById($courseId);
if (!$course || $course->getTeacherId() !== $currentUser->getUserId()) {
    header('Location: /403.php');
    exit;
}

$errors = [];
$formData = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'course_id' => $courseId,
        'assignment_name' => trim($_POST['assignment_name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'max_points' => $_POST['max_points'] ?? '',
        'due_date' => trim($_POST['due_date'] ?? '')
    ];

    // Validation
    if (empty($formData['assignment_name'])) {
        $errors['assignment_name'] = 'Assignment name is required';
    }
    if (empty($formData['max_points']) || !is_numeric($formData['max_points']) || $formData['max_points'] <= 0) {
        $errors['max_points'] = 'Max points must be a positive number';
    }

    // If no errors, create assignment
    if (empty($errors)) {
        $assignment = $assignmentService->createAssignment($formData);
        if ($assignment) {
            header('Location: /teacher/course-detail.php?id=' . $courseId);
            exit;
        } else {
            $errors['general'] = 'Failed to create assignment. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assignment</title>
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
            max-width: 800px;
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

        .course-info {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .course-info h2 {
            color: #667eea;
            font-size: 20px;
            margin-bottom: 5px;
        }

        .course-info p {
            color: #666;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-container h3 {
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            color: #333;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .required {
            color: #dc3545;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }

        .input-error {
            border-color: #dc3545;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
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

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            flex: 1;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .help-text {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create New Assignment</h1>
            <a href="/teacher/course-detail.php?id=<?php echo $courseId; ?>">← Back to Course</a>
        </div>

        <div class="course-info">
            <h2><?php echo htmlspecialchars($course->getCourseCode()); ?> - <?php echo htmlspecialchars($course->getCourseName()); ?></h2>
            <p>Semester: <?php echo htmlspecialchars($course->getSemester() ?? 'N/A'); ?></p>
        </div>

        <div class="form-container">
            <h3>Assignment Details</h3>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="assignment_name">
                        Assignment Name <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="assignment_name"
                        name="assignment_name"
                        value="<?php echo htmlspecialchars($formData['assignment_name'] ?? ''); ?>"
                        class="<?php echo isset($errors['assignment_name']) ? 'input-error' : ''; ?>"
                        placeholder="e.g., Midterm Exam, Homework 1, Final Project"
                        required
                    >
                    <?php if (isset($errors['assignment_name'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['assignment_name']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="description">
                        Description
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        placeholder="Brief description of the assignment, requirements, and expectations..."
                    ><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
                    <div class="help-text">Optional assignment description</div>
                </div>

                <div class="form-group">
                    <label for="max_points">
                        Maximum Points <span class="required">*</span>
                    </label>
                    <input
                        type="number"
                        id="max_points"
                        name="max_points"
                        value="<?php echo htmlspecialchars($formData['max_points'] ?? ''); ?>"
                        class="<?php echo isset($errors['max_points']) ? 'input-error' : ''; ?>"
                        min="0.01"
                        step="0.01"
                        placeholder="e.g., 100"
                        required
                    >
                    <?php if (isset($errors['max_points'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['max_points']); ?></div>
                    <?php endif; ?>
                    <div class="help-text">Maximum points a student can earn for this assignment</div>
                </div>

                <div class="form-group">
                    <label for="due_date">
                        Due Date
                    </label>
                    <input
                        type="date"
                        id="due_date"
                        name="due_date"
                        value="<?php echo htmlspecialchars($formData['due_date'] ?? ''); ?>"
                    >
                    <div class="help-text">Optional due date for this assignment</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Assignment</button>
                    <a href="/teacher/course-detail.php?id=<?php echo $courseId; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
