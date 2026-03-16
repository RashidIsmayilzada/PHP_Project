# CTO Architectural Synthesis & Risk Assessment Report

## 1. Cross-Layer Contamination: The "Leaky Pipeline"
The 'Separation of Concerns' is failing most consistently at the **Service-to-Infrastructure** and **Service-to-View** boundaries.

*   **Service Infrastructure Leakage:** The `AuthService` is the primary offender, treating itself as a Controller by handling HTTP redirects, headers, and global session state (`$_SESSION`, `exit;`). This makes the service non-testable in isolation and couples business logic to the web request lifecycle.
*   **Domain Leakage into Persistence:** Repositories are making business decisions by hardcoding roles (`'student'`) and statuses (`'active'`). This logic belongs in the Service layer or as Model constants.
*   **Data Transformation in the Wrong Place:** The View layer is performing heavy lifting (GPA calculations, UI color logic) that should be pre-calculated by Services and passed via View Models.

## 2. Technical Debt Heatmap: Top 3 Danger Zones
| Zone | Layers Involved | Impact |
| :--- | :--- | :--- |
| **Authentication Flow** | Service + Framework | High risk of session fixation or bypass due to `AuthService` mixing persistence with session management and direct global access. |
| **Student Progress/GPA** | View + Service + Repo | Logic is fragmented: Repo returns raw data, Service has a "God Method," and View recalculates GPA/Credits. A change in grading logic requires 3+ file edits. |
| **Grade Management** | Repo + Service | `GradeRepository` returns raw arrays, bypassing the Model layer entirely, which leads to "Magic Array" dependencies in the Service and potentially the View. |

## 3. The 'Invisible' Redundancies
*   **The Model "Ghost" Layer:** While Models exist, they are being bypassed by the `GradeRepository` (returning arrays) and "hydrated" with magic properties in the `EnrollmentRepository`. This renders the `src/Models` directory partially redundant as the application relies on raw SQL result sets rather than typed objects.
*   **Dead-End Validation:** Validation logic is mentioned as being "mixed" in services, but the View report shows the UI is re-validating or simply echoing errors without a standardized structure. The "Validation logic" in services likely isn't being caught effectively because services return `null` instead of throwing catchable exceptions.

## 4. Security Consolidation: The #1 Vulnerability
**Systemic XSS (Cross-Site Scripting).**
The codebase treats user-generated data as "safe by default" across almost every view. The lack of a global escaping strategy (`htmlspecialchars`) combined with the practice of echoing error messages (which contain user input) creates a massive attack surface.

---

## 5. The Refactoring Roadmap

| Phase | Priority | Action Item | Target Layer |
| :--- | :--- | :--- | :--- |
| **P1** | 1 | **Global XSS Protection:** Implement an `e()` helper and wrap all view echoes. | View |
| **P1** | 2 | **Decouple AuthService:** Move `header()` and `$_SESSION` logic to Controllers/Session Wrapper. | Service |
| **P1** | 3 | **Domain Exceptions:** Replace `return null/false` with specific Exceptions (e.g., `UserNotFoundException`). | Service |
| **P2** | 4 | **Logic Extraction:** Move GPA and Course counting from Views to `StudentService`. | View / Service |
| **P2** | 5 | **Constant Migration:** Move `'active'`, `'student'`, `'teacher'` strings to Enums or Model constants. | Repo / Model |
| **P2** | 6 | **Repository Standardization:** Ensure all methods return Model objects; fix `GradeRepository` array leak. | Repository |
| **P2** | 7 | **View Models:** Introduce View Models to pass pre-calculated UI state (e.g., `gradeColor`). | Controller / View |
| **P3** | 8 | **Pagination:** Add `limit`/`offset` to all `findAll` methods to prevent memory exhaustion. | Repository |
| **P3** | 9 | **DI for Utilities:** Inject `PasswordHasher` and `SessionManager` into services. | Service / Framework |
| **P3** | 10 | **Asset Management:** Externalize inline JS/CSS and implement a basic asset helper. | View |

---

### Maintainability Score: 42/100
*Current State: "Functional but Fragile." The application is highly coupled, making it difficult to test or extend without introducing side effects. The security posture is currently unacceptable for production.*

### The 'Golden Rule' for the Team:
> **"The Layer Below Never Knows the Layer Above Exists."**
> *(The Repository doesn't know about business statuses; the Service doesn't know about HTTP redirects; the View doesn't know about database schemas.)*
