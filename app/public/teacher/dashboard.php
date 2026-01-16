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

// Get all courses for this teacher
$courses = $courseService->getCoursesForTeacher($currentUser->getUserId());

// Get counts for each course
$courseData = [];
foreach ($courses as $course) {
    $enrollments = $enrollmentRepository->findByCourseId($course->getCourseId());
    $assignments = $assignmentRepository->findByCourseId($course->getCourseId());

    $courseData[] = [
        'course' => $course,
        'enrollment_count' => count($enrollments),
        'assignment_count' => count($assignments)
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
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

        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .welcome-section h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .welcome-section p {
            color: #666;
            font-size: 16px;
        }

        .action-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .action-bar h3 {
            color: #333;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
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

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .course-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .course-header {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .course-code {
            color: #667eea;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .course-name {
            color: #333;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .course-semester {
            color: #666;
            font-size: 14px;
        }

        .course-stats {
            display: flex;
            gap: 20px;
            margin: 15px 0;
        }

        .stat {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .stat-number {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .course-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .course-actions a {
            flex: 1;
            text-align: center;
        }

        .empty-state {
            background: white;
            padding: 60px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .empty-state h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 24px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Teacher Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <strong><?php echo htmlspecialchars($currentUser->getFullName()); ?></strong></span>
                <a href="/logout">Logout</a>
            </div>
        </div>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <h2>Welcome back, <?php echo htmlspecialchars($currentUser->getFirstName()); ?>!</h2>
            <p>Manage your courses, assignments, and grades from your dashboard.</p>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <h3>My Courses</h3>
            <a href="/teacher/course-create.php" class="btn btn-primary">+ Create New Course</a>
        </div>

        <!-- Courses Grid -->
        <?php if (empty($courseData)): ?>
            <div class="empty-state">
                <h3>No Courses Yet</h3>
                <p>You haven't created any courses yet. Click the button below to get started!</p>
                <a href="/teacher/course-create.php" class="btn btn-primary">Create Your First Course</a>
            </div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($courseData as $data): ?>
                    <?php $course = $data['course']; ?>
                    <div class="course-card">
                        <div class="course-header">
                            <div class="course-code"><?php echo htmlspecialchars($course->getCourseCode()); ?></div>
                            <div class="course-name"><?php echo htmlspecialchars($course->getCourseName()); ?></div>
                            <div class="course-semester">Semester: <?php echo htmlspecialchars($course->getSemester() ?? 'N/A'); ?></div>
                        </div>

                        <div class="course-stats">
                            <div class="stat">
                                <span class="stat-number"><?php echo $data['enrollment_count']; ?></span>
                                <span class="stat-label">Students</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number"><?php echo $data['assignment_count']; ?></span>
                                <span class="stat-label">Assignments</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number"><?php echo $course->getCredits() ?? 'N/A'; ?></span>
                                <span class="stat-label">Credits</span>
                            </div>
                        </div>

                        <div class="course-actions">
                            <a href="/teacher/course-detail.php?id=<?php echo $course->getCourseId(); ?>" class="btn btn-secondary">View Details</a>
                            <a href="/teacher/course-edit.php?id=<?php echo $course->getCourseId(); ?>" class="btn btn-secondary">Edit</a>
                            <a href="/teacher/course-delete.php?id=<?php echo $course->getCourseId(); ?>"
                               class="btn btn-danger"
                               onclick="return confirm('Are you sure you want to delete this course?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
