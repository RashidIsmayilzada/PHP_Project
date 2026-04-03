<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/teacher/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/teacher/course-detail/<?= htmlspecialchars((string) $courseId) ?>" class="text-decoration-none">Course</a></li>
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
                    <?php if (!empty($gradeRows)): ?>
                        <?php foreach ($gradeRows as $row): ?>
                            <?php $student = $row['enrollment']; ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold"><?= htmlspecialchars($student->getStudentFullName()) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($student->getStudentNumber() ?? 'N/A') ?></div>
                                </td>
                                <td>
                                    <?= $row['average'] !== null ? htmlspecialchars(number_format($row['average'], 1)) . '%' : 'Not graded yet' ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-dark border">
                                        <?= htmlspecialchars($row['letter']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="/teacher/course-enroll/<?= htmlspecialchars((string) $courseId) ?>" class="btn btn-outline-primary btn-sm">Manage Student</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-grid-3x3 display-4"></i>
                                <p class="mt-2">No enrolled students found for this course yet.</p>
                                <a href="/teacher/course-enroll/<?= htmlspecialchars((string) $courseId) ?>" class="btn btn-primary btn-sm mt-2">Manage Enrollments</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Assignment Grading Status</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Assignment</th>
                        <th>Due Date</th>
                        <th>Grades Placed</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($assignmentRows)): ?>
                        <?php foreach ($assignmentRows as $row): ?>
                            <?php $assignment = $row['assignment']; ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold"><?= htmlspecialchars($assignment->getAssignmentName()) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($assignment->getDescription() ?? 'No description provided.') ?></div>
                                </td>
                                <td><?= htmlspecialchars($assignment->getDueDate() ?? 'No due date') ?></td>
                                <td><?= htmlspecialchars((string)$row['graded_count']) ?></td>
                                <td class="text-end pe-3">
                                    <a href="/teacher/grade-assign/<?= htmlspecialchars((string) $assignment->getAssignmentId()) ?>" class="btn btn-primary btn-sm">View Grades</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                No assignments have been created for this course yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
