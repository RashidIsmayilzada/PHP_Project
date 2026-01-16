<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\AuthService;
use App\Services\CourseService;
use App\Services\GradeService;
use App\Repositories\UserRepository;
use App\Repositories\CourseRepository;
use App\Repositories\GradeRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\EnrollmentRepository;

$userRepository = new UserRepository();
$courseRepository = new CourseRepository();
$gradeRepository = new GradeRepository();
$assignmentRepository = new AssignmentRepository();
$enrollmentRepository = new EnrollmentRepository();

$authService = new AuthService($userRepository);
$courseService = new CourseService($courseRepository, $userRepository);
$gradeService = new GradeService($gradeRepository, $assignmentRepository, $enrollmentRepository, $courseRepository);

// Require student authentication
$authService->requireRole('student');
$currentUser = $authService->getCurrentUser();

// Get course ID from URL
$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$courseId) {
    header('Location: /student/dashboard.php');
    exit;
}

// Load course
$course = $courseService->findById($courseId);
if (!$course) {
    header('Location: /student/dashboard.php');
    exit;
}

// Verify student is enrolled in this course
$enrollments = $enrollmentRepository->findByStudentId($currentUser->getUserId());
$isEnrolled = false;
$enrollmentStatus = null;

foreach ($enrollments as $enrollment) {
    if ($enrollment->getCourseId() === $courseId) {
        $isEnrolled = true;
        $enrollmentStatus = $enrollment->getStatus();
        break;
    }
}

if (!$isEnrolled) {
    header('Location: /403.php');
    exit;
}

// Get teacher info
$teacher = $userRepository->findById($course->getTeacherId());

// Get all assignments for this course
$assignments = $assignmentRepository->findByCourseId($courseId);

// Get assignment data with grades
$assignmentsData = [];
$gradedCount = 0;

foreach ($assignments as $assignment) {
    $grade = $gradeService->findByStudentAndAssignment($currentUser->getUserId(), $assignment->getAssignmentId());

    $percentage = null;
    $letterGrade = null;

    if ($grade) {
        $percentage = ($grade->getPointsEarned() / $assignment->getMaxPoints()) * 100;
        $letterGrade = $gradeService->percentageToLetterGrade($percentage);
        $gradedCount++;
    }

    $assignmentsData[] = [
        'assignment' => $assignment,
        'grade' => $grade,
        'percentage' => $percentage,
        'letter_grade' => $letterGrade
    ];
}

