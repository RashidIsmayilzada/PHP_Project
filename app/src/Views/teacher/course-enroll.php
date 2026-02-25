<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/teacher/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/teacher/course-detail/<?= $courseId ?>" class="text-decoration-none"><?= htmlspecialchars($course->getCourseCode()) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Enrollments</li>
        </ol>
    </nav>
    <h1 class="h2">Manage Enrollments</h1>
    <p class="text-muted"><?= htmlspecialchars($course->getCourseCode()) ?> - <?= htmlspecialchars($course->getCourseName()) ?></p>
</div>

<div class="row g-4">
    <!-- Enrolled Students -->
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Enrolled Students</h5>
                <span class="badge bg-primary rounded-pill"><?= count($enrollments) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($enrollments)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-people text-muted display-4"></i>
                        <p class="mt-2 text-muted">No students enrolled</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Student ID</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrollments as $enrollment): ?>
                                    <tr>
                                        <td class="ps-3">ID: <?= $enrollment->getStudentId() ?></td>
                                        <td>
                                            <span class="badge rounded-pill bg-<?= $enrollment->getStatus() === 'active' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($enrollment->getStatus()) ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="unenroll">
                                                <input type="hidden" name="enrollment_id" value="<?= $enrollment->getEnrollmentId() ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Unenroll this student?')">
                                                    <i class="bi bi-person-dash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Available Students -->
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Available Students</h5>
                <span class="badge bg-secondary rounded-pill"><?= count($availableStudents) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($availableStudents)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle text-success display-4"></i>
                        <p class="mt-2 text-muted">All students are already enrolled</p>
                    </div>
                <?php else: ?>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" id="studentSearch" class="form-control" placeholder="Search students by name or email...">
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action" value="bulk_enroll">
                        <div class="table-responsive scrollable-y-400">
                            <table class="table table-hover align-middle mb-0" id="studentsTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="w-40"></th>
                                        <th>Name</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($availableStudents as $student): ?>
                                        <tr class="student-row" data-search="<?= htmlspecialchars(strtolower($student->getFullName() . ' ' . $student->getEmail())) ?>">
                                            <td>
                                                <div class="form-check">
                                                    <input type="checkbox" name="student_ids[]" value="<?= $student->getUserId() ?>" class="form-check-input student-checkbox">
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($student->getFullName()) ?></td>
                                            <td class="text-muted small"><?= htmlspecialchars($student->getEmail()) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <div class="text-muted small">
                                <span id="selectedCount" class="fw-bold text-primary">0</span> students selected
                            </div>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-person-plus me-1"></i> Enroll Selected
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('studentSearch');
    const tableRows = document.querySelectorAll('.student-row');
    const checkboxes = document.querySelectorAll('.student-checkbox');
    const countDisplay = document.getElementById('selectedCount');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            tableRows.forEach(row => {
                const text = row.getAttribute('data-search');
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
            countDisplay.textContent = checkedCount;
        });
    });
</script>
