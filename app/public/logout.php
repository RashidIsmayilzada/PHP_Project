<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\AuthService;
use App\Repositories\UserRepository;

$userRepository = new UserRepository();
$authService = new AuthService($userRepository);

// Log out the user
$authService->logout();

// Redirect to login page with success message
header('Location: /login.php?message=logout_success');
exit;
