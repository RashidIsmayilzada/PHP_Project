<?php

namespace App\Controllers;

use App\Services\AssignmentService;

class AssignmentController extends BaseController
{
    private AssignmentService $assignmentService;

    public function __construct()
    {
        parent::__construct();
        $this->assignmentService = new AssignmentService();
    }

    // Handle assignment creation form display and processing
    public function createAction(): void
    {
        $this->getAuthService()->requireRole('teacher');

        require __DIR__ . '/../../public/teacher/assignment-create.php';
    }

    // Handle assignment editing form display and processing
    public function editAction(int $id): void
    {
        $this->getAuthService()->requireRole('teacher');
        $_GET['id'] = $id;

        require __DIR__ . '/../../public/teacher/assignment-edit.php';
    }

    // Handle assignment deletion with confirmation
    public function delete(int $id): void
    {
        $this->getAuthService()->requireRole('teacher');
        $_GET['id'] = $id;

        require __DIR__ . '/../../public/teacher/assignment-delete.php';
    }
}
