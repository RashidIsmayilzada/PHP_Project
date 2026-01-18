-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Jan 18, 2026 at 02:27 PM
-- Server version: 12.0.2-MariaDB-ubu2404
-- PHP Version: 8.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `developmentdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `assignment_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `assignment_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `max_points` decimal(5,2) NOT NULL,
  `due_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`assignment_id`, `course_id`, `assignment_name`, `description`, `max_points`, `due_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'Homework 1', 'Basic programming concepts', 100.00, '2024-10-15 23:59:59', '2025-12-22 15:28:15', '2025-12-22 15:28:15'),
(2, 1, 'Midterm Project', 'Build a simple calculator', 200.00, '2024-11-01 23:59:59', '2025-12-22 15:28:15', '2025-12-22 15:28:15'),
(3, 2, 'Lab 1', 'Implement a linked list', 50.00, '2024-10-20 23:59:59', '2025-12-22 15:28:15', '2025-12-22 15:28:15'),
(4, 2, 'Algorithm Analysis', 'Analyze sorting algorithms', 150.00, '2024-11-10 23:59:59', '2025-12-22 15:28:15', '2025-12-22 15:28:15'),
(5, 3, 'Problem Set 1', 'Derivatives and limits', 100.00, '2024-10-18 23:59:59', '2025-12-22 15:28:15', '2025-12-22 15:28:15'),
(6, 6, 'Test', 'test', 20.00, '2026-03-02 00:00:00', '2025-12-24 19:40:39', '2025-12-24 19:40:39'),
(7, 9, 'MOCK EXAM', '', 2.00, '2026-02-02 00:00:00', '2026-01-05 10:52:52', '2026-01-05 10:52:52'),
(8, 10, 'Midterm Exam', 'test', 80.00, '2006-03-02 00:00:00', '2026-01-11 15:13:46', '2026-01-11 15:13:46');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `teacher_id` int(11) NOT NULL,
  `credits` decimal(3,1) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_code`, `course_name`, `description`, `teacher_id`, `credits`, `semester`, `created_at`, `updated_at`) VALUES
(1, 'CS101', 'Introduction to Computer Science', 'Fundamental concepts of programming and computer science', 1, 3.0, 'Fall 2024', '2025-12-22 15:28:15', '2025-12-22 15:28:15'),
(2, 'CS201', 'Data Structures and Algorithms', 'Study of data structures and algorithm design', 1, 4.0, 'Fall 2024', '2025-12-22 15:28:15', '2025-12-22 15:28:15'),
(3, 'MATH101', 'Calculus I', 'Introduction to differential and integral calculus', 2, 4.0, 'Fall 2024', '2025-12-22 15:28:15', '2025-12-22 15:28:15'),
(6, 'CS202', 'Java Advanced', 'Test Description', 9, 3.0, 'Fall 2025', '2025-12-24 19:38:58', '2025-12-24 19:38:58'),
(7, 'CS60', 'Not an Introduction to Computer SCI', 'test', 10, 12.0, 'Term 1', '2025-12-25 16:40:26', '2025-12-25 16:40:26'),
(9, 'CS205', 'Introcution to how to create apps', 'klsjflksdf', 12, 3.0, 'Fall 2026', '2026-01-05 10:51:42', '2026-01-05 10:51:42'),
(10, 'CS591', 'Intro to something that will work', 'test', 14, 3.0, 'Fall 2027', '2026-01-11 15:12:01', '2026-01-11 15:12:01');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `status` enum('active','inactive','completed','dropped') DEFAULT 'active',
  `enrollment_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `course_id`, `status`, `enrollment_date`) VALUES
(1, 3, 1, 'active', '2025-12-22 15:28:15'),
(2, 3, 2, 'active', '2025-12-22 15:28:15'),
(3, 4, 1, 'active', '2025-12-22 15:28:15'),
(4, 4, 3, 'active', '2025-12-22 15:28:15'),
(5, 5, 2, 'active', '2025-12-22 15:28:15'),
(6, 5, 3, 'active', '2025-12-22 15:28:15'),
(7, 5, 6, 'active', '2025-12-24 19:39:26'),
(8, 5, 7, 'active', '2025-12-25 16:40:38'),
(9, 6, 7, 'active', '2025-12-25 16:40:47'),
(10, 8, 7, 'active', '2025-12-25 16:40:51'),
(11, 3, 7, 'active', '2025-12-25 16:40:56'),
(12, 4, 7, 'active', '2025-12-25 16:40:59'),
(13, 5, 9, 'active', '2026-01-05 10:51:55'),
(14, 6, 9, 'active', '2026-01-05 10:52:08'),
(15, 8, 9, 'active', '2026-01-05 10:52:18'),
(16, 3, 9, 'active', '2026-01-05 10:52:23'),
(17, 11, 9, 'active', '2026-01-05 10:52:28'),
(18, 5, 10, 'active', '2026-01-11 15:12:10'),
(19, 13, 10, 'active', '2026-01-11 15:12:23'),
(20, 6, 10, 'active', '2026-01-11 15:12:30'),
(23, 8, 10, 'active', '2026-01-16 18:56:50'),
(24, 3, 10, 'active', '2026-01-16 18:56:56'),
(25, 11, 10, 'active', '2026-01-16 18:57:04'),
(26, 15, 10, 'active', '2026-01-16 18:57:08');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `points_earned` decimal(5,2) NOT NULL,
  `feedback` text DEFAULT NULL,
  `graded_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`grade_id`, `assignment_id`, `student_id`, `points_earned`, `feedback`, `graded_at`, `updated_at`) VALUES
