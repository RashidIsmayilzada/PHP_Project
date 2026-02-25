<div class="row justify-content-center align-items-center py-5">
    <div class="col-md-6 text-center">
        <div class="display-1 fw-bold text-danger mb-4"><i class="bi bi-file-earmark-x"></i></div>
        <h1 class="h2 mb-3">Delete Assignment?</h1>
        <div class="card shadow-sm border-danger-subtle mb-5">
            <div class="card-body p-4">
                <p class="mb-0">Are you sure you want to delete <strong><?= htmlspecialchars($assignment->getAssignmentName()) ?></strong>?</p>
                <p class="text-danger small mt-2 mb-0"><i class="bi bi-exclamation-triangle-fill"></i> This action cannot be undone and will delete all grades for this assignment.</p>
            </div>
        </div>
        
        <form method="POST" action="/teacher/assignment-delete/<?= $assignment->getAssignmentId() ?>">
            <input type="hidden" name="confirm" value="1">
            <div class="d-flex justify-content-center gap-3">
                <button type="submit" class="btn btn-danger btn-lg px-5">Yes, Delete Assignment</button>
                <a href="/teacher/course-detail/<?= $assignment->getCourseId() ?>" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
