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

// Get enrollments
$enrollments = $enrollmentRepository->findByCourseId($courseId);
$enrolledStudents = [];
foreach ($enrollments as $enrollment) {
    $student = $userRepository->findById($enrollment->getStudentId());
    if ($student) {
        $enrolledStudents[] = [
            'enrollment' => $enrollment,
            'student' => $student
        ];
    }
}

// Get assignments
$assignments = $assignmentRepository->findByCourseId($courseId);

// Get teacher info
$teacher = $userRepository->findById($course->getTeacherId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Details - <?php echo htmlspecialchars($course->getCourseCode()); ?></title>
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
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }

        .course-title h2 {
            color: #667eea;
            font-size: 32px;
            margin-bottom: 5px;
        }

        .course-title h3 {
            color: #333;
            font-size: 24px;
            font-weight: 500;
        }

        .course-actions {
            display: flex;
            gap: 10px;
        }

        .course-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .info-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }

        .section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h3 {
            color: #333;
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

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-block;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-dropped {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Course Details</h1>
            <a href="/teacher/dashboard.php">← Back to Dashboard</a>
        </div>

        <!-- Course Header -->
        <div class="course-header">
            <div class="course-title">
                <div>
                    <h2><?php echo htmlspecialchars($course->getCourseCode()); ?></h2>
                    <h3><?php echo htmlspecialchars($course->getCourseName()); ?></h3>
                </div>
                <div class="course-actions">
                    <a href="/teacher/course-edit.php?id=<?php echo $courseId; ?>" class="btn btn-secondary">Edit Course</a>
                    <a href="/teacher/course-delete.php?id=<?php echo $courseId; ?>"
                       class="btn btn-danger"
                       onclick="return confirm('Are you sure you want to delete this course?')">Delete Course</a>
                </div>
            </div>

            <?php if ($course->getDescription()): ?>
                <p style="color: #666; margin-bottom: 20px;"><?php echo htmlspecialchars($course->getDescription()); ?></p>
            <?php endif; ?>

            <div class="course-info">
                <div class="info-item">
                    <div class="info-label">Semester</div>
                    <div class="info-value"><?php echo htmlspecialchars($course->getSemester() ?? 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Credits</div>
                    <div class="info-value"><?php echo htmlspecialchars($course->getCredits() ?? 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Teacher</div>
                    <div class="info-value"><?php echo htmlspecialchars($teacher ? $teacher->getFullName() : 'Unknown'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Enrolled Students</div>
                    <div class="info-value"><?php echo count($enrolledStudents); ?></div>
                </div>
            </div>
        </div>

        <!-- Enrolled Students Section -->
        <div class="section">
            <div class="section-header">
                <h3>Enrolled Students</h3>
                <a href="/teacher/course-enroll.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary">Manage Enrollments</a>
            </div>

            <?php if (empty($enrolledStudents)): ?>
                <div class="empty-state">
                    <p>No students enrolled yet.</p>
                    <a href="/teacher/course-enroll.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary">Enroll Students</a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Student Number</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrolledStudents as $data): ?>
                            <?php
                            $student = $data['student'];
                            $enrollment = $data['enrollment'];
                            $status = $enrollment->getStatus();
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student->getFullName()); ?></td>
                                <td><?php echo htmlspecialchars($student->getEmail()); ?></td>
                                <td><?php echo htmlspecialchars($student->getStudentNumber() ?? 'N/A'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $status; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Assignments Section -->
        <div class="section">
            <div class="section-header">
                <h3>Assignments</h3>
                <a href="/teacher/assignment-create.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary">+ Create Assignment</a>
            </div>

            <?php if (empty($assignments)): ?>
                <div class="empty-state">
                    <p>No assignments created yet.</p>
                    <a href="/teacher/assignment-create.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary">Create First Assignment</a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Assignment Name</th>
                            <th>Max Points</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $assignment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($assignment->getAssignmentName()); ?></td>
                                <td><?php echo htmlspecialchars($assignment->getMaxPoints()); ?></td>
                                <td><?php echo htmlspecialchars($assignment->getDueDate() ?? 'No due date'); ?></td>
                                <td>
                                    <a href="/teacher/grade-assign.php?assignment_id=<?php echo $assignment->getAssignmentId(); ?>" class="btn btn-sm btn-primary">Grade</a>
                                    <a href="/teacher/assignment-edit.php?id=<?php echo $assignment->getAssignmentId(); ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <a href="/teacher/assignment-delete.php?id=<?php echo $assignment->getAssignmentId(); ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Delete this assignment?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Grades Overview Section -->
        <div class="section">
            <div class="section-header">
                <h3>Grades Overview</h3>
                <a href="/teacher/course-grades.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary">View All Grades</a>
            </div>
            <p style="color: #666;">View and manage all grades for this course.</p>
        </div>
    </div>
</body>
</html>