(1, 1, 3, 95.00, 'Excellent work!', '2024-10-16 10:30:00', '2025-12-22 15:28:15'),
(2, 1, 4, 87.50, 'Good job, minor improvements needed', '2024-10-16 11:00:00', '2025-12-22 15:28:15'),
(3, 3, 3, 48.00, 'Well implemented', '2024-10-21 14:20:00', '2025-12-22 15:28:15'),
(4, 3, 5, 50.00, 'Perfect implementation', '2024-10-21 14:25:00', '2025-12-22 15:28:15'),
(5, 5, 4, 92.00, 'Strong understanding of concepts', '2024-10-19 09:15:00', '2025-12-22 15:28:15'),
(6, 5, 5, 88.50, 'Good work overall', '2024-10-19 09:20:00', '2025-12-22 15:28:15'),
(7, 6, 5, 20.00, 'Good Job', '2025-12-24 19:40:50', '2025-12-24 19:40:50'),
(8, 8, 13, 73.00, 'You did great on the exam', '2026-01-11 15:28:58', '2026-01-11 15:28:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role` enum('student','teacher') NOT NULL,
  `student_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `first_name`, `last_name`, `role`, `student_number`, `created_at`, `updated_at`) VALUES
(1, 'john.doe@university.edu', '$2y$12$k29VP5YeYjlbjQvfpn5CzeTxoVX5DNHMtdXsrYpxp5.NvOLf.kyLq', 'John', 'Doe', 'teacher', NULL, '2025-12-22 15:28:15', '2025-12-22 15:28:15'),
(2, 'jane.smith@university.edu', '$2y$12$k29VP5YeYjlbjQvfpn5CzeTxoVX5DNHMtdXsrYpxp5.NvOLf.kyLq', 'Jane', 'Smith', 'teacher', NULL, '2025-12-22 15:28:15', '2025-12-22 15:28:15'),
(3, 'alice.student@university.edu', '$2y$12$k29VP5YeYjlbjQvfpn5CzeTxoVX5DNHMtdXsrYpxp5.NvOLf.kyLq', 'Alice', 'Johnson', 'student', 'S001', '2025-12-22 15:28:15', '2025-12-22 15:28:15'),
(4, 'bob.student@university.edu', '$2y$12$k29VP5YeYjlbjQvfpn5CzeTxoVX5DNHMtdXsrYpxp5.NvOLf.kyLq', 'Bob', 'Williams', 'student', 'S002', '2025-12-22 15:28:15', '2025-12-22 15:28:15'),
(5, 'charlie.student@university.edu', '$2y$12$k29VP5YeYjlbjQvfpn5CzeTxoVX5DNHMtdXsrYpxp5.NvOLf.kyLq', 'Charlie', 'Brown', 'student', 'S003', '2025-12-22 15:28:15', '2025-12-22 15:28:15'),
(6, 'rashidismayilzade@gmail.com', '$2y$12$JOn9V1V1aYW.f/xSKovUOeBecd8G37X3zFA1KKE9ZUhBmDLORV6QS', 'Rashid', 'Ismayilzada', 'student', '728313', '2025-12-24 18:19:58', '2025-12-24 18:19:58'),
(7, 'dan@gmail.com', '$2y$12$kBa6qAOCikvWx1IQJ5Lvze./lfitRs/hKr5xotFkK9BRcR/FbWD.i', 'Daniel', 'Breczinski', 'teacher', '', '2025-12-24 18:21:30', '2025-12-24 18:21:30'),
(8, 'rashidismayilzada.id@gmail.com', '$2y$12$nhK03dIHLZicq/lNsCFpXuZItuOFWPudetZiiYTtRq5XGKZZ7qoj2', 'Rashid', 'Ismayilzada', 'student', '728313', '2025-12-24 19:37:24', '2025-12-24 19:37:24'),
(9, 'frankdersjant@gmail.com', '$2y$12$69oeX4uUfVv7zpsOElMtHe.Z5FhruOXxXQPC6Ikr/q2QmmwZ1Z9Fa', 'Frank', 'Dersjant', 'teacher', '', '2025-12-24 19:38:14', '2025-12-24 19:38:14'),
(10, 'sarxan@gmail.com', '$2y$12$cmHpkiL3XmsCagU70auYZeH0zRR8FdM/pfFPUuZhbRuWppyo5407K', 'Sarxan', 'Lemberanski', 'teacher', '728313', '2025-12-25 16:39:44', '2025-12-25 16:39:44'),
(11, '783412@gmail.com', '$2y$12$pszcn5nJpWNPpZTXNnJA7.gF6/6ZGF7P4KL0.yy7KDHNkF42j9OQ2', 'Leandro', 'Nunez', 'student', '783412', '2026-01-05 10:50:38', '2026-01-05 10:50:38'),
(12, 'test1@gmail.com', '$2y$12$tplYTAm/23G3ceJDoXteqesmKExfc3ppS.iFXH/h3K0q7dw3JP9oG', 'Test', 'Testowski', 'teacher', '', '2026-01-05 10:51:13', '2026-01-05 10:51:13'),
(13, 'azad@gmail.com', '$2y$12$KQRZnx3oOSwBldxHlRal3.TmkzPBY0uqYsd.1Rnefbph9dwFXrrW6', 'Azad', 'Hamidov', 'student', '758438', '2026-01-11 15:06:44', '2026-01-11 15:06:44'),
(14, 'daniel@gmail.com', '$2y$12$APerfE8vVRRbVu8auusw4uWpWANodQgjuH3wIfwF3tRw37nlNwM0O', 'Daniel', 'Breczinski', 'teacher', '', '2026-01-11 15:11:31', '2026-01-11 15:11:31'),
(15, 'student@example.com', '$2y$12$lc7q9zWxVvggphl9cubGXuwWw4iq1ZN4W9K0ZDYk6ubk7SGT2amDm', 'Test', 'Student', 'student', '235232', '2026-01-16 18:56:15', '2026-01-16 18:56:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `unique_student_course` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`grade_id`),
  ADD UNIQUE KEY `unique_student_assignment` (`student_id`,`assignment_id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`assignment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
