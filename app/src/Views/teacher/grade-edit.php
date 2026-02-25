<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/teacher/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Grade</li>
                </ol>
            </nav>
            <h1 class="h2">Edit Grade</h1>
            <p class="text-muted">Update student performance for this assignment</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/teacher/grade-edit/<?= $grade->getGradeId() ?>">
                    <div class="mb-4 text-center p-3 bg-light rounded">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Student ID</div>
                        <div class="h5 mb-0 text-primary"><?= $grade->getStudentId() ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="points_earned" class="form-label fw-semibold">Points Earned</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="bi bi-award"></i></span>
                            <input type="number" class="form-control" id="points_earned" name="points_earned" step="0.5" required value="<?= htmlspecialchars((string)$grade->getPointsEarned()) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="feedback" class="form-label fw-semibold">Feedback</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="4" placeholder="Provide feedback to the student..."><?= htmlspecialchars($grade->getFeedback() ?? '') ?></textarea>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary btn-lg px-5 flex-grow-1">Update Grade</button>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
