<div class="row justify-content-center align-items-center py-5">
    <div class="col-md-6 text-center">
        <div class="display-1 fw-bold text-warning mb-4"><i class="bi bi-exclamation-triangle"></i></div>
        <h1 class="h2 mb-3">Something went wrong</h1>
        <div class="alert alert-light border shadow-sm mb-5">
            <p class="text-muted mb-0"><?= htmlspecialchars($errorMessage ?? 'An unexpected error occurred.') ?></p>
        </div>
        <div class="d-flex justify-content-center gap-3">
            <a href="/" class="btn btn-primary btn-lg px-4">Go to Home</a>
            <button onclick="history.back()" class="btn btn-outline-secondary btn-lg px-4">Go Back</button>
        </div>
    </div>
</div>
