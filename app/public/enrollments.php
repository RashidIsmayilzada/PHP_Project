<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\EnrollmentRepository;

$enrollmentRepository = new EnrollmentRepository();
$enrollments = $enrollmentRepository->findAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollments</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-active {
            background-color: #4CAF50;
            color: white;
        }
        .badge-inactive {
            background-color: #f44336;
            color: white;
        }
        .nav {
            margin-bottom: 20px;
        }
        .nav a {
            margin-right: 15px;
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
        }
        .nav a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="users.php">Users</a>
        <a href="courses.php">Courses</a>
        <a href="assignments.php">Assignments</a>
        <a href="grades.php">Grades</a>
        <a href="enrollments.php">Enrollments</a>
    </div>

    <h1>Enrollments</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Student ID</th>
                <th>Course ID</th>
                <th>Status</th>
                <th>Enrollment Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($enrollments as $enrollment): ?>
            <tr>
                <td><?php echo htmlspecialchars($enrollment->getEnrollmentId()); ?></td>
                <td><?php echo htmlspecialchars($enrollment->getStudentId()); ?></td>
                <td><?php echo htmlspecialchars($enrollment->getCourseId()); ?></td>
                <td>
                    <span class="badge badge-<?php echo htmlspecialchars($enrollment->getStatus()); ?>">
                        <?php echo htmlspecialchars(strtoupper($enrollment->getStatus())); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($enrollment->getEnrollmentDate() ?? 'N/A'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
