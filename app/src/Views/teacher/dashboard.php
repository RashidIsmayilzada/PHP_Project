<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2">Teacher Dashboard</h1>
        <p class="text-muted">Manage your courses and student grades</p>
    </div>
    <div>
        <a href="/teacher/course-create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Create New Course
        </a>
    </div>
</div>

<div class="row">
    <?php if (empty($courseData)): ?>
        <div class="col-12">
            <div class="card bg-light border-0 py-5">
                <div class="card-body text-center">
                    <i class="bi bi-journal-x display-1 text-muted mb-3"></i>
                    <h3>No Courses Created</h3>
                    <p class="text-muted">You haven't created any courses yet. Get started by creating your first course!</p>
                    <a href="/teacher/course-create" class="btn btn-primary mt-2">Create Your First Course</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($courseData as $data): ?>
            <?php $course = $data['course']; ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0 transition-hover">
                    <div class="card-header bg-primary text-white py-3 border-0">
                        <div class="small text-white-50"><?= htmlspecialchars($course->getCourseCode()) ?></div>
                        <h3 class="h5 mb-0"><?= htmlspecialchars($course->getCourseName()) ?></h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-4">
                            <?= htmlspecialchars(substr($course->getDescription() ?? 'No description provided.', 0, 100)) ?>...
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-people me-1"></i> <?= $data['enrollment_count'] ?> Students
                            </span>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-file-text me-1"></i> <?= $data['assignment_count'] ?> Assignments
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 p-3">
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="/teacher/course-detail/<?= $course->getCourseId() ?>" class="btn btn-outline-primary w-100">Manage</a>
                            </div>
                            <div class="col-6">
                                <a href="/teacher/course-grades/<?= $course->getCourseId() ?>" class="btn btn-outline-secondary w-100">Grades</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
