<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\AuthService;
use App\Services\CourseService;
use App\Services\EnrollmentService;
use App\Repositories\UserRepository;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;

$userRepository = new UserRepository();
$courseRepository = new CourseRepository();
$enrollmentRepository = new EnrollmentRepository();

$authService = new AuthService($userRepository);
$courseService = new CourseService($courseRepository, $userRepository);
$enrollmentService = new EnrollmentService($enrollmentRepository, $userRepository, $courseRepository);

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

$message = '';
$error = '';

// Handle enrollment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'enroll' && isset($_POST['student_id'])) {
        $studentId = (int)$_POST['student_id'];
        $enrollment = $enrollmentService->enrollStudent($studentId, $courseId);

        if ($enrollment) {
            $message = 'Student enrolled successfully!';
        } else {
            $error = 'Failed to enroll student. They may already be enrolled.';
        }
    } elseif ($action === 'unenroll' && isset($_POST['enrollment_id'])) {
        $enrollmentId = (int)$_POST['enrollment_id'];
        $success = $enrollmentService->deleteEnrollment($enrollmentId);

        if ($success) {
            $message = 'Student unenrolled successfully!';
        } else {
            $error = 'Failed to unenroll student.';
        }
    } elseif ($action === 'bulk_enroll' && isset($_POST['student_ids'])) {
        $studentIds = $_POST['student_ids'];
        $enrolledCount = 0;

        foreach ($studentIds as $studentId) {
            $enrollment = $enrollmentService->enrollStudent((int)$studentId, $courseId);
            if ($enrollment) {
                $enrolledCount++;
            }
        }

        $message = "Successfully enrolled $enrolledCount student(s)!";
    }
}

// Get all enrollments for this course
$enrollments = $enrollmentRepository->findByCourseId($courseId);
$enrolledStudentIds = [];
$enrolledStudentsData = [];

foreach ($enrollments as $enrollment) {
    $student = $userRepository->findById($enrollment->getStudentId());
    if ($student) {
        $enrolledStudentIds[] = $student->getUserId();
        $enrolledStudentsData[] = [
            'enrollment' => $enrollment,
            'student' => $student
        ];
    }
}

// Get all students
$allStudents = $userRepository->findAllStudents();

// Filter out already enrolled students
$availableStudents = array_filter($allStudents, function($student) use ($enrolledStudentIds) {
    return !in_array($student->getUserId(), $enrolledStudentIds);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Enrollments</title>
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

        .course-info {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .course-info h2 {
            color: #667eea;
            font-size: 22px;
            margin-bottom: 5px;
        }

        .course-info p {
            color: #666;
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

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state h4 {
            color: #333;
            margin-bottom: 10px;
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

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .bulk-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-box {
            margin-bottom: 15px;
        }

        .search-box input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Manage Enrollments</h1>
            <a href="/teacher/course-detail.php?id=<?php echo $courseId; ?>">← Back to Course</a>
        </div>

        <div class="course-info">
            <h2><?php echo htmlspecialchars($course->getCourseCode()); ?> - <?php echo htmlspecialchars($course->getCourseName()); ?></h2>
            <p>Semester: <?php echo htmlspecialchars($course->getSemester() ?? 'N/A'); ?> | Credits: <?php echo htmlspecialchars($course->getCredits() ?? 'N/A'); ?></p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Enrolled Students Section -->
        <div class="section">
            <div class="section-header">
                <h3>Enrolled Students (<?php echo count($enrolledStudentsData); ?>)</h3>
            </div>

            <?php if (empty($enrolledStudentsData)): ?>
                <div class="empty-state">
                    <h4>No Students Enrolled</h4>
                    <p>No students are currently enrolled in this course.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Student Number</th>
                            <th>Status</th>
                            <th>Enrolled Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrolledStudentsData as $data): ?>
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
                                <td><?php echo date('M d, Y', strtotime($enrollment->getEnrollmentDate())); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="unenroll">
                                        <input type="hidden" name="enrollment_id" value="<?php echo $enrollment->getEnrollmentId(); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to unenroll this student?')">
                                            Unenroll
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Available Students Section -->
        <div class="section">
            <div class="section-header">
                <h3>Available Students (<?php echo count($availableStudents); ?>)</h3>
            </div>

            <?php if (empty($availableStudents)): ?>
                <div class="empty-state">
                    <h4>No Available Students</h4>
                    <p>All students are already enrolled in this course.</p>
                </div>
            <?php else: ?>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search by name, email, or student number..." onkeyup="filterStudents()">
                </div>

                <form method="POST" id="bulkEnrollForm">
                    <input type="hidden" name="action" value="bulk_enroll">
                    <table id="studentsTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Select</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Student Number</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($availableStudents as $student): ?>
                                <tr class="student-row"
                                    data-name="<?php echo htmlspecialchars(strtolower($student->getFullName())); ?>"
                                    data-email="<?php echo htmlspecialchars(strtolower($student->getEmail())); ?>"
                                    data-number="<?php echo htmlspecialchars(strtolower($student->getStudentNumber() ?? '')); ?>">
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="student_ids[]" value="<?php echo $student->getUserId(); ?>" class="student-checkbox">
                                    </td>
                                    <td><?php echo htmlspecialchars($student->getFullName()); ?></td>
                                    <td><?php echo htmlspecialchars($student->getEmail()); ?></td>
                                    <td><?php echo htmlspecialchars($student->getStudentNumber() ?? 'N/A'); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="enroll">
                                            <input type="hidden" name="student_id" value="<?php echo $student->getUserId(); ?>">
                                            <button type="submit" class="btn btn-sm btn-success">Enroll</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="bulk-actions">
                        <div>
                            <span id="selectedCount">0</span> student(s) selected
                        </div>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Enroll selected students?')">
                            Enroll Selected
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.student-checkbox:checked');
            document.getElementById('selectedCount').textContent = checkboxes.length;
        }

        document.querySelectorAll('.student-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });

        function filterStudents() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.student-row');

            rows.forEach(row => {
                const name = row.dataset.name;
                const email = row.dataset.email;
                const number = row.dataset.number;

                if (name.includes(searchTerm) || email.includes(searchTerm) || number.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
