<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/teacher/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/teacher/course-detail/<?= $course->getCourseId() ?>" class="text-decoration-none"><?= htmlspecialchars($course->getCourseCode()) ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
            <h1 class="h2">Edit Course</h1>
            <p class="text-muted">Update course information</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/teacher/course-edit/<?= $course->getCourseId() ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="course_code" class="form-label fw-semibold">Course Code</label>
                            <input type="text" class="form-control" id="course_code" name="course_code" required value="<?= htmlspecialchars($course->getCourseCode()) ?>">
                        </div>
                        <div class="col-md-8">
                            <label for="course_name" class="form-label fw-semibold">Course Name</label>
                            <input type="text" class="form-control" id="course_name" name="course_name" required value="<?= htmlspecialchars($course->getCourseName()) ?>">
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($course->getDescription() ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="credits" class="form-label fw-semibold">Credits</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-award"></i></span>
                                <input type="number" class="form-control" id="credits" name="credits" step="0.5" required value="<?= htmlspecialchars((string)$course->getCredits()) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="semester" class="form-label fw-semibold">Semester</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="text" class="form-control" id="semester" name="semester" value="<?= htmlspecialchars($course->getSemester() ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary btn-lg px-5">Update Course</button>
                        <a href="/teacher/course-detail/<?= $course->getCourseId() ?>" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
