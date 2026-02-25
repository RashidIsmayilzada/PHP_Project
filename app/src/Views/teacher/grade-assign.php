<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/teacher/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Grade Assignment</li>
        </ol>
    </nav>
    <h1 class="h2">Grade Assignment</h1>
    <p class="text-muted"><?= isset($assignment) ? htmlspecialchars($assignment->getAssignmentName()) : 'Assignment Grading' ?></p>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Student Submissions & Grading</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info border-0 shadow-sm mb-4">
            <i class="bi bi-info-circle me-2"></i>
            Enter grades for all students enrolled in this course. Changes will be saved once you click the button below.
        </div>

        <form method="POST">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th class="w-200">Grade</th>
                            <th>Feedback / Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Placeholder rows as actual logic is currently missing in controller -->
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">
                                <i class="bi bi-person-slash display-4"></i>
                                <p class="mt-2">No student data available to grade.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                <a href="javascript:history.back()" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5">
                    <i class="bi bi-check2-circle me-1"></i> Save All Grades
                </button>
            </div>
        </form>
    </div>
</div>
