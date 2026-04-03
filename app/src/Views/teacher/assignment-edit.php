<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/teacher/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/teacher/course-detail/<?= htmlspecialchars((string) $courseId) ?>" class="text-decoration-none">Course</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Assignment</li>
                </ol>
            </nav>
            <h1 class="h2">Edit Assignment</h1>
            <p class="text-muted">Update task details for your students</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/teacher/assignment-edit/<?= htmlspecialchars((string) $assignment->getAssignmentId()) ?>">
                    <div class="mb-3">
                        <label for="assignment_name" class="form-label fw-semibold">Assignment Name</label>
                        <input type="text" class="form-control form-control-lg <?= !empty($errors['assignment_name']) ? 'is-invalid' : '' ?>" id="assignment_name" name="assignment_name" required value="<?= htmlspecialchars($formData['assignment_name'] ?? $assignment->getAssignmentName()) ?>">
                        <?php if (!empty($errors['assignment_name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['assignment_name']) ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control <?= !empty($errors['description']) ? 'is-invalid' : '' ?>" id="description" name="description" rows="4" required><?= htmlspecialchars($formData['description'] ?? $assignment->getDescription() ?? '') ?></textarea>
                        <?php if (!empty($errors['description'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['description']) ?></div><?php endif; ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="max_points" class="form-label fw-semibold">Maximum Points</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-star"></i></span>
                                <input type="number" class="form-control <?= !empty($errors['max_points']) ? 'is-invalid' : '' ?>" id="max_points" name="max_points" step="0.5" required value="<?= htmlspecialchars($formData['max_points'] ?? (string)$assignment->getMaxPoints()) ?>">
                                <?php if (!empty($errors['max_points'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['max_points']) ?></div><?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="due_date" class="form-label fw-semibold">Due Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                <input type="date" class="form-control <?= !empty($errors['due_date']) ? 'is-invalid' : '' ?>" id="due_date" name="due_date" required value="<?= htmlspecialchars($formData['due_date'] ?? $assignment->getDueDate() ?? '') ?>">
                                <?php if (!empty($errors['due_date'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['due_date']) ?></div><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary btn-lg px-5">Update Assignment</button>
                        <a href="/teacher/course-detail/<?= htmlspecialchars((string) $courseId) ?>" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
