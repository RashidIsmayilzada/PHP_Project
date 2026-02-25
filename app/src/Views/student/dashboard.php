<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h1 class="h2">Student Dashboard</h1>
        <p class="text-muted">Overview of your academic progress</p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="/student/statistics" class="btn btn-primary">
            <i class="bi bi-graph-up me-2"></i>View Detailed Statistics
        </a>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- GPA Card -->
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow h-100 border-0">
            <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                <div class="text-white-50 small text-uppercase fw-bold mb-2">Overall GPA</div>
                <div class="display-3 fw-bold mb-0"><?= number_format($overallGPA, 2) ?></div>
                <div class="text-white-50">out of 4.0</div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="col-md-8">
        <div class="row g-4 h-100">
            <div class="col-sm-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-primary mb-1"><?= count($enrolledCourses) ?></div>
                        <div class="text-muted small text-uppercase fw-semibold">Enrolled Courses</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center">
                        <?php
                        $activeCount = 0;
                        foreach ($coursesData as $data) {
                            if ($data['status'] === 'active') $activeCount++;
                        }
                        ?>
                        <div class="display-6 fw-bold text-success mb-1"><?= $activeCount ?></div>
                        <div class="text-muted small text-uppercase fw-semibold">Active Courses</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body text-center">
                        <?php
                        $totalCredits = 0;
                        foreach ($enrolledCourses as $course) {
                            $totalCredits += $course->getCredits() ?? 0;
                        }
                        ?>
                        <div class="display-6 fw-bold text-info mb-1"><?= $totalCredits ?></div>
                        <div class="text-muted small text-uppercase fw-semibold">Total Credits</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<h3 class="h4 mb-4">My Courses</h3>

<?php if (empty($coursesData)): ?>
    <div class="card bg-light border-0 py-5 text-center">
        <div class="card-body">
            <i class="bi bi-journal-x display-1 text-muted mb-3"></i>
            <h4>No Courses Enrolled</h4>
            <p class="text-muted">You are not currently enrolled in any courses.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($coursesData as $data): ?>
            <?php
            $course = $data['course'];
            $average = $data['average'];
            $letterGrade = $data['letter_grade'];
            $teacher = $data['teacher'];
            $status = $data['status'];
            
            $gradeColor = 'secondary';
            if ($average !== null) {
                if ($average >= 90) $gradeColor = 'success';
                elseif ($average >= 70) $gradeColor = 'primary';
                elseif ($average >= 50) $gradeColor = 'warning';
                else $gradeColor = 'danger';
            }
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm transition-hover border-0">
                    <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between">
                        <span class="badge bg-light text-dark border small"><?= htmlspecialchars($course->getCourseCode()) ?></span>
                        <span class="badge rounded-pill bg-<?= $status === 'active' ? 'success' : 'secondary' ?> opacity-75">
                            <?= ucfirst($status) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-1"><?= htmlspecialchars($course->getCourseName()) ?></h5>
                        <p class="text-muted small mb-4">
                            <i class="bi bi-person me-1"></i> <?= htmlspecialchars($teacher ? $teacher->getFullName() : 'Unknown Teacher') ?>
                        </p>

                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded text-center">
                                    <div class="small text-muted mb-1">Average</div>
                                    <div class="h4 mb-0 text-<?= $gradeColor ?>"><?= $average !== null ? number_format($average, 1) . '%' : 'N/A' ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded text-center">
                                    <div class="small text-muted mb-1">Grade</div>
                                    <div class="h4 mb-0 text-<?= $gradeColor ?>"><?= $letterGrade ?: '-' ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between small text-muted mb-3">
                            <span><i class="bi bi-calendar-event me-1"></i> <?= htmlspecialchars($course->getSemester() ?? 'N/A') ?></span>
                            <span><i class="bi bi-award me-1"></i> <?= htmlspecialchars((string)($course->getCredits() ?? 'N/A')) ?> Credits</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pb-4">
                        <a href="/student/course-detail/<?= $course->getCourseId() ?>" class="btn btn-outline-primary w-100">
                            View Course Details
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
