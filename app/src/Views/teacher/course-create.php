<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/teacher/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create New Course</li>
                </ol>
            </nav>
            <h1 class="h2">Create New Course</h1>
            <p class="text-muted">Set up a new course for your students</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/teacher/course-create">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="course_code" class="form-label fw-semibold">Course Code</label>
                            <input type="text" class="form-control" id="course_code" name="course_code" required placeholder="e.g., CS101">
                        </div>
                        <div class="col-md-8">
                            <label for="course_name" class="form-label fw-semibold">Course Name</label>
                            <input type="text" class="form-control" id="course_name" name="course_name" required placeholder="e.g., Introduction to Programming">
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Briefly describe the course content..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="credits" class="form-label fw-semibold">Credits</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-award"></i></span>
                                <input type="number" class="form-control" id="credits" name="credits" step="0.5" required value="3.0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="semester" class="form-label fw-semibold">Semester</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="text" class="form-control" id="semester" name="semester" placeholder="e.g., Fall 2026">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary btn-lg px-5">Create Course</button>
                        <a href="/teacher/dashboard" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
