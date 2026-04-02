<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Controller;

class ErrorController extends Controller
{
    public function notFound(): void
    {
        http_response_code(404);
        $this->render('errors/404', ['pageTitle' => '404 Not Found']);
    }

    public function forbidden(): void
    {
        http_response_code(403);
        $this->render('errors/403', ['pageTitle' => '403 Access Denied']);
    }

    public function serverError(?string $message = null): void
    {
        http_response_code(500);

        if ($message === null && isset($_GET['message'])) {
            $message = $_GET['message'];
        }

        $this->render('errors/error', [
            'pageTitle' => 'Error',
            'errorMessage' => $message ?? 'An unexpected error occurred. Please try again.'
        ]);
    }
}
