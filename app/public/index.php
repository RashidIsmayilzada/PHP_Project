<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Framework\Container;
use App\Framework\Router;
use App\Framework\Auth;

// Import Interfaces
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Repositories\Interfaces\AssignmentRepositoryInterface;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use App\Repositories\Interfaces\GradeRepositoryInterface;

use App\Services\Interfaces\UserServiceInterface;
use App\Services\Interfaces\CourseServiceInterface;
use App\Services\Interfaces\AssignmentServiceInterface;
use App\Services\Interfaces\EnrollmentServiceInterface;
use App\Services\Interfaces\GradeServiceInterface;

// Import Concrete Classes
use App\Repositories\UserRepository;
use App\Repositories\CourseRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\GradeRepository;

use App\Services\UserService;
use App\Services\CourseService;
use App\Services\AssignmentService;
use App\Services\EnrollmentService;
use App\Services\GradeService;

use App\Controllers\AuthController;
use App\Controllers\StudentController;
use App\Controllers\TeacherController;
use App\Controllers\CourseController;
use App\Controllers\AssignmentController;
use App\Controllers\GradeController;
use App\Controllers\EnrollmentController;
use App\Controllers\UserController;
use App\Controllers\ErrorController;

// --- 1. Environment & Setup ---
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (\Exception $e) {
    // .env is optional
}

// --- 2. Session Management ---
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$timeoutSeconds = 15 * 60;
$now = time();
if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > $timeoutSeconds) {
    Auth::logout();
    header('Location: /login?message=session_expired');
    exit;
}
$_SESSION['last_activity'] = $now;

// --- 3. Dependency Injection Container ---
$container = new Container();

// Bind Repositories
$container->set(UserRepositoryInterface::class, fn() => new UserRepository());
$container->set(CourseRepositoryInterface::class, fn() => new CourseRepository());
$container->set(AssignmentRepositoryInterface::class, fn() => new AssignmentRepository());
$container->set(EnrollmentRepositoryInterface::class, fn() => new EnrollmentRepository());
$container->set(GradeRepositoryInterface::class, fn() => new GradeRepository());

// Bind Services
$container->set(UserServiceInterface::class, fn($c) => new UserService($c->get(UserRepositoryInterface::class)));
$container->set(CourseServiceInterface::class, fn($c) => new CourseService($c->get(CourseRepositoryInterface::class), $c->get(UserRepositoryInterface::class)));
$container->set(AssignmentServiceInterface::class, fn($c) => new AssignmentService($c->get(AssignmentRepositoryInterface::class), $c->get(CourseRepositoryInterface::class)));
$container->set(EnrollmentServiceInterface::class, fn($c) => new EnrollmentService($c->get(EnrollmentRepositoryInterface::class), $c->get(UserRepositoryInterface::class), $c->get(CourseRepositoryInterface::class)));
$container->set(GradeServiceInterface::class, fn($c) => new GradeService($c->get(GradeRepositoryInterface::class), $c->get(AssignmentRepositoryInterface::class), $c->get(EnrollmentRepositoryInterface::class), $c->get(CourseRepositoryInterface::class)));

// Bind Controllers
$container->set(AuthController::class, fn($c) => new AuthController($c->get(UserServiceInterface::class)));
$container->set(StudentController::class, fn($c) => new StudentController(
    $c->get(CourseServiceInterface::class),
    $c->get(GradeServiceInterface::class),
    $c->get(UserServiceInterface::class),
    $c->get(EnrollmentServiceInterface::class)
));
$container->set(TeacherController::class, fn($c) => new TeacherController(
    $c->get(CourseServiceInterface::class),
    $c->get(EnrollmentServiceInterface::class),
    $c->get(AssignmentServiceInterface::class)
));
$container->set(CourseController::class, fn($c) => new CourseController($c->get(CourseServiceInterface::class)));
$container->set(AssignmentController::class, fn($c) => new AssignmentController($c->get(AssignmentServiceInterface::class), $c->get(CourseServiceInterface::class)));
$container->set(GradeController::class, fn($c) => new GradeController($c->get(GradeServiceInterface::class)));
$container->set(EnrollmentController::class, fn($c) => new EnrollmentController(
    $c->get(EnrollmentServiceInterface::class),
    $c->get(CourseServiceInterface::class),
    $c->get(UserServiceInterface::class)
));
$container->set(UserController::class, fn($c) => new UserController($c->get(UserServiceInterface::class)));
$container->set(ErrorController::class, fn() => new ErrorController());

