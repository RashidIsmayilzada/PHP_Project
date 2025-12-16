-- Drop tables if they exist (in correct order to handle foreign keys)
DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS assignments;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('student', 'teacher') NOT NULL,
    student_number VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create courses table
CREATE TABLE courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    teacher_id INT NOT NULL,
    credits DECIMAL(3,1) NULL,
    semester VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Create assignments table
CREATE TABLE assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    assignment_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    max_points DECIMAL(5,2) NOT NULL,
    due_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

-- Create grades table
CREATE TABLE grades (
    grade_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    points_earned DECIMAL(5,2) NOT NULL,
    feedback TEXT NULL,
    graded_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_assignment (student_id, assignment_id)
);

-- Create enrollments table
CREATE TABLE enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    status ENUM('active', 'inactive', 'completed', 'dropped') DEFAULT 'active',
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_course (student_id, course_id)
);

-- Insert sample data for testing

-- Insert teachers
INSERT INTO users (email, password, first_name, last_name, role, student_number) VALUES
('john.doe@university.edu', '$2y$10$abcdefghijklmnopqrstuv', 'John', 'Doe', 'teacher', NULL),
('jane.smith@university.edu', '$2y$10$abcdefghijklmnopqrstuv', 'Jane', 'Smith', 'teacher', NULL);

-- Insert students
INSERT INTO users (email, password, first_name, last_name, role, student_number) VALUES
('alice.student@university.edu', '$2y$10$abcdefghijklmnopqrstuv', 'Alice', 'Johnson', 'student', 'S001'),
('bob.student@university.edu', '$2y$10$abcdefghijklmnopqrstuv', 'Bob', 'Williams', 'student', 'S002'),
('charlie.student@university.edu', '$2y$10$abcdefghijklmnopqrstuv', 'Charlie', 'Brown', 'student', 'S003');

-- Insert courses
INSERT INTO courses (course_code, course_name, description, teacher_id, credits, semester) VALUES
('CS101', 'Introduction to Computer Science', 'Fundamental concepts of programming and computer science', 1, 3.0, 'Fall 2024'),
('CS201', 'Data Structures and Algorithms', 'Study of data structures and algorithm design', 1, 4.0, 'Fall 2024'),
('MATH101', 'Calculus I', 'Introduction to differential and integral calculus', 2, 4.0, 'Fall 2024');

-- Insert enrollments
INSERT INTO enrollments (student_id, course_id, status) VALUES
(3, 1, 'active'),
(3, 2, 'active'),
(4, 1, 'active'),
(4, 3, 'active'),
(5, 2, 'active'),
(5, 3, 'active');

-- Insert assignments
INSERT INTO assignments (course_id, assignment_name, description, max_points, due_date) VALUES
(1, 'Homework 1', 'Basic programming concepts', 100.00, '2024-10-15 23:59:59'),
(1, 'Midterm Project', 'Build a simple calculator', 200.00, '2024-11-01 23:59:59'),
(2, 'Lab 1', 'Implement a linked list', 50.00, '2024-10-20 23:59:59'),
(2, 'Algorithm Analysis', 'Analyze sorting algorithms', 150.00, '2024-11-10 23:59:59'),
(3, 'Problem Set 1', 'Derivatives and limits', 100.00, '2024-10-18 23:59:59');

-- Insert grades
INSERT INTO grades (assignment_id, student_id, points_earned, feedback, graded_at) VALUES
(1, 3, 95.00, 'Excellent work!', '2024-10-16 10:30:00'),
(1, 4, 87.50, 'Good job, minor improvements needed', '2024-10-16 11:00:00'),
(3, 3, 48.00, 'Well implemented', '2024-10-21 14:20:00'),
(3, 5, 50.00, 'Perfect implementation', '2024-10-21 14:25:00'),
(5, 4, 92.00, 'Strong understanding of concepts', '2024-10-19 09:15:00'),
(5, 5, 88.50, 'Good work overall', '2024-10-19 09:20:00');
