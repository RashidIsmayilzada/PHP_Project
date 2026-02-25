# Project Documentation & Refactoring Guide (Retake Edition)

This document provides a comprehensive overview of the current application architecture, API references, and a detailed audit of issues based on instructor feedback with specific fix recommendations.

---

## 1. Architectural Overview

### Current "Leaky" MVC
The application currently follows a hybrid MVC pattern that violates the principle of separation of concerns.
- **Router (`index.php`)**: Dispatches requests to Controllers.
- **Controllers**: Act as thin proxies that merely `require` PHP files from the `public/` directory.
- **Views (the "Public" files)**: These files contain heavy business logic, direct Service and Repository instantiations, and data manipulation.

### Target "Clean" Architecture
To pass the retake, the architecture must be strictly separated:
1.  **Controllers**: Instantiate Services (via Dependency Injection), handle request parameters, and perform business orchestration.
2.  **Models**: Represent data entities.
3.  **Services**: Contain core business logic (e.g., GPA calculation).
4.  **Repositories**: Handle raw SQL and PDO operations.
5.  **Views**: Pure HTML/PHP templates that only echo variables passed from the Controller.

---

## 2. API Reference

### Current UI Routes (Managed in `index.php`)
| Method | URI | Description |
| :--- | :--- | :--- |
| GET | `/login` | Shows the login page |
| POST | `/login` | Processes authentication |
| GET | `/student/dashboard` | Student main view |
| GET | `/teacher/course/{id}` | Teacher course management |

### Documented (Missing) API Routes
These routes are mentioned in the `README` but are **not implemented** in the router. They should return JSON.
| Method | URI | Controller Method | Status |
| :--- | :--- | :--- | :--- |
| GET | `/api/users` | `UserController@index` | 404 (Missing in Router) |
| GET | `/api/students` | `UserController@students` | 404 (Missing in Router) |
| GET | `/api/courses` | `CourseController@index` | 404 (Missing in Router) |

---

## 3. Audit & Refactoring Plan (The "Retake" Fixes)

### A. Environment & Configuration
*   **Issue**: App crashes without `.env`. Grade config is in `.env`.
*   **Fix**: 
    1.  Create `app/.env.example`.
    2.  In `app/src/Config.php`, wrap `$dotenv->load()` in a `try-catch` block to prevent fatal errors when the file is missing.
    3.  Move grade thresholds (e.g., `GRADE_A_THRESHOLD`) to a new `app/src/Constants/GradeConfig.php` or a database table.

### B. View Layer Refactoring (Logic Removal)
*   **Issue**: Repository/Service calls inside `app/public/student/course-detail.php`.
*   **Fix**:
    ```php
    // Move this logic FROM app/public/student/course-detail.php
    // TO app/src/Controllers/StudentController.php
    public function courseDetail(int $id): void {
        $course = $this->courseService->findById($id);
        $assignments = $this->assignmentService->findByCourseId($id);
        // ... load all data ...
        
        // Pass data to view
        $viewData = ['course' => $course, 'assignments' => $assignments];
        require __DIR__ . '/../../views/student/course-detail.view.php';
    }
    ```

### C. CSS & Assets
*   **Issue**: "Poor code structure: inline css in multiple files".
*   **Fix**:
    1.  Create `app/public/assets/css/style.css`.
    2.  Extract `<style>` blocks from all files in `app/public/student` and `teacher`.
    3.  Remove `style="..."` attributes. Replace with CSS classes (e.g., `.text-muted`, `.flex-inline`).
    4.  Reference the CSS in a global header template.

### D. Bug Fix: Enrollment
*   **Issue**: "enroll selected student is not enrolling selected students".
*   **Root Cause**: Nested `<form>` tags in `course-enroll.php`. Browsers ignore the outer form's submit button when nested.
*   **Fix**: Remove the individual "Enroll" forms inside the table rows. Use a single form for the whole table or use JavaScript to capture individual clicks and submit a hidden form.

### E. Bug Fix: Course Statistics
*   **Issue**: Fatal error in `statistics.php`.
*   **Root Cause**: Likely a null pointer or type error when `Config` methods are called without a `.env` file, or `$statistics['courses']` being empty/malformed.
*   **Fix**: Add null checks in `student/statistics.php` before calling methods on `$course` and ensure `GradeService` returns a consistent array structure.

### F. Security & Authorization
*   **Issue**: `UserController` has no auth checks.
*   **Fix**:
    1.  Implement a `Middleware` or call `$this->authService->requireRole('admin')` inside every method in `UserController`.
    2.  Register the routes in `index.php`.

---

## 4. Usage Examples

### Using the Service Layer
```php
// Correct way to get student stats from a Controller
$gradeService = new GradeService();
$stats = $gradeService->getStudentStatistics($userId);
```

### Accessing Config
```php
// Use methods, not constants
$dbHost = Config::getDbServerName();
```

---

## 5. Folder Structure Cleanup
Recommendation for the retake:
```text
app/
├── public/
│   ├── assets/           <-- NEW: CSS, JS, Images
│   └── index.php         <-- Entry point
├── src/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/            <-- MOVE: All .php templates here
│   └── ...
└── .env.example          <-- NEW: Template for setup
```

---

## 6. Refactoring Execution Plan

The project will be refactored in 5 key phases:

### Phase 1: Stability & Foundation
-   **Config Robustness**: Modify `app/src/Config.php` to handle missing `.env` files gracefully (try/catch around load).
-   **Grade Config Isolation**: Create a new file `app/src/Constants/GradeConfig.php` and move all grade-related thresholds (`GRADE_A_THRESHOLD`, `GPA_A`, etc.) from `.env` into this dedicated file. Update `GradeService.php` to use these constants.
-   **Asset Organization**: Create `app/public/assets/css/main.css` and `app/public/assets/js/main.js`.
-   **Style Migration**: Extract all `<style>` blocks and `style="..."` attributes from view files into the CSS file.
-   **Global Layouts**: Create `app/src/Views/layout/header.php` and `footer.php` to centralize shared UI.

### Phase 2: View Logic Separation
-   **Template Extraction**: Move `.php` files from `app/public/student/` and `app/public/teacher/` to `app/src/Views/`.
-   **Logic Stripping**: Remove all `new Service()` or `new Repository()` calls from these files. Replace them with logic that expects passed variables (e.g., `$courses`).

### Phase 3: Controller Logic Migration
-   **BaseController Enhancement**: Implement a `render($view, $data)` method to handle view includes and variable extraction.
-   **Route Implementation**: Rewrite `StudentController`, `TeacherController`, `EnrollmentController`, and `GradeController` to perform all data fetching.
-   **Enrollment Fix**: Solve the nested form bug by refactoring `course-enroll.view.php` and handling multi-select in the Controller.

### Phase 4: API & Router Completion
-   **Router Cleanup**: Point all URI's in `app/public/index.php` directly to Controller methods (no more file `require` shortcuts).
-   **API Activation**: Correct the missing routes for `/api/users`, `/api/students`, and `/api/courses` in the router.
-   **Security**: Ensure `UserController` methods enforce authentication (e.g., admin-only access).

### Phase 5: Final Validation & Bug Fixes
-   **Statistics Fix**: Robustly handle empty grade sets in `GradeService` and `statistics.view.php` to prevent division by zero or null pointer errors.
-   **Dead Code Removal**: Delete the legacy files in `app/public/student/` and `app/public/teacher/`.
-   **Compliance Check**: Final pass to ensure no Service/Repository calls remain in any view files.