// Calculate course average
$courseAverage = $gradeService->calculateCourseAverage($courseId, $currentUser->getUserId());
$courseLetterGrade = $courseAverage !== null ? $gradeService->percentageToLetterGrade($courseAverage) : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course->getCourseCode()); ?> - Course Details</title>
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

        .course-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .course-title {
            margin-bottom: 15px;
        }

        .course-code {
            color: #667eea;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .course-name {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .course-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .course-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .info-item {
            color: #666;
        }

        .info-item strong {
            display: block;
            color: #333;
            margin-bottom: 3px;
        }

        .summary-section {
            background: white;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .summary-section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .summary-card {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }

        .summary-value {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .summary-label {
            color: #666;
            font-size: 14px;
        }

        .grade-a { color: #28a745; }
        .grade-b { color: #17a2b8; }
        .grade-c { color: #ffc107; }
        .grade-d { color: #fd7e14; }
        .grade-f { color: #dc3545; }

        .assignments-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .assignments-section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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
            color: #666;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .not-graded {
            color: #999;
            font-style: italic;
        }

        .grade-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state h4 {
            color: #333;
            margin-bottom: 10px;
        }

        .feedback-box {
            background: #e7f3ff;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 5px;
            font-size: 14px;
            color: #004085;
        }

        .feedback-label {
            font-weight: 600;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Course Details</h1>
            <a href="/student/dashboard.php">← Back to Dashboard</a>
        </div>

        <!-- Course Header -->
        <div class="course-header">
            <div class="course-title">
                <div class="course-code"><?php echo htmlspecialchars($course->getCourseCode()); ?></div>
                <div class="course-name"><?php echo htmlspecialchars($course->getCourseName()); ?></div>
            </div>

            <?php if ($course->getDescription()): ?>
                <div class="course-description">
                    <?php echo htmlspecialchars($course->getDescription()); ?>
                </div>
            <?php endif; ?>

            <div class="course-info-grid">
                <div class="info-item">
                    <strong>Teacher</strong>
                    <?php echo htmlspecialchars($teacher ? $teacher->getFullName() : 'Unknown'); ?>
                </div>
                <div class="info-item">
                    <strong>Semester</strong>
                    <?php echo htmlspecialchars($course->getSemester() ?? 'N/A'); ?>
                </div>
                <div class="info-item">
                    <strong>Credits</strong>
                    <?php echo htmlspecialchars($course->getCredits() ?? 'N/A'); ?>
                </div>
                <div class="info-item">
                    <strong>Status</strong>
                    <?php echo ucfirst($enrollmentStatus); ?>
                </div>
            </div>
        </div>

        <!-- Course Summary -->
        <div class="summary-section">
            <h3>Course Summary</h3>
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-value <?php echo $courseAverage !== null ? 'grade-' . strtolower($courseLetterGrade) : ''; ?>">
                        <?php echo $courseAverage !== null ? number_format($courseAverage, 1) . '%' : 'N/A'; ?>
                    </div>
                    <div class="summary-label">Course Average</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value <?php echo $courseAverage !== null ? 'grade-' . strtolower($courseLetterGrade) : ''; ?>">
                        <?php echo $courseLetterGrade; ?>
                    </div>
                    <div class="summary-label">Letter Grade</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value"><?php echo count($assignments); ?></div>
                    <div class="summary-label">Total Assignments</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value"><?php echo $gradedCount; ?></div>
                    <div class="summary-label">Graded Assignments</div>
                </div>
            </div>
        </div>

        <!-- Assignments & Grades -->
        <div class="assignments-section">
            <h3>Assignments & Grades</h3>

            <?php if (empty($assignmentsData)): ?>
                <div class="empty-state">
                    <h4>No Assignments Yet</h4>
                    <p>No assignments have been created for this course yet.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Assignment Name</th>
                            <th>Due Date</th>
                            <th>Max Points</th>
                            <th>Points Earned</th>
                            <th>Percentage</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignmentsData as $data): ?>
                            <?php
                            $assignment = $data['assignment'];
                            $grade = $data['grade'];
                            $percentage = $data['percentage'];
                            $letterGrade = $data['letter_grade'];
                            $gradeClass = $letterGrade ? 'grade-' . strtolower($letterGrade) : '';
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($assignment->getAssignmentName()); ?></strong>
                                    <?php if ($assignment->getDescription()): ?>
                                        <div style="font-size: 13px; color: #999; margin-top: 3px;">
                                            <?php echo htmlspecialchars($assignment->getDescription()); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $assignment->getDueDate() ? date('M d, Y', strtotime($assignment->getDueDate())) : 'No due date'; ?>
                                </td>
                                <td><?php echo $assignment->getMaxPoints(); ?></td>
                                <td>
                                    <?php if ($grade): ?>
                                        <strong><?php echo $grade->getPointsEarned(); ?></strong>
                                    <?php else: ?>
                                        <span class="not-graded">Not Graded</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($grade): ?>
                                        <span class="<?php echo $gradeClass; ?>">
                                            <strong><?php echo number_format($percentage, 1); ?>%</strong>
                                        </span>
                                    <?php else: ?>
                                        <span class="not-graded">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($grade): ?>
                                        <span class="grade-badge <?php echo $gradeClass; ?>">
                                            <?php echo $letterGrade; ?>
                                        </span>
                                        <?php if ($grade->getFeedback()): ?>
                                            <div class="feedback-box">
                                                <div class="feedback-label">Feedback:</div>
                                                <?php echo htmlspecialchars($grade->getFeedback()); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="not-graded">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
