# Repository Layer Review Report

This report evaluates the data access and persistence layer based on the core mandates of the MVC architecture, focusing on Logic Leakage, Performance, Consistency, and Abstraction.

---

## 1. Logic Leakage (Business Rules in Data Layer)
The Repository's only job is data retrieval and persistence. Any decision-making based on the data's meaning should live in the Service layer.

*   **Hardcoded Statuses/Roles:**
    *   `EnrollmentRepository::findActiveEnrollmentsByStudentId`: Hardcodes the string `'active'`.
    *   `UserRepository::findAllStudents` and `findAllTeachers`: Hardcode the strings `'student'` and `'teacher'`.
    *   *Finding:* These business domain values are "leaking" into the persistence layer. If a role name or status changes, multiple repositories must be updated.
*   **Conditional Hydration Logic:**
    *   `EnrollmentRepository::mapRowToEnrollment`: Uses `isset()` checks to conditionally populate student names from a `JOIN`. This introduces "magic" properties into the `Enrollment` model that only exist when specific queries are executed, making the model's state unpredictable outside of this repository.

## 2. Performance & Query Efficiency
Evaluates the efficiency of data retrieval, specifically looking for N+1 issues and data handling.

*   **Positive: Optimized Joins:**
    *   Most repositories correctly use `JOIN` statements (e.g., `EnrollmentRepository::findByCourseId`, `CourseRepository::findByStudentId`) to fetch related data in a single trip, preventing N+1 query issues in the Service layer.
*   **Negative: Data Mapping Inconsistency:**
    *   `GradeRepository::getGradeDataForCourseAndStudent`: This method returns a raw associative array instead of mapped Model objects. This forces the calling layer to understand the database schema, increasing coupling and bypassing the repository's abstraction.
*   **Missing Pagination:**
    *   `findAll()` methods across all repositories lack `LIMIT` and `OFFSET` parameters. As the database grows, these methods will cause memory exhaustion and performance degradation.

## 3. Consistency
Consistency in naming and structure ensures the codebase is predictable and maintainable.

*   **Naming Divergence:**
    *   Most methods use the `find` prefix (`findById`, `findByEmail`), but `GradeRepository` uses `getGradeDataForCourseAndStudent`.
    *   `UserRepository` uses specific methods like `findAllStudents()` instead of a generic, reusable `findByRole(string $role)`.
*   **Parameter Naming:**
    *   Primary key parameter naming varies between `delete(int $id)` and `delete(int $entityId)`.
*   **Result Sorting:**
    *   `EnrollmentRepository::findAll` is missing an `ORDER BY` clause, unlike the other repositories which include default sorting for UI consistency.

## 4. Abstraction
Evaluates how well the implementation details are hidden from the rest of the application.

*   **Raw SQL Dependency:**
    *   All repositories are tightly coupled to the SQL dialect and schema. The implementation details (table names, column names) are hardcoded within every method.
*   **Schema Exposure:**
    *   The `GradeRepository` bypasses the `mapRowToGrade` method in `getGradeDataForCourseAndStudent`, leaking the internal database schema directly to the Service/Controller layers.
*   **Tight Coupling to PDO/Base Class:**
    *   The repositories are dependent on a base `Repository` class that expects raw SQL strings, making it difficult to swap the persistence engine (e.g., for an external API or NoSQL database) without a full rewrite of the logic.

---

## 5. Summary of Recommendations
1.  **Refactor Constants:** Move roles (`student`, `teacher`) and statuses (`active`) to Model constants or pass them as arguments from the Service layer.
2.  **Standardize GradeRepository:** Ensure all methods return mapped Model objects or DTOs to maintain the abstraction barrier.
3.  **Generic Filtering:** Replace role-specific finders in `UserRepository` with a generic `findByRole` method.
4.  **Implement Pagination:** Add `limit` and `offset` parameters to all `findAll` and bulk search methods.
5.  **Standardize Naming:** Align all methods to use the `find...` prefix for consistency across the layer.
