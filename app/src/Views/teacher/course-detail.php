<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/teacher/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($course->getCourseCode()) ?></li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <span class="badge bg-primary mb-2"><?= htmlspecialchars($course->getCourseCode()) ?></span>
            <h1 class="h2"><?= htmlspecialchars($course->getCourseName()) ?></h1>
        </div>
        <div class="btn-group">
            <a href="/teacher/course-edit/<?= $course->getCourseId() ?>" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Course Description</h5>
                <p class="text-muted lh-lg">
                    <?= nl2br(htmlspecialchars($course->getDescription() ?? 'No description provided.')) ?>
                </p>
                
                <hr class="my-4">
                
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <div class="text-muted small text-uppercase fw-semibold">Semester</div>
                        <div class="h5 mb-0"><?= htmlspecialchars($course->getSemester() ?? 'N/A') ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small text-uppercase fw-semibold">Credits</div>
                        <div class="h5 mb-0"><?= htmlspecialchars((string)($course->getCredits() ?? 'N/A')) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Course Summary</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info border-0 shadow-sm mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Detailed course statistics and performance metrics will be displayed here as they become available.
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm sticky-top sticky-offset">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="/teacher/course-enroll/<?= $course->getCourseId() ?>" class="btn btn-primary py-2">
                        <i class="bi bi-people me-2"></i>Manage Enrollments
                    </a>
                    <a href="/teacher/course-grades/<?= $course->getCourseId() ?>" class="btn btn-outline-primary py-2">
                        <i class="bi bi-table me-2"></i>View All Grades
                    </a>
                    <a href="/teacher/assignment-create/<?= $course->getCourseId() ?>" class="btn btn-success py-2">
                        <i class="bi bi-plus-lg me-2"></i>Create Assignment
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong><?= htmlspecialchars($course->getCourseName()) ?></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="/teacher/course-delete/<?= $course->getCourseId() ?>" method="POST">
                    <button type="submit" class="btn btn-danger">Delete Course</button>
                </form>
            </div>
        </div>
    </div>
</div>
