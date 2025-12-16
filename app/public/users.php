<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\UserRepository;

$userRepository = new UserRepository();
$users = $userRepository->findAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
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
        .badge-teacher {
            background-color: #2196F3;
            color: white;
        }
        .badge-student {
            background-color: #FF9800;
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

    <h1>Users</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Student Number</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user->getUserId()); ?></td>
                <td><?php echo htmlspecialchars($user->getFullName()); ?></td>
                <td><?php echo htmlspecialchars($user->getEmail()); ?></td>
                <td>
                    <span class="badge badge-<?php echo $user->getRole(); ?>">
                        <?php echo htmlspecialchars(strtoupper($user->getRole())); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($user->getStudentNumber() ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($user->getCreatedAt() ?? 'N/A'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
