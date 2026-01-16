<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\AuthService;
use App\Services\AssignmentService;
use App\Services\CourseService;
use App\Services\GradeService;
use App\Repositories\UserRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\CourseRepository;
use App\Repositories\GradeRepository;
use App\Repositories\EnrollmentRepository;

$userRepository = new UserRepository();
$assignmentRepository = new AssignmentRepository();
$courseRepository = new CourseRepository();
$gradeRepository = new GradeRepository();
$enrollmentRepository = new EnrollmentRepository();

$authService = new AuthService($userRepository);
$assignmentService = new AssignmentService($assignmentRepository, $courseRepository);
$courseService = new CourseService($courseRepository, $userRepository);
$gradeService = new GradeService($gradeRepository, $assignmentRepository, $enrollmentRepository, $courseRepository);

// Require teacher authentication
$authService->requireRole('teacher');
$currentUser = $authService->getCurrentUser();

// Get assignment ID from URL
$assignmentId = isset($_GET['assignment_id']) ? (int)$_GET['assignment_id'] : 0;
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

// Get enrolled students
$enrollments = $enrollmentRepository->findByCourseId($course->getCourseId());
$studentsData = [];
foreach ($enrollments as $enrollment) {
    $student = $userRepository->findById($enrollment->getStudentId());
    if ($student && $enrollment->getStatus() === 'active') {
        // Check if grade already exists
        $existingGrade = $gradeService->findByStudentAndAssignment($student->getUserId(), $assignmentId);

        $studentsData[] = [
            'student' => $student,
            'grade' => $existingGrade
        ];
    }
}

$errors = [];
$successMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gradesSubmitted = 0;
    $gradesSkipped = 0;

    foreach ($_POST['grades'] ?? [] as $studentId => $gradeData) {
        // Skip if not marked for grading
        if (!isset($gradeData['should_grade']) || $gradeData['should_grade'] !== '1') {
            $gradesSkipped++;
            continue;
        }

        $pointsEarned = trim($gradeData['points_earned'] ?? '');
        $feedback = trim($gradeData['feedback'] ?? '');

        // Skip if no points entered
        if ($pointsEarned === '') {
            $gradesSkipped++;
            continue;
        }

        // Validate points
        if (!is_numeric($pointsEarned) || $pointsEarned < 0 || $pointsEarned > $assignment->getMaxPoints()) {
            $errors[] = "Invalid points for student ID $studentId. Must be between 0 and " . $assignment->getMaxPoints();
            continue;
        }

        // Check if grade exists
        $existingGrade = $gradeService->findByStudentAndAssignment((int)$studentId, $assignmentId);

        if ($existingGrade) {
            // Update existing grade
            $success = $gradeService->updateGrade($existingGrade, [
                'points_earned' => (float)$pointsEarned,
                'feedback' => $feedback
            ]);
        } else {
            // Create new grade
            $grade = $gradeService->createGrade([
                'assignment_id' => $assignmentId,
                'student_id' => (int)$studentId,
                'points_earned' => (float)$pointsEarned,
                'feedback' => $feedback
            ]);
            $success = ($grade !== null);
        }

        if ($success) {
            $gradesSubmitted++;
        }
    }

    if ($gradesSubmitted > 0) {
        $successMessage = "Successfully saved $gradesSubmitted grade(s).";
    }

    // Reload grades data
    $studentsData = [];
    foreach ($enrollments as $enrollment) {
        $student = $userRepository->findById($enrollment->getStudentId());
        if ($student && $enrollment->getStatus() === 'active') {
            $existingGrade = $gradeService->findByStudentAndAssignment($student->getUserId(), $assignmentId);
            $studentsData[] = [
                'student' => $student,
                'grade' => $existingGrade
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Grades</title>
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

        .assignment-info {
            background: white;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .assignment-info h2 {
            color: #667eea;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .assignment-info p {
            color: #666;
            margin-bottom: 5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .info-item {
            color: #666;
        }

        .info-item strong {
            color: #333;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .grades-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .grades-form h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
        }

        .instructions {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: #004085;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            color: #333;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        tr:hover {
            background: #f8f9fa;
        }

        input[type="number"],
        input[type="text"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        textarea {
            resize: vertical;
            min-height: 60px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .graded-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #d4edda;
            color: #155724;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state h3 {
            color: #333;
            margin-bottom: 10px;
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
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Assign Grades</h1>
            <a href="/teacher/course-detail.php?id=<?php echo $course->getCourseId(); ?>">← Back to Course</a>
        </div>

        <div class="assignment-info">
            <h2><?php echo htmlspecialchars($assignment->getAssignmentName()); ?></h2>
            <p><?php echo htmlspecialchars($course->getCourseCode()); ?> - <?php echo htmlspecialchars($course->getCourseName()); ?></p>

            <div class="info-grid">
                <div class="info-item">
                    <strong>Max Points:</strong> <?php echo htmlspecialchars($assignment->getMaxPoints()); ?>
                </div>
                <?php if ($assignment->getDueDate()): ?>
                    <div class="info-item">
                        <strong>Due Date:</strong> <?php echo htmlspecialchars($assignment->getDueDate()); ?>
                    </div>
                <?php endif; ?>
                <div class="info-item">
                    <strong>Enrolled Students:</strong> <?php echo count($studentsData); ?>
                </div>
            </div>
        </div>

        <div class="grades-form">
            <?php if ($successMessage): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Errors:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <h3>Student Grades</h3>

            <div class="instructions">
                <p><strong>Instructions:</strong> Check the box next to each student you want to grade, enter the points earned (0 - <?php echo $assignment->getMaxPoints(); ?>), and optionally add feedback. Click "Save All Grades" when done.</p>
            </div>

            <?php if (empty($studentsData)): ?>
                <div class="empty-state">
                    <h3>No Students Enrolled</h3>
                    <p>There are no active students enrolled in this course.</p>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 50px;">Grade</th>
                                <th>Student Name</th>
                                <th>Student Number</th>
                                <th style="width: 120px;">Points Earned</th>
                                <th>Feedback</th>
                                <th style="width: 100px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($studentsData as $data): ?>
                                <?php
                                $student = $data['student'];
                                $grade = $data['grade'];
                                $isGraded = ($grade !== null);
                                ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <input
                                            type="checkbox"
                                            name="grades[<?php echo $student->getUserId(); ?>][should_grade]"
                                            value="1"
                                            <?php echo $isGraded ? '' : 'checked'; ?>
                                        >
                                    </td>
                                    <td><?php echo htmlspecialchars($student->getFullName()); ?></td>
                                    <td><?php echo htmlspecialchars($student->getStudentNumber() ?? 'N/A'); ?></td>
                                    <td>
                                        <input
                                            type="number"
                                            name="grades[<?php echo $student->getUserId(); ?>][points_earned]"
                                            value="<?php echo $isGraded ? htmlspecialchars($grade->getPointsEarned()) : ''; ?>"
                                            min="0"
                                            max="<?php echo $assignment->getMaxPoints(); ?>"
                                            step="0.01"
                                            placeholder="0 - <?php echo $assignment->getMaxPoints(); ?>"
                                        >
                                    </td>
                                    <td>
                                        <textarea
                                            name="grades[<?php echo $student->getUserId(); ?>][feedback]"
                                            placeholder="Optional feedback..."
                                        ><?php echo $isGraded ? htmlspecialchars($grade->getFeedback() ?? '') : ''; ?></textarea>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($isGraded): ?>
                                            <span class="graded-badge">Graded</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save All Grades</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
