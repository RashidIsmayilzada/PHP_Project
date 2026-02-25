<?php

namespace App\Controllers;

class ErrorController extends BaseController
{
    // Display 404 not found error page
    public function notFound(): void
    {
        http_response_code(404);
        $this->render('errors/404.view.php', ['pageTitle' => '404 Not Found']);
    }

    // Display 403 forbidden error page
    public function forbidden(): void
    {
        http_response_code(403);
        $this->render('errors/403.view.php', ['pageTitle' => '403 Access Denied']);
    }

    // Display 500 server error page with optional custom message
    public function serverError(?string $message = null): void
    {
        http_response_code(500);

        if ($message === null && isset($_GET['message'])) {
            $message = $_GET['message'];
        }

        $this->render('errors/error.view.php', [
            'pageTitle' => 'Error',
            'errorMessage' => $message ?? 'An unexpected error occurred. Please try again.'
        ]);
    }
}
