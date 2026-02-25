<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h1 class="h3 mb-3 fw-normal">Login</h1>
                    <p class="text-muted">Access your grade management dashboard</p>
                </div>

                <form method="POST" action="/login">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" placeholder="name@example.com" required autofocus>
                        <label for="email">Email address</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button class="btn btn-primary btn-lg" type="submit">Sign In</button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0 text-muted">Don't have an account? <a href="/register" class="text-primary text-decoration-none fw-semibold">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
