<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\AuthService;
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
$gradeService = new GradeService($gradeRepository, $assignmentRepository, $enrollmentRepository, $courseRepository);

// Require student authentication
$authService->requireRole('student');
$currentUser = $authService->getCurrentUser();

// Get comprehensive student statistics
$statistics = $gradeService->getStudentStatistics($currentUser->getUserId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Statistics</title>
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

        .gpa-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 50px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
            margin-bottom: 30px;
            text-align: center;
            color: white;
        }

        .gpa-hero h2 {
            font-size: 22px;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .gpa-display {
            font-size: 96px;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
            line-height: 1;
        }

        .gpa-scale {
            font-size: 18px;
            opacity: 0.9;
        }

        .overview-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .overview-section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
        }

        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .overview-card {
            padding: 25px;
            background: #f8f9fa;
            border-radius: 10px;
            text-align: center;
        }

        .overview-value {
            font-size: 40px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 8px;
        }

        .overview-label {
            color: #666;
            font-size: 15px;
        }

        .courses-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .courses-section h3 {
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

        .grade-a { color: #28a745; }
        .grade-b { color: #17a2b8; }
        .grade-c { color: #ffc107; }
        .grade-d { color: #fd7e14; }
        .grade-f { color: #dc3545; }

        .distribution-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .distribution-section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
        }

        .grade-bars {
            margin-top: 20px;
        }

        .grade-bar-item {
            margin-bottom: 20px;
        }

        .grade-bar-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .grade-bar-label {
            font-weight: 600;
            font-size: 16px;
        }

        .grade-bar-count {
            color: #666;
        }

        .grade-bar-track {
            height: 30px;
            background: #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
        }

        .grade-bar-fill {
            height: 100%;
            border-radius: 15px;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .bar-a { background: linear-gradient(90deg, #28a745, #20c997); }
        .bar-b { background: linear-gradient(90deg, #17a2b8, #20c997); }
        .bar-c { background: linear-gradient(90deg, #ffc107, #ffcd39); }
        .bar-d { background: linear-gradient(90deg, #fd7e14, #ff922b); }
        .bar-f { background: linear-gradient(90deg, #dc3545, #e55353); }

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

        .empty-state p {
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Academic Statistics</h1>
            <a href="/student/dashboard.php">← Back to Dashboard</a>
        </div>

        <?php if ($statistics['total_courses'] > 0): ?>
            <!-- GPA Hero Section -->
            <div class="gpa-hero">
                <h2>Your Overall GPA</h2>
                <div class="gpa-display"><?php echo number_format($statistics['overall_gpa'], 2); ?></div>
                <div class="gpa-scale">out of 4.0</div>
            </div>

            <!-- Overview Section -->
            <div class="overview-section">
                <h3>Academic Overview</h3>
                <div class="overview-grid">
                    <div class="overview-card">
                        <div class="overview-value"><?php echo $statistics['total_courses']; ?></div>
                        <div class="overview-label">Total Courses</div>
                    </div>
                    <div class="overview-card">
                        <div class="overview-value"><?php echo number_format($statistics['total_credits'], 0); ?></div>
                        <div class="overview-label">Total Credits</div>
                    </div>
                    <div class="overview-card">
                        <div class="overview-value">
                            <?php
                            $gradeDistribution = $statistics['grade_distribution'];
                            $totalA = $gradeDistribution['A'] ?? 0;
                            $totalB = $gradeDistribution['B'] ?? 0;
                            echo $totalA + $totalB;
                            ?>
                        </div>
                        <div class="overview-label">A's & B's</div>
                    </div>
                </div>
            </div>

            <!-- Performance by Course -->
            <div class="courses-section">
                <h3>Performance by Course</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Credits</th>
                            <th>Average</th>
                            <th>Letter Grade</th>
                            <th>GPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statistics['courses'] as $courseData): ?>
                            <?php
                            $course = $courseData['course'];
                            $average = $courseData['average'];
                            $letter = $courseData['letter'];
                            $gpa = $courseData['gpa'];
                            $credits = $courseData['credits'];
                            $gradeClass = 'grade-' . strtolower($letter);
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($course->getCourseCode()); ?></strong><br>
                                    <span style="font-size: 13px; color: #999;">
                                        <?php echo htmlspecialchars($course->getCourseName()); ?>
                                    </span>
                                </td>
                                <td><?php echo $credits; ?></td>
                                <td class="<?php echo $gradeClass; ?>">
                                    <strong><?php echo number_format($average, 1); ?>%</strong>
                                </td>
                                <td>
                                    <span class="<?php echo $gradeClass; ?>" style="font-size: 18px; font-weight: bold;">
                                        <?php echo $letter; ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo number_format($gpa, 1); ?></strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Grade Distribution -->
            <div class="distribution-section">
                <h3>Grade Distribution</h3>
                <p style="color: #666; margin-bottom: 20px;">
                    Breakdown of your letter grades across all <?php echo $statistics['total_courses']; ?> course(s)
                </p>

                <div class="grade-bars">
                    <?php
                    $gradeDistribution = $statistics['grade_distribution'];
                    $totalCourses = $statistics['total_courses'];
                    $grades = [
                        'A' => ['label' => "A's (90-100%)", 'class' => 'bar-a'],
                        'B' => ['label' => "B's (80-89%)", 'class' => 'bar-b'],
                        'C' => ['label' => "C's (70-79%)", 'class' => 'bar-c'],
                        'D' => ['label' => "D's (60-69%)", 'class' => 'bar-d'],
                        'F' => ['label' => "F's (<60%)", 'class' => 'bar-f']
                    ];

                    foreach ($grades as $grade => $info):
                        $count = $gradeDistribution[$grade] ?? 0;
                        $percentage = $totalCourses > 0 ? ($count / $totalCourses) * 100 : 0;
                    ?>
                        <div class="grade-bar-item">
                            <div class="grade-bar-header">
                                <span class="grade-bar-label"><?php echo $info['label']; ?></span>
                                <span class="grade-bar-count"><?php echo $count; ?> course(s)</span>
                            </div>
                            <div class="grade-bar-track">
                                <div class="grade-bar-fill <?php echo $info['class']; ?>"
                                     style="width: <?php echo $percentage; ?>%">
                                    <?php if ($percentage > 10): ?>
                                        <?php echo number_format($percentage, 0); ?>%
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="overview-section">
                <div class="empty-state">
                    <h3>No Statistics Available</h3>
                    <p>You don't have any graded courses yet. Statistics will appear once you receive grades.</p>
                    <a href="/student/dashboard.php" class="btn">Go to Dashboard</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