// --- 4. Routing ---
$router = new Router($container);

// Public Routes
$router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest']);
$router->get('/register', [AuthController::class, 'showRegister'], ['guest']);
$router->post('/register', [AuthController::class, 'register'], ['guest']);

// API Routes
$router->get('/api/users', [UserController::class, 'index'], ['auth', 'teacher']);
$router->get('/api/students', [UserController::class, 'students'], ['auth', 'teacher']);

// Protected Routes
$router->get('/logout', [AuthController::class, 'logout'], ['auth']);

// Student Area
$router->get('/student/dashboard', [StudentController::class, 'dashboard'], ['auth', 'student']);
$router->get('/student/course-detail/{id:\d+}', [StudentController::class, 'courseDetail'], ['auth', 'student']);
$router->get('/student/statistics', [StudentController::class, 'statistics'], ['auth', 'student']);

// Teacher Area
$router->get('/teacher/dashboard', [TeacherController::class, 'dashboard'], ['auth', 'teacher']);
$router->get('/teacher/course-detail/{id:\d+}', [CourseController::class, 'show'], ['auth', 'teacher']);
$router->get('/teacher/course-create', [CourseController::class, 'createAction'], ['auth', 'teacher']);
$router->post('/teacher/course-create', [CourseController::class, 'createAction'], ['auth', 'teacher']);
$router->get('/teacher/course-edit/{id:\d+}', [CourseController::class, 'editAction'], ['auth', 'teacher']);
$router->post('/teacher/course-edit/{id:\d+}', [CourseController::class, 'editAction'], ['auth', 'teacher']);
$router->post('/teacher/course-delete/{id:\d+}', [CourseController::class, 'delete'], ['auth', 'teacher']);

// Teacher Management
$router->get('/teacher/assignment-create/{courseId:\d+}', [AssignmentController::class, 'createAction'], ['auth', 'teacher']);
$router->post('/teacher/assignment-create/{courseId:\d+}', [AssignmentController::class, 'createAction'], ['auth', 'teacher']);
$router->get('/teacher/assignment-edit/{id:\d+}', [AssignmentController::class, 'editAction'], ['auth', 'teacher']);
$router->post('/teacher/assignment-edit/{id:\d+}', [AssignmentController::class, 'editAction'], ['auth', 'teacher']);
$router->post('/teacher/assignment-delete/{id:\d+}', [AssignmentController::class, 'delete'], ['auth', 'teacher']);

$router->get('/teacher/course-grades/{courseId:\d+}', [GradeController::class, 'showCourseGrades'], ['auth', 'teacher']);
$router->get('/teacher/grade-assign/{assignmentId:\d+}', [GradeController::class, 'gradeAction'], ['auth', 'teacher']);
$router->post('/teacher/grade-assign/{assignmentId:\d+}', [GradeController::class, 'gradeAction'], ['auth', 'teacher']);
$router->get('/teacher/grade-edit/{id:\d+}', [GradeController::class, 'editAction'], ['auth', 'teacher']);
$router->post('/teacher/grade-edit/{id:\d+}', [GradeController::class, 'editAction'], ['auth', 'teacher']);

$router->get('/teacher/course-enroll/{courseId:\d+}', [EnrollmentController::class, 'enrollAction'], ['auth', 'teacher']);
$router->post('/teacher/course-enroll/{courseId:\d+}', [EnrollmentController::class, 'enrollAction'], ['auth', 'teacher']);

// Root
$router->get('/', function() {
    if (Auth::check()) {
        header('Location: ' . (Auth::role() === 'teacher' ? '/teacher/dashboard' : '/student/dashboard'));
    } else {
        header('Location: /login');
    }
});

$router->dispatch();
