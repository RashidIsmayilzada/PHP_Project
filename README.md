# DEAR PROFESSOR
Consider that you MUST add the .env file with mentioned variables below for the app to work. 
Technically speaking the command "docker compose up" should work. However sometimes it happened to me that while running this command was not working due to some Docker Daemon not found issue (or something similar).
If it will happen for you as well, you will have to run the container via *Docker Desktop*. If something like this happens, I am very sorry for inconvenience. In case if needed the compose file is the same as it was in the boilerplate and the whole app has been built based on that boilerplate. 

- another important notice is that when I was creating the pages I accidentally created a folder views inside of public, however I was a bit lazy to change all the paths in the code, however it shouldn't be any problem for the app to work. I hope it won't affect my grade :)

# IMPORTANT
**If you don't have php setup on Docker go to the  [First Time Setup](#first-time-setup) section and follow the instructions.**

# Student Management System

A PHP-based web application for managing courses, students, teachers, grades, and enrollments. (EXCEPT SUBMISSIONS, SUBMISSION OF HOMEWORKS DOES NOT WORK)

## Setup & Run

1. Clone the project

2. Start the application:
```bash
docker compose up
```

3. Create `.env` file in the `app` directory with database credentials:
```bash
DB_SERVER_NAME=mysql
DB_USERNAME=root
DB_PASSWORD=secret123
DB_NAME=developmentdb
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost
SESSION_LIFETIME=3600
PASSWORD_MIN_LENGTH=8
GRADE_A_THRESHOLD=90
GRADE_B_THRESHOLD=80
GRADE_C_THRESHOLD=70
GRADE_D_THRESHOLD=60
GPA_A=4.0
GPA_B=3.0
GPA_C=2.0
GPA_D=1.0
GPA_F=0.0
DEFAULT_COURSE_CREDITS=3.0
```

4. Access the application:
   - **Main Application:** http://localhost
   - **PHPMyAdmin:** http://localhost:8080 (user: developer, password: secret123)

## First Time Setup

Install dependencies:
```bash
docker compose run --rm php composer install
```

## Stopping the Application

Press Ctrl+C or run:
```bash
docker compose down
```

## Test Credentials

- **Teacher:** daniel@gmail.com / Test123!
- **Student:** student@example.com / Test123!


## API endpoints

The application contains several secure API endpoints for managing resources.

Examples:
- `GET /api/courses` - Retrieve a list of all courses.
- `POST /api/courses` - Create a new course.
- `GET /api/students/{id}` - Retrieve details of a specific student.
and many more for different use cases.

## Code Structure
Application uses Advanced MVC architecture.
By advanced I mean:
- Usage of Service and repository layers to separate business logic and data access.
- Usage of Dependency Injection to manage loose coupling for better flexibility and scalability.
- Usage of Middleware for handling cross-cutting concerns like authentication and authorization.


## Sessions

The application uses PHP Sessions to manage user authentification(session timeout can be changed in the .env file)

## Security Measures
The application complies with several practices to ensure security:
1. **Password Hashing:** User passwords are securely hashed using PHP's `password_hash()` function before storing them in the database.
2. **Prepared Statements:** All database queries utilize prepared statements to prevent SQL injection attacks.
3. **Input Validation and Sanitization:** User inputs are validated and sanitized to prevent XSS attacks.

## WCAG Compliance

The basic AA and partially AAA standards of WCAG have been implemented in the application to ensure accessibility for all users.

Examples of Features Implemented:

### Basic Error pages in case of unallowed access and not found pages
```php
<body>
    <div class="error-container">
        <div class="icon">🚫</div>
        <div class="error-code">403</div>
        <h1 class="error-title">Access Denied</h1>
        <p class="error-message">
            You don't have permission to access this page. This area is restricted to authorized users only.
        </p>
        <div class="error-actions">
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
            <a href="/login" class="btn btn-primary">Go to Login</a>
        </div>
    </div>
</body>
```

```php
<body>
    <div class="error-container">
        <div class="icon">🔍</div>
        <div class="error-code">404</div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-message">
            Oops! The page you're looking for doesn't exist. It might have been moved or deleted.
        </p>
        <div class="error-actions">
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
            <a href="/login" class="btn btn-primary">Go to Home</a>
        </div>
    </div>
</body>
</html>
```

### Form Labels and Inputs
```php
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Student Grade Management System</p>
        </div>

        <?php if (isset($success) && $success): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error) && $error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                    required
                    placeholder="Enter your email"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Enter your password"
                >
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="login-footer">
            Don't have an account? <a href="/register">Register here</a>
        </div>

        <div class="credentials-hint">
            <strong>Demo Credentials:</strong>
            <ul>
                <li><strong>Teacher:</strong> john.doe@university.edu</li>
                <li><strong>Student:</strong> alice.student@university.edu</li>
                <li><strong>Password:</strong> password123</li>
            </ul>
        </div>
    </div>
</body>
</html>

```