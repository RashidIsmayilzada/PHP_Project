<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\AuthService;
use App\Services\CourseService;
use App\Repositories\UserRepository;
use App\Repositories\CourseRepository;

$userRepository = new UserRepository();
$courseRepository = new CourseRepository();

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

$errors = [];
$formData = [
    'course_code' => $course->getCourseCode(),
    'course_name' => $course->getCourseName(),
    'description' => $course->getDescription() ?? '',
    'credits' => $course->getCredits(),
    'semester' => $course->getSemester() ?? ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'course_code' => trim($_POST['course_code'] ?? ''),
        'course_name' => trim($_POST['course_name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'credits' => $_POST['credits'] ?? '',
        'semester' => trim($_POST['semester'] ?? '')
    ];

    // Validation
    if (empty($formData['course_code'])) {
        $errors['course_code'] = 'Course code is required';
    }
    if (empty($formData['course_name'])) {
        $errors['course_name'] = 'Course name is required';
    }
    if (empty($formData['credits']) || !is_numeric($formData['credits']) || $formData['credits'] <= 0) {
        $errors['credits'] = 'Credits must be a positive number';
    }
    if (empty($formData['semester'])) {
        $errors['semester'] = 'Semester is required';
    }

    // If no errors, update course
    if (empty($errors)) {
        $success = $courseService->updateCourse($course, $formData);
        if ($success) {
            header('Location: /teacher/course-detail.php?id=' . $courseId);
            exit;
        } else {
            $errors['general'] = 'Failed to update course. Course code might already exist.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
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

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-container h2 {
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
            <h1>Edit Course</h1>
            <a href="/teacher/course-detail.php?id=<?php echo $courseId; ?>">← Back to Course</a>
        </div>

        <div class="form-container">
            <h2>Course Information</h2>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="course_code">
                        Course Code <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="course_code"
                        name="course_code"
                        value="<?php echo htmlspecialchars($formData['course_code']); ?>"
                        class="<?php echo isset($errors['course_code']) ? 'input-error' : ''; ?>"
                        placeholder="e.g., CS101"
                        required
                    >
                    <?php if (isset($errors['course_code'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['course_code']); ?></div>
                    <?php endif; ?>
                    <div class="help-text">A unique identifier for this course</div>
                </div>

                <div class="form-group">
                    <label for="course_name">
                        Course Name <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="course_name"
                        name="course_name"
                        value="<?php echo htmlspecialchars($formData['course_name']); ?>"
                        class="<?php echo isset($errors['course_name']) ? 'input-error' : ''; ?>"
                        placeholder="e.g., Introduction to Computer Science"
                        required
                    >
                    <?php if (isset($errors['course_name'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['course_name']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="description">
                        Description
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        placeholder="Brief description of the course..."
                    ><?php echo htmlspecialchars($formData['description']); ?></textarea>
                    <div class="help-text">Optional course description</div>
                </div>

                <div class="form-group">
                    <label for="credits">
                        Credits <span class="required">*</span>
                    </label>
                    <input
                        type="number"
                        id="credits"
                        name="credits"
                        value="<?php echo htmlspecialchars($formData['credits']); ?>"
                        class="<?php echo isset($errors['credits']) ? 'input-error' : ''; ?>"
                        min="1"
                        step="1"
                        placeholder="e.g., 3"
                        required
                    >
                    <?php if (isset($errors['credits'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['credits']); ?></div>
                    <?php endif; ?>
                    <div class="help-text">Number of credits for this course</div>
                </div>

                <div class="form-group">
                    <label for="semester">
                        Semester <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="semester"
                        name="semester"
                        value="<?php echo htmlspecialchars($formData['semester']); ?>"
                        class="<?php echo isset($errors['semester']) ? 'input-error' : ''; ?>"
                        placeholder="e.g., Fall 2025"
                        required
                    >
                    <?php if (isset($errors['semester'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['semester']); ?></div>
                    <?php endif; ?>
                    <div class="help-text">The semester this course is being offered</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Course</button>
                    <a href="/teacher/course-detail.php?id=<?php echo $courseId; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
