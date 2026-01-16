<?php

/**
 * This is the central route handler of the application.
 * It uses FastRoute to map URLs to controller methods.
 * 
 * See the documentation for FastRoute for more information: https://github.com/nikic/FastRoute
 */

require __DIR__ . '/../vendor/autoload.php';

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

// Define all application routes
$dispatcher = simpleDispatcher(function(RouteCollector $r) {
    // Home route
    $r->addRoute('GET', '/', ['App\Controllers\AuthController', 'showLogin']);

    // Authentication routes
    $r->addRoute('GET', '/login', ['App\Controllers\AuthController', 'showLogin']);
    $r->addRoute('POST', '/login', ['App\Controllers\AuthController', 'login']);
    $r->addRoute('GET', '/register', ['App\Controllers\AuthController', 'showRegister']);
    $r->addRoute('POST', '/register', ['App\Controllers\AuthController', 'register']);
    $r->addRoute(['GET', 'POST'], '/logout', ['App\Controllers\AuthController', 'logout']);

    // Student routes
    $r->addRoute('GET', '/student/dashboard', ['App\Controllers\StudentController', 'dashboard']);
    $r->addRoute('GET', '/student/course/{id:\d+}', ['App\Controllers\StudentController', 'courseDetail']);
    $r->addRoute('GET', '/student/statistics', ['App\Controllers\StudentController', 'statistics']);

    // Teacher routes
    $r->addRoute('GET', '/teacher/dashboard', ['App\Controllers\TeacherController', 'dashboard']);

    // Course management routes (teacher only)
    $r->addRoute('GET', '/teacher/course/{id:\d+}', ['App\Controllers\CourseController', 'show']);
    $r->addRoute(['GET', 'POST'], '/teacher/course/create', ['App\Controllers\CourseController', 'createAction']);
    $r->addRoute(['GET', 'POST'], '/teacher/course/{id:\d+}/edit', ['App\Controllers\CourseController', 'editAction']);
    $r->addRoute(['GET', 'POST'], '/teacher/course/{id:\d+}/delete', ['App\Controllers\CourseController', 'delete']);

    // Assignment management routes (teacher only)
    $r->addRoute(['GET', 'POST'], '/teacher/assignment/create', ['App\Controllers\AssignmentController', 'createAction']);
    $r->addRoute(['GET', 'POST'], '/teacher/assignment/{id:\d+}/edit', ['App\Controllers\AssignmentController', 'editAction']);
    $r->addRoute(['GET', 'POST'], '/teacher/assignment/{id:\d+}/delete', ['App\Controllers\AssignmentController', 'delete']);

    // Enrollment management routes (teacher only)
    $r->addRoute(['GET', 'POST'], '/teacher/course/{courseId:\d+}/enroll', ['App\Controllers\EnrollmentController', 'enrollAction']);

    // Grade management routes (teacher only)
    $r->addRoute('GET', '/teacher/course/{courseId:\d+}/grades', ['App\Controllers\GradeController', 'showCourseGrades']);
    $r->addRoute(['GET', 'POST'], '/teacher/assignment/{assignmentId:\d+}/grade', ['App\Controllers\GradeController', 'gradeAction']);
    $r->addRoute(['GET', 'POST'], '/teacher/grade/{id:\d+}/edit', ['App\Controllers\GradeController', 'editAction']);

    // Error routes
    $r->addRoute('GET', '/403', ['App\Controllers\ErrorController', 'forbidden']);
    $r->addRoute('GET', '/404', ['App\Controllers\ErrorController', 'notFound']);
});





/**
 * Get the request method and URI from the server variables and invoke the dispatcher.
 */
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

/**
 * Switch on the dispatcher result and call the appropriate controller method if found.
 */
switch ($routeInfo[0]) {
    // Handle not found routes
    case FastRoute\Dispatcher::NOT_FOUND:
        $controller = new App\Controllers\ErrorController();
        $controller->notFound();
        break;
    // Handle routes that were invoked with the wrong HTTP method
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo 'Method Not Allowed';
        break;
    // Handle found routes
    case FastRoute\Dispatcher::FOUND:
        /**
         * $routeInfo contains the data about the matched route.
         * 
         * $routeInfo[1] is the whatever we define as the third argument the `$r->addRoute` method.
         *  For instance for: `$r->addRoute('GET', '/hello/{name}', ['App\Controllers\HelloController', 'greet']);`
         *  $routeInfo[1] will be `['App\Controllers\HelloController', 'greet']`
         * 
         * Hint: we can use class strings like `App\Controllers\HelloController` to create new instances of that class.
         * Hint: in PHP we can use a string to call a class method dynamically, like this: `$instance->$methodName($args);`
         */

        $controllerClass = $routeInfo[1][0];
        $methodName = $routeInfo[1][1];

        /**
         * $route[2] contains any dynamic parameters parsed from the URL.
         * For instance, if we add a route like:
         *  $r->addRoute('GET', '/hello/{name}', ['App\Controllers\HelloController', 'greet']);
         * and the URL is `/hello/dan-the-man`, then `$routeInfo[2][name]` will be `dan-the-man`.
         */
        $params = $routeInfo[2];

        $controller = new $controllerClass();
        $controller->$methodName(...array_values($params));

        break;
}
