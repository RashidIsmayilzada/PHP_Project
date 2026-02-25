<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/teacher/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/teacher/course-detail/<?= $courseId ?>" class="text-decoration-none">Course</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Assignment</li>
                </ol>
            </nav>
            <h1 class="h2">Create New Assignment</h1>
            <p class="text-muted">Add a new task or exam to your course</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/teacher/assignment-create/<?= $courseId ?>">
                    <div class="mb-3">
                        <label for="assignment_name" class="form-label fw-semibold">Assignment Name</label>
                        <input type="text" class="form-control form-control-lg" id="assignment_name" name="assignment_name" required placeholder="e.g., Homework 1: Introduction to Logic">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Instructions for students..."></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="max_points" class="form-label fw-semibold">Maximum Points</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-star"></i></span>
                                <input type="number" class="form-control" id="max_points" name="max_points" step="0.5" required value="100.0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="due_date" class="form-label fw-semibold">Due Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                <input type="date" class="form-control" id="due_date" name="due_date">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary btn-lg px-5">Create Assignment</button>
                        <a href="/teacher/course-detail/<?= $courseId ?>" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
