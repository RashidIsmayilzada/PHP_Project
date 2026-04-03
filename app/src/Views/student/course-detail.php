<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/student/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($course->getCourseCode()) ?></li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-primary mb-2"><?= htmlspecialchars($course->getCourseCode()) ?></span>
            <h1 class="h2"><?= htmlspecialchars($course->getCourseName()) ?></h1>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Course Description</h5>
                <p class="text-muted lh-lg">
                    <?= nl2br(htmlspecialchars($course->getDescription() ?? 'No description available.')) ?>
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
                <h5 class="mb-0">Assignments & Grades</h5>
            </div>
            <div class="card-body p-0">
	                <div class="table-responsive">
	                    <table class="table table-hover align-middle mb-0">
	                        <thead class="table-light">
	                            <tr>
	                                <th class="ps-3">Assignment</th>
                                <th>Due Date</th>
                                <th>Grade</th>
                                <th class="text-end pe-3">Max Points</th>
                            </tr>
	                        </thead>
	                        <tbody>
	                            <?php if (!empty($assignmentRows)): ?>
	                                <?php foreach ($assignmentRows as $row): ?>
	                                    <?php
	                                    $assignment = $row['assignment'];
	                                    $grade = $row['grade'];
	                                    ?>
	                                    <tr>
	                                        <td class="ps-3">
	                                            <div class="fw-semibold"><?= htmlspecialchars($assignment->getAssignmentName()) ?></div>
	                                            <div class="small text-muted"><?= htmlspecialchars($assignment->getDescription() ?? 'No description available.') ?></div>
	                                        </td>
	                                        <td><?= htmlspecialchars($assignment->getDueDate() ?? 'No due date') ?></td>
	                                        <td>
	                                            <?= htmlspecialchars($grade ? number_format($grade->getPointsEarned(), 1) : 'Not graded yet') ?>
	                                        </td>
	                                        <td class="text-end pe-3"><?= htmlspecialchars(number_format($assignment->getMaxPoints(), 1)) ?></td>
	                                    </tr>
	                                <?php endforeach; ?>
	                            <?php else: ?>
	                                <tr>
	                                    <td colspan="4" class="text-center py-4 text-muted">
	                                        <i class="bi bi-file-earmark-text display-4"></i>
	                                        <p class="mt-2">No assignments found for this course.</p>
	                                    </td>
	                                </tr>
	                            <?php endif; ?>
	                        </tbody>
	                    </table>
	                </div>
	            </div>
	        </div>
    </div>

	    <div class="col-lg-4">
	        <div class="card shadow-sm bg-primary text-white border-0 mb-4">
	            <div class="card-body p-4 text-center">
	                <div class="text-white-50 small text-uppercase fw-bold mb-1">Your Grade</div>
	                <div class="display-4 fw-bold"><?= htmlspecialchars($courseAverageDisplay ?? 'N/A') ?></div>
	                <div class="text-white-50 small">Course Average</div>
	            </div>
	        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Teacher Information</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
	                    <div class="flex-shrink-0">
	                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center avatar-md">
	                            <i class="bi bi-person text-primary fs-4"></i>
	                        </div>
	                    </div>
	                    <div class="flex-grow-1 ms-3">
	                        <h6 class="mb-0"><?= htmlspecialchars($teacher ? $teacher->getFullName() : 'Unknown Teacher') ?></h6>
	                        <small class="text-muted">Primary Instructor</small>
	                    </div>
	                </div>
            </div>
        </div>
    </div>
</div>
