<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/teacher/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/teacher/course-detail/<?= $courseId ?>" class="text-decoration-none">Course</a></li>
            <li class="breadcrumb-item active" aria-current="page">Grades</li>
        </ol>
    </nav>
    <h1 class="h2">Course Grades</h1>
    <p class="text-muted">Comprehensive grade overview for all enrolled students</p>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Grade Matrix: <?= isset($course) ? htmlspecialchars($course->getCourseName()) : 'Course' ?></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Student</th>
                        <th>Average</th>
                        <th>Letter Grade</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-grid-3x3 display-4"></i>
                            <p class="mt-2">No grade data available yet.</p>
                            <a href="/teacher/course-enroll/<?= $courseId ?>" class="btn btn-primary btn-sm mt-2">Manage Enrollments</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
