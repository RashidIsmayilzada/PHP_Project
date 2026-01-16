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

// Get enrolled courses
$enrolledCourses = $courseService->getCoursesForStudent($currentUser->getUserId());

// Calculate overall GPA
$overallGPA = $gradeService->calculateOverallGPA($currentUser->getUserId());

// Get course data with averages
$coursesData = [];
foreach ($enrolledCourses as $course) {
    $courseAverage = $gradeService->calculateCourseAverage($course->getCourseId(), $currentUser->getUserId());
    $letterGrade = $courseAverage !== null ? $gradeService->percentageToLetterGrade($courseAverage) : 'N/A';

    // Get teacher info
    $teacher = $userRepository->findById($course->getTeacherId());

    // Get enrollment status
    $enrollments = $enrollmentRepository->findByStudentId($currentUser->getUserId());
    $enrollmentStatus = 'unknown';
    foreach ($enrollments as $enrollment) {
        if ($enrollment->getCourseId() === $course->getCourseId()) {
            $enrollmentStatus = $enrollment->getStatus();
            break;
        }
    }

    $coursesData[] = [
        'course' => $course,
        'average' => $courseAverage,
        'letter_grade' => $letterGrade,
        'teacher' => $teacher,
        'status' => $enrollmentStatus
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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

        .user-info {
            color: #666;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info a {
            color: #667eea;
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid #667eea;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .user-info a:hover {
            background: #667eea;
            color: white;
        }

        .gpa-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
            margin-bottom: 30px;
            text-align: center;
            color: white;
        }

        .gpa-section h2 {
            font-size: 18px;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .gpa-display {
            font-size: 72px;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .gpa-scale {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .stats-link {
            display: inline-block;
            background: white;
            color: #667eea;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .stats-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,255,255,0.3);
        }

        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
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

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .course-card {
            border: 2px solid #f0f0f0;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s;
        }

        .course-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .course-header {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .course-code {
            color: #667eea;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .course-name {
            color: #333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .course-teacher {
            color: #666;
            font-size: 14px;
        }

        .course-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 15px 0;
        }

        .course-stat {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .course-stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }

        .course-stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 3px;
        }

        .grade-a { color: #28a745; }
        .grade-b { color: #17a2b8; }
        .grade-c { color: #ffc107; }
        .grade-d { color: #fd7e14; }
        .grade-f { color: #dc3545; }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 10px;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            text-align: center;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            margin-top: 15px;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #5568d3;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 22px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Student Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <strong><?php echo htmlspecialchars($currentUser->getFullName()); ?></strong></span>
                <a href="/logout">Logout</a>
            </div>
        </div>

        <!-- GPA Section -->
        <div class="gpa-section">
            <h2>Your Overall GPA</h2>
            <div class="gpa-display"><?php echo number_format($overallGPA, 2); ?></div>
            <div class="gpa-scale">out of 4.0</div>
            <a href="/student/statistics.php" class="stats-link">View Detailed Statistics →</a>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($enrolledCourses); ?></div>
                <div class="stat-label">Enrolled Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php
                    $activeCount = 0;
                    foreach ($coursesData as $data) {
                        if ($data['status'] === 'active') $activeCount++;
                    }
                    echo $activeCount;
                    ?>
                </div>
                <div class="stat-label">Active Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php
                    $totalCredits = 0;
                    foreach ($enrolledCourses as $course) {
                        $totalCredits += $course->getCredits() ?? 0;
                    }
                    echo $totalCredits;
                    ?>
                </div>
                <div class="stat-label">Total Credits</div>
            </div>
        </div>

        <!-- Enrolled Courses Section -->
        <div class="section">
            <div class="section-header">
                <h3>My Courses</h3>
            </div>

            <?php if (empty($coursesData)): ?>
                <div class="empty-state">
                    <h3>No Courses Enrolled</h3>
                    <p>You are not currently enrolled in any courses.</p>
                </div>
            <?php else: ?>
                <div class="courses-grid">
                    <?php foreach ($coursesData as $data): ?>
                        <?php
                        $course = $data['course'];
                        $average = $data['average'];
                        $letterGrade = $data['letter_grade'];
                        $teacher = $data['teacher'];
                        $status = $data['status'];
                        $gradeClass = 'grade-' . strtolower($letterGrade);
                        ?>
                        <div class="course-card">
                            <div class="course-header">
                                <div class="course-code"><?php echo htmlspecialchars($course->getCourseCode()); ?></div>
                                <div class="course-name"><?php echo htmlspecialchars($course->getCourseName()); ?></div>
                                <div class="course-teacher">
                                    <?php echo htmlspecialchars($teacher ? $teacher->getFullName() : 'Unknown Teacher'); ?>
                                </div>
                            </div>

                            <div class="course-stats">
                                <div class="course-stat">
                                    <div class="course-stat-value <?php echo $average !== null ? $gradeClass : ''; ?>">
                                        <?php echo $average !== null ? number_format($average, 1) . '%' : 'N/A'; ?>
                                    </div>
                                    <div class="course-stat-label">Course Average</div>
                                </div>
                                <div class="course-stat">
                                    <div class="course-stat-value <?php echo $average !== null ? $gradeClass : ''; ?>">
                                        <?php echo $letterGrade; ?>
                                    </div>
                                    <div class="course-stat-label">Letter Grade</div>
                                </div>
                            </div>

                            <div style="margin-top: 10px;">
                                <span><strong>Semester:</strong> <?php echo htmlspecialchars($course->getSemester() ?? 'N/A'); ?></span><br>
                                <span><strong>Credits:</strong> <?php echo htmlspecialchars($course->getCredits() ?? 'N/A'); ?></span>
                            </div>

                            <span class="status-badge status-<?php echo $status; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>

                            <a href="/student/course-detail.php?id=<?php echo $course->getCourseId(); ?>" class="btn">
                                View Course Details
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
