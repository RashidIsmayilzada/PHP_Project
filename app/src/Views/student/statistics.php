<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/student/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Academic Statistics</li>
        </ol>
    </nav>
    <h1 class="h2">Academic Statistics</h1>
    <p class="text-muted">Detailed breakdown of your academic performance</p>
</div>

<?php if ($statistics['total_courses'] > 0): ?>
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-4">
            <div class="card bg-primary text-white shadow-sm border-0 py-4 h-100">
                <div class="card-body text-center">
                    <div class="text-white-50 small text-uppercase fw-bold mb-2">Overall GPA</div>
                    <div class="display-3 fw-bold mb-0"><?= number_format($statistics['overall_gpa'], 2) ?></div>
                    <div class="text-white-50">on a 4.0 scale</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-lg-4">
            <div class="card shadow-sm border-0 py-4 h-100">
                <div class="card-body text-center">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Total Courses</div>
                    <div class="display-4 fw-bold text-primary mb-0"><?= $statistics['total_courses'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-lg-4">
            <div class="card shadow-sm border-0 py-4 h-100">
                <div class="card-body text-center">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Total Credits</div>
                    <div class="display-4 fw-bold text-success mb-0"><?= number_format($statistics['total_credits'], 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Performance by Course</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Course</th>
                            <th>Average</th>
                            <th>Letter Grade</th>
                            <th class="text-end pe-3">GPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statistics['courses'] as $c): ?>
                            <?php
                            $gradeColor = 'secondary';
                            $avg = $c['average'];
                            if ($avg >= 90) $gradeColor = 'success';
                            elseif ($avg >= 70) $gradeColor = 'primary';
                            elseif ($avg >= 50) $gradeColor = 'warning';
                            else $gradeColor = 'danger';
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold"><?= htmlspecialchars($c['course']->getCourseCode()) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($c['course']->getCourseName()) ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2"><?= number_format($avg, 1) ?>%</span>
                                        <div class="progress flex-grow-1 d-none d-sm-flex progress-xs min-w-100">
                                            <div class="progress-bar bg-<?= $gradeColor ?>" role="progressbar" style="width: <?= $avg ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $gradeColor ?> px-3"><?= $c['letter'] ?></span>
                                </td>
                                <td class="text-end pe-3 fw-semibold">
                                    <?= number_format($c['gpa'], 1) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm border-0 py-5 text-center">
        <div class="card-body">
            <i class="bi bi-bar-chart display-1 text-muted mb-3"></i>
            <h3>No Statistics Yet</h3>
            <p class="text-muted mb-4">Your academic statistics will appear here once you receive grades for your courses.</p>
            <a href="/student/dashboard" class="btn btn-primary px-4">Go to Dashboard</a>
        </div>
    </div>
<?php endif; ?>
