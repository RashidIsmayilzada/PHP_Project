<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card shadow">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h1 class="h3 mb-3 fw-normal">Create Account</h1>
                    <p class="text-muted">Join the grade management system</p>
                </div>

                <form method="POST" action="/register">
                    <div class="row mb-3">
                        <div class="col">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control <?= !empty($errors['first_name']) ? 'is-invalid' : '' ?>" id="first_name" name="first_name" value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>" required>
                            <?php if (!empty($errors['first_name'])): ?><div class="invalid-feedback"><?= $errors['first_name'] ?></div><?php endif; ?>
                        </div>
                        <div class="col">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control <?= !empty($errors['last_name']) ? 'is-invalid' : '' ?>" id="last_name" name="last_name" value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>" required>
                            <?php if (!empty($errors['last_name'])): ?><div class="invalid-feedback"><?= $errors['last_name'] ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
                        <?php if (!empty($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" required>
                        <?php if (!empty($errors['password'])): ?><div class="invalid-feedback"><?= $errors['password'] ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" name="role" id="role">
                            <option value="student" <?= ($formData['role'] ?? '') === 'student' ? 'selected' : '' ?>>Student</option>
                            <option value="teacher" <?= ($formData['role'] ?? '') === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                        </select>
                    </div>

                    <div id="student_fields" class="<?= ($formData['role'] ?? '') === 'teacher' ? 'd-none' : '' ?>">
                        <div class="mb-3">
                            <label for="student_number" class="form-label">Student Number</label>
                            <input type="text" class="form-control <?= !empty($errors['student_number']) ? 'is-invalid' : '' ?>" id="student_number" name="student_number" value="<?= htmlspecialchars($formData['student_number'] ?? '') ?>">
                            <?php if (!empty($errors['student_number'])): ?><div class="invalid-feedback"><?= $errors['student_number'] ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button class="btn btn-primary btn-lg" type="submit">Create Account</button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0 text-muted">Already have an account? <a href="/login" class="text-primary text-decoration-none fw-semibold">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('role').addEventListener('change', function() {
        const studentFields = document.getElementById('student_fields');
        if (this.value === 'student') {
            studentFields.classList.remove('d-none');
        } else {
            studentFields.classList.add('d-none');
        }
    });
</script>
