<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\DatabaseTestController;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1, h2, h3 {
            color: #333;
        }
        hr {
            border: none;
            border-top: 2px solid #ccc;
            margin: 20px 0;
        }
        ul {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <?php
    try {
        $controller = new DatabaseTestController();
        $controller->testAllConnections();
    } catch (Exception $e) {
        echo "<h1 style='color: red;'>Error</h1>";
        echo "<p style='color: red;'>Failed to run database tests: " . $e->getMessage() . "</p>";
    }
    ?>
</body>
</html>
