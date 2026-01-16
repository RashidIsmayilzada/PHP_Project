<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\AuthService;
use App\Services\GradeService;
use App\Services\AssignmentService;
use App\Services\CourseService;
use App\Repositories\UserRepository;
use App\Repositories\GradeRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;

$userRepository = new UserRepository();
$gradeRepository = new GradeRepository();
$assignmentRepository = new AssignmentRepository();
$courseRepository = new CourseRepository();
$enrollmentRepository = new EnrollmentRepository();

$authService = new AuthService($userRepository);
$gradeService = new GradeService($gradeRepository, $assignmentRepository, $enrollmentRepository, $courseRepository);
$assignmentService = new AssignmentService($assignmentRepository, $courseRepository);
$courseService = new CourseService($courseRepository, $userRepository);

// Require teacher authentication
$authService->requireRole('teacher');
$currentUser = $authService->getCurrentUser();

// Get grade ID from URL
$gradeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$gradeId) {
    header('Location: /teacher/dashboard.php');
    exit;
}

// Load grade
$grade = $gradeService->findById($gradeId);
if (!$grade) {
    header('Location: /teacher/dashboard.php');
    exit;
}

// Load assignment and course
$assignment = $assignmentService->findById($grade->getAssignmentId());
$course = $courseService->findById($assignment->getCourseId());

// Verify teacher owns this course
if (!$course || $course->getTeacherId() !== $currentUser->getUserId()) {
    header('Location: /403.php');
    exit;
}

// Load student
$student = $userRepository->findById($grade->getStudentId());

$errors = [];
$formData = [
    'points_earned' => $grade->getPointsEarned(),
    'feedback' => $grade->getFeedback() ?? ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'points_earned' => trim($_POST['points_earned'] ?? ''),
        'feedback' => trim($_POST['feedback'] ?? '')
    ];

    // Validation
    if (empty($formData['points_earned']) || !is_numeric($formData['points_earned'])) {
        $errors['points_earned'] = 'Points earned is required and must be a number';
    } elseif ($formData['points_earned'] < 0 || $formData['points_earned'] > $assignment->getMaxPoints()) {
        $errors['points_earned'] = 'Points must be between 0 and ' . $assignment->getMaxPoints();
    }

    // If no errors, update grade
    if (empty($errors)) {
        $success = $gradeService->updateGrade($grade, [
            'points_earned' => (float)$formData['points_earned'],
            'feedback' => $formData['feedback']
        ]);

        if ($success) {
            header('Location: /teacher/course-grades.php?course_id=' . $course->getCourseId());
            exit;
        } else {
            $errors['general'] = 'Failed to update grade. Please try again.';
        }
    }
}

// Calculate percentage
$percentage = ($grade->getPointsEarned() / $assignment->getMaxPoints()) * 100;
$letterGrade = $gradeService->percentageToLetterGrade($percentage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Grade</title>
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

        .grade-info {
            background: white;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .grade-info h2 {
            color: #333;
            font-size: 22px;
            margin-bottom: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .info-label {
            color: #666;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .info-value {
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }

        .current-grade {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .current-grade h3 {
            color: #004085;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .grade-display {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 10px;
        }

        .grade-number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
        }

        .grade-details {
            color: #004085;
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

        input[type="number"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
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
            <h1>Edit Grade</h1>
            <a href="/teacher/course-grades.php?course_id=<?php echo $course->getCourseId(); ?>">← Back to Grades</a>
        </div>

        <div class="grade-info">
            <h2>Grade Information</h2>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Student</div>
                    <div class="info-value"><?php echo htmlspecialchars($student ? $student->getFullName() : 'Unknown'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Student Number</div>
                    <div class="info-value"><?php echo htmlspecialchars($student ? ($student->getStudentNumber() ?? 'N/A') : 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Course</div>
                    <div class="info-value"><?php echo htmlspecialchars($course->getCourseCode()); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Assignment</div>
                    <div class="info-value"><?php echo htmlspecialchars($assignment->getAssignmentName()); ?></div>
                </div>
            </div>

            <div class="current-grade">
                <h3>Current Grade</h3>
                <div class="grade-display">
                    <div class="grade-number"><?php echo number_format($percentage, 1); ?>%</div>
                    <div class="grade-details">
                        <div><strong>Letter Grade:</strong> <?php echo $letterGrade; ?></div>
                        <div><strong>Points:</strong> <?php echo $grade->getPointsEarned(); ?> / <?php echo $assignment->getMaxPoints(); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-container">
            <h3>Update Grade</h3>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="points_earned">
                        Points Earned <span class="required">*</span>
                    </label>
                    <input
                        type="number"
                        id="points_earned"
                        name="points_earned"
                        value="<?php echo htmlspecialchars($formData['points_earned']); ?>"
                        class="<?php echo isset($errors['points_earned']) ? 'input-error' : ''; ?>"
                        min="0"
                        max="<?php echo $assignment->getMaxPoints(); ?>"
                        step="0.01"
                        required
                    >
                    <?php if (isset($errors['points_earned'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['points_earned']); ?></div>
                    <?php endif; ?>
                    <div class="help-text">Enter points between 0 and <?php echo $assignment->getMaxPoints(); ?></div>
                </div>

                <div class="form-group">
                    <label for="feedback">
                        Feedback
                    </label>
                    <textarea
                        id="feedback"
                        name="feedback"
                        placeholder="Optional feedback for the student..."
                    ><?php echo htmlspecialchars($formData['feedback']); ?></textarea>
                    <div class="help-text">Provide feedback or comments about this grade</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Grade</button>
                    <a href="/teacher/course-grades.php?course_id=<?php echo $course->getCourseId(); ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
