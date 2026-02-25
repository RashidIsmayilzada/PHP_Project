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
