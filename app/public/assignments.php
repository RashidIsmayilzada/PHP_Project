<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\AssignmentRepository;

$assignmentRepository = new AssignmentRepository();
$assignments = $assignmentRepository->findAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments</title>
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

    <h1>Assignments</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Course ID</th>
                <th>Assignment Name</th>
                <th>Description</th>
                <th>Max Points</th>
                <th>Due Date</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assignments as $assignment): ?>
            <tr>
                <td><?php echo htmlspecialchars($assignment->getAssignmentId()); ?></td>
                <td><?php echo htmlspecialchars($assignment->getCourseId()); ?></td>
                <td><?php echo htmlspecialchars($assignment->getAssignmentName()); ?></td>
                <td><?php echo htmlspecialchars($assignment->getDescription() ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($assignment->getMaxPoints()); ?></td>
                <td><?php echo htmlspecialchars($assignment->getDueDate() ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($assignment->getCreatedAt() ?? 'N/A'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
