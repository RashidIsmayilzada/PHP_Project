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

// Get filter (assignment filter)
$filterAssignmentId = isset($_GET['assignment']) ? (int)$_GET['assignment'] : 0;

// Get all assignments for this course
$assignments = $assignmentRepository->findByCourseId($courseId);

// Get all grades for this course
$allGrades = $gradeRepository->findByCourseId($courseId);

// Organize grades by student and assignment
$gradesData = [];
$studentIds = [];

foreach ($allGrades as $grade) {
    if ($filterAssignmentId && $grade->getAssignmentId() !== $filterAssignmentId) {
        continue; // Skip if filtering by assignment
    }

    $studentIds[$grade->getStudentId()] = true;

    $assignment = $assignmentRepository->findById($grade->getAssignmentId());
    $student = $userRepository->findById($grade->getStudentId());

    if ($student && $assignment) {
        $percentage = ($grade->getPointsEarned() / $assignment->getMaxPoints()) * 100;

        $gradesData[] = [
            'grade' => $grade,
            'student' => $student,
            'assignment' => $assignment,
            'percentage' => $percentage
        ];
    }
}

// Calculate course averages for each student
$studentAverages = [];
foreach (array_keys($studentIds) as $studentId) {
    $average = $gradeService->calculateCourseAverage($courseId, $studentId);
    if ($average !== null) {
        $studentAverages[$studentId] = $average;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Grades - <?php echo htmlspecialchars($course->getCourseCode()); ?></title>
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
            max-width: 1400px;
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
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .course-header h2 {
            color: #667eea;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .course-header p {
            color: #666;
        }

        .controls {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-group label {
            color: #333;
            font-weight: 500;
        }

        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            min-width: 200px;
        }

        .grades-table {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .grades-table h3 {
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
            position: sticky;
            top: 0;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            color: #666;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .grade-percentage {
            font-weight: 600;
            color: #333;
        }

        .grade-a { color: #28a745; }
        .grade-b { color: #17a2b8; }
        .grade-c { color: #ffc107; }
        .grade-d { color: #fd7e14; }
        .grade-f { color: #dc3545; }

        .feedback-preview {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
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
            margin-bottom: 15px;
            font-size: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .summary-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            text-align: center;
        }

        .summary-value {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .summary-label {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Course Grades</h1>
            <a href="/teacher/course-detail.php?id=<?php echo $courseId; ?>">← Back to Course</a>
        </div>

        <div class="course-header">
            <h2><?php echo htmlspecialchars($course->getCourseCode()); ?> - <?php echo htmlspecialchars($course->getCourseName()); ?></h2>
            <p>Semester: <?php echo htmlspecialchars($course->getSemester() ?? 'N/A'); ?></p>
        </div>

        <?php if (!empty($studentAverages)): ?>
            <div class="summary-section">
                <h3>Course Statistics</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-value"><?php echo count($gradesData); ?></div>
                        <div class="summary-label">Total Grades</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value"><?php echo count(array_unique(array_column($gradesData, 'student'))); ?></div>
                        <div class="summary-label">Students Graded</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value"><?php echo number_format(array_sum($studentAverages) / count($studentAverages), 1); ?>%</div>
                        <div class="summary-label">Class Average</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="controls">
            <div class="filter-group">
                <label for="assignment-filter">Filter by Assignment:</label>
                <select id="assignment-filter" onchange="window.location.href='?course_id=<?php echo $courseId; ?>&assignment=' + this.value">
                    <option value="0">All Assignments</option>
                    <?php foreach ($assignments as $assignment): ?>
                        <option value="<?php echo $assignment->getAssignmentId(); ?>"
                                <?php echo $filterAssignmentId === $assignment->getAssignmentId() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($assignment->getAssignmentName()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grades-table">
            <h3>Grade Details</h3>

            <?php if (empty($gradesData)): ?>
                <div class="empty-state">
                    <h3>No Grades Yet</h3>
                    <p>No grades have been assigned for <?php echo $filterAssignmentId ? 'this assignment' : 'this course'; ?> yet.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Student Number</th>
                            <th>Assignment</th>
                            <th>Points Earned / Max</th>
                            <th>Percentage</th>
                            <th>Letter Grade</th>
                            <th>Feedback</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gradesData as $data): ?>
                            <?php
                            $grade = $data['grade'];
                            $student = $data['student'];
                            $assignment = $data['assignment'];
                            $percentage = $data['percentage'];
                            $letterGrade = $gradeService->percentageToLetterGrade($percentage);

                            // Determine grade color class
                            $gradeClass = 'grade-' . strtolower($letterGrade);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student->getFullName()); ?></td>
                                <td><?php echo htmlspecialchars($student->getStudentNumber() ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($assignment->getAssignmentName()); ?></td>
                                <td><?php echo $grade->getPointsEarned(); ?> / <?php echo $assignment->getMaxPoints(); ?></td>
                                <td class="grade-percentage <?php echo $gradeClass; ?>">
                                    <?php echo number_format($percentage, 1); ?>%
                                </td>
                                <td class="<?php echo $gradeClass; ?>">
                                    <strong><?php echo $letterGrade; ?></strong>
                                </td>
                                <td>
                                    <?php if ($grade->getFeedback()): ?>
                                        <div class="feedback-preview" title="<?php echo htmlspecialchars($grade->getFeedback()); ?>">
                                            <?php echo htmlspecialchars($grade->getFeedback()); ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #999;">No feedback</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/teacher/grade-edit.php?id=<?php echo $grade->getGradeId(); ?>"
                                       class="btn btn-sm btn-primary">Edit</a>
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
