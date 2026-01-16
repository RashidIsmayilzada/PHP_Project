# DEAR PROFESSOR
Consider that you MUST add the .env file with mentioned variables below for the app to work. 
Technically speaking the command "docker compose up" should work. However sometimes it happened to me that while running this command was not working due to some Docker Daemon not found issue (or something similar).
If it will happen for you as well, you will have to run the container via *Docker Desktop*. If something like this happens, I am very sorry for inconvenience. In case if needed the compose file is the same as it was in the boilerplate and the whole app has been built based on that boilerplate. 

- another important notice is that when I was creating the pages I accidentally created a folder views inside of public, however I was a bit lazy to change all the paths in the code, however it shouldn't be any problem for the app to work. I hope it won't affect my grade :)

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

