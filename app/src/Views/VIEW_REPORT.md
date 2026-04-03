# View Audit Report: Zero Logic in Views

## 1. Zero Logic Violations
Multiple views contain business logic, calculations, and data processing that should reside in the **Service** or **Controller** layer.

### Major Violations:
- **`student/dashboard.php`**:
    - **GPA Calculation**: Direct formatting of `$overallGPA`.
    - **Active Course Counting**: A `foreach` loop and conditional `if ($data['status'] === 'active')` to count active courses.
    - **Total Credits Summation**: A `foreach` loop to sum course credits.
    - **UI Color Logic**: Complex nested `if/elseif` blocks to determine `gradeColor` based on percentage.
- **`student/statistics.php`**: Contains similar GPA and credit summation logic.
- **`partials/navbar.php`**: Direct dependency on `\App\Framework\Auth::role()`. This should be passed from the Controller via the View Model.
- **`partials/footer.php`**: Usage of `date('Y')`. While minor, a "SystemService" or "Config" should ideally provide this.

---

## 2. XSS (Cross-Site Scripting) Risks
A significant number of variables are echoed using the shorthand `<?= $var ?>` without `htmlspecialchars()`, leaving the application vulnerable to XSS if these variables contain user-generated content.

### High Risk Examples:
- **`auth/register.php`**: `<?= $errors['first_name'] ?>`, `<?= $errors['email'] ?>`, etc. Error messages often echo back user input.
- **`student/dashboard.php`**: `<?= $activeCount ?>`, `<?= $totalCredits ?>`, `<?= $status ?>`, `<?= $gradeColor ?>`, `<?= $letterGrade ?>`.
- **`teacher/` (All files)**: Course IDs, student IDs, and counts are echoed without escaping.
- **`partials/head.php`**: `<?= $pageTitle ?>` is unescaped.

---

## 3. Hardcoded Strings (Localization)
The following files contain hardcoded UI text that should be moved to translation/language files (e.g., `en.php`, `fr.php`):
- **`auth/register.php`**: "Create Account", "First Name", "Role", etc.
- **`student/dashboard.php`**: "Student Dashboard", "Overview of your academic progress", "Active Courses", etc.
- **`teacher/dashboard.php`**: "Teacher Dashboard", "Manage Courses", etc.
- **`errors/404.php`**: "Page Not Found".

---

## 4. Asset Management
CSS and JS are being included inconsistently.

### Inline Scripts/Styles:
- **`auth/register.php`**: Inline `<script>` for toggling "Student Number" visibility. This should be moved to a dedicated `register.js` file.
- **`teacher/course-enroll.php`**: Inline `<script>` for handling student selection checkboxes.
- **`partials/head.php`**: External CSS is linked via CDN or static paths. A proper asset bundler (like Vite or Webpack) or a `AssetHelper` should be used to manage versions/cache-busting.

---

## Recommendations
1. **Sanitize Everything**: Wrap all `<?= ... ?>` tags in `htmlspecialchars()` or use a dedicated `e()` helper function.
2. **Move Logic to Services**: Calculations for GPA, credit sums, and status counts must move to a `StudentService`.
3. **View Models**: Use View Models to pass pre-calculated UI state (like `gradeColor` or `activeCount`) to the view.
4. **Externalize Assets**: Move all inline `<script>` and `<style>` blocks to the `/public` directory and include them via `<script src="...">`.
5. **Localization**: Implement a translation helper `__('string')` and move all text to language files.
