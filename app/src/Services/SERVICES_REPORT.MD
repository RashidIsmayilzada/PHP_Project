# Service Layer Audit Report

## 1. Coupling (Request & Global State)
- **Major Issue in AuthService**: 
    - Directly interacts with `$_SESSION`, `$_COOKIE`, and uses `setcookie()`.
    - Contains `header()` redirects and `exit;` calls in `requireAuth()` and `requireRole()`. 
    - **Violation**: Services should be platform-agnostic business logic containers. Redirects and session persistence handling should reside in Controllers or Middleware.
- **Other Services**: Generally well-decoupled from the Request object, taking arrays or primitives as arguments.

## 2. Granularity & SRP
- **AuthService**: `register()` method handles both user persistence AND session management (automatic login). These should be separate concerns.
- **GradeService**: `getStudentStatistics()` is becoming a "God method" for reporting. While acceptable for a prototype, it should be monitored for complexity.
- **Validation Logic**: Basic validation (checking for empty fields) is currently mixed with business logic in `createX` methods. As the app grows, this should be moved to dedicated Validator classes.

## 3. Error Handling
- **Ambiguity**: Most services return `null` or `false` on failure (e.g., `UserService::createUser` returns `null` if the email is invalid OR if the email exists).
- **Recommendation**: Replace boolean/null returns with **Domain Exceptions**. 
    - Use `ValidationException` for input errors.
    - Use `ConflictException` for duplicate records (e.g., email already exists).
    - This allows Controllers to catch specific errors and return meaningful HTTP status codes (400, 409, etc.).

## 4. Dependency Injection
- **Positives**: Most services correctly use constructor injection for their respective Repositories.
- **Negatives**:
    - **Missing Interface**: `AuthService` is the only service that does not implement an interface. This makes it harder to mock and swap implementations (e.g., switching to an OAuth service).
    - **Hardcoded Logic**: `UserService` and `AuthService` have hardcoded password hashing logic or use static helpers (`PasswordHelper`). A `PasswordHasherInterface` should be injected.
    - **Static Config**: `GradeService` is tightly coupled to `GradeConfig` constants.
    - **Global Functions**: `AuthService` calls `session_start()` and `session_status()` directly instead of using a Session abstraction.

## 5. MVC Boundary Check
- **Violation Found**: `AuthService` violates the Controller/Service boundary by managing HTTP headers and terminating execution (`exit`).
- **Violation Found**: `UserService` and `AssignmentService` perform data transformation (hashing, casting) that might be better suited for the Model or a Data Mapper, though it is currently acceptable within the Service layer for this scale.

## Summary of Action Items
1. **Refactor AuthService**: Remove `header()` and `exit;`. Move session logic to a Session wrapper or handle in the Controller.
2. **Implement Exceptions**: Shift from `return null` to `throw new ...Exception`.
3. **Inject Utilities**: Pass a PasswordHasher service into `UserService` and `AuthService`.
4. **Decouple Sessions**: Inject a Session manager into `AuthService`.
