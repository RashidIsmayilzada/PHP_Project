# Student Management System

A PHP MVC application for managing courses, enrollments, assignments, and grades for teachers and students.

## Current State

This project was refactored for the retake to align better with a cleaner MVC structure:

- Controllers handle request flow and view orchestration
- Services contain business logic
- Repositories handle persistence and SQL
- Views are stored under `app/src/Views`
- Assets live under `app/public/assets`
- JSON API routes are registered in the router
- JavaScript consumes internal API routes for live search

## Project Structure

```text
app/
├── public/
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   └── index.php
├── src/
│   ├── Constants/
│   ├── Controllers/
│   ├── Framework/
│   ├── Models/
│   ├── Repositories/
│   ├── Services/
│   └── Views/
└── tests/
```

## Setup

1. Clone the project.

2. Start the containers:

```bash
docker compose up
```

3. Install PHP dependencies if needed:

```bash
docker compose run --rm php composer install
```

4. Configure environment values *only if you want to override* the defaults.

The app can run with the built-in defaults from [Config.php](app/src/Config.php):

- `DB_SERVER_NAME=mysql`
- `DB_USERNAME=developer`
- `DB_PASSWORD=secret123`
- `DB_NAME=developmentdb`
- `APP_URL=http://localhost`

If you want to override them, create `app/.env` manually:

```env
DB_SERVER_NAME=mysql
DB_USERNAME=developer
DB_PASSWORD=secret123
DB_NAME=developmentdb
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost
SESSION_LIFETIME=3600
PASSWORD_MIN_LENGTH=8
```

Note:
- Grade thresholds are no longer stored in `.env`
- Grade configuration now lives in [GradeConfig.php](app/src/Constants/GradeConfig.php)

5. Make sure the database schema and seed data exist in MariaDB before using the app.

6. Open the application:

- Main app: `http://localhost`
- phpMyAdmin: `http://localhost:8080`

## Test Accounts

If you used the latest seed data discussed during setup:

- Teacher: `teacher1@example.com`
- Teacher: `teacher2@example.com`
- Student: `student1@example.com`
- Student: `student2@example.com`
- Student: `student3@example.com`
- Password for all users: `password123`

## Main Features

- Teacher dashboard with course overview
- Course creation and editing
- Student enrollment management
- Assignment creation and editing
- Grade entry, editing, and teacher-side grade overview
- Student dashboard and statistics view
- Flash messaging and session-based authentication

## API Routes

These routes return JSON and are protected by teacher authorization:

- `GET /api/users`
- `GET /api/students`
- `GET /api/courses`

## JavaScript + API Integration

The project includes frontend API integration through [main.js](app/public/assets/js/main.js).

Implemented example:

- The teacher enrollment page uses `fetch('/api/students')`
- Available students can be searched live by name or email
- The selected-student count updates while filtering

This demonstrates real JS + API integration instead of relying only on server-rendered pages.

## Architecture Notes

The application uses layered MVC with dependency injection:

- Controllers depend on service interfaces
- Services depend on repository interfaces
- Repositories encapsulate SQL access
- Shared grade rules are centralized in [GradeConfig.php](app/src/Constants/GradeConfig.php)
- Password hashing and session handling are abstracted behind service interfaces

## Security

Implemented security-related measures include:

- Password hashing with PHP password APIs
- Prepared statements for database access
- Route-level and controller-level authorization
- Output escaping in many view templates with `htmlspecialchars()`

## Accessibility / UI

The UI uses Bootstrap-based layouts, consistent form labels, feedback alerts, and dedicated error pages for common failure states such as `403` and `404`.

## Running Tests

From the `app` directory:

```bash
./vendor/bin/phpunit tests/Unit
```

## Stop the App

```bash
docker compose down
```
