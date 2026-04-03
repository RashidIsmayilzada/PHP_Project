-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Apr 03, 2026 at 06:09 PM
-- Server version: 12.2.2-MariaDB-ubu2404
-- PHP Version: 8.3.30

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
(1, 1, 'HTML Basics', 'Build a semantic HTML page.', 100.00, '2026-09-20 00:00:00', '2026-04-02 17:59:00', '2026-04-02 17:59:00'),
(2, 1, 'CSS Layout', 'Create a responsive layout.', 100.00, '2026-10-05 00:00:00', '2026-04-02 17:59:00', '2026-04-02 17:59:00'),
(3, 2, 'SQL Queries', 'Write basic SELECT and JOIN queries.', 100.00, '2026-10-12 00:00:00', '2026-04-02 17:59:00', '2026-04-02 17:59:00'),
(4, 3, 'Project Plan', 'Create a software project proposal.', 100.00, '2027-02-15 00:00:00', '2026-04-02 17:59:00', '2026-04-02 17:59:00');

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
(1, 'WD101', 'Web Development 1', 'Intro to HTML, CSS, and PHP.', 1, 3.0, 'Fall 2026', '2026-04-02 17:59:00', '2026-04-02 17:59:00'),
(2, 'DB201', 'Database Systems', 'Relational databases and SQL fundamentals.', 1, 4.0, 'Fall 2026', '2026-04-02 17:59:00', '2026-04-02 17:59:00'),
(3, 'SE301', 'Software Engineering', 'Design principles and teamwork practices.', 2, 3.0, 'Spring 2027', '2026-04-02 17:59:00', '2026-04-02 17:59:00'),
(4, 'CS1223', 'Test', 'test', 1, 4.0, 'Fall 2030', '2026-04-02 18:06:46', '2026-04-02 18:06:46');

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
(1, 3, 1, 'active', '2026-09-01 00:00:00'),
(2, 4, 1, 'active', '2026-09-01 00:00:00'),
(3, 5, 1, 'active', '2026-09-01 00:00:00'),
(4, 3, 2, 'active', '2026-09-01 00:00:00'),
(5, 4, 2, 'active', '2026-09-01 00:00:00'),
(6, 5, 3, 'active', '2027-02-01 00:00:00'),
(7, 3, 3, 'active', NULL),
(8, 3, 4, 'active', NULL),
(9, 4, 4, 'active', NULL),
(10, 5, 4, 'active', NULL);

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
(1, 1, 3, 92.00, 'Excellent structure and semantics.', '2026-09-21 00:00:00', '2026-04-02 17:59:00'),
(2, 1, 4, 85.00, 'Good work, minor formatting issues.', '2026-09-21 00:00:00', '2026-04-02 17:59:00'),
(3, 1, 5, 74.00, 'Decent effort, improve accessibility.', '2026-09-21 00:00:00', '2026-04-02 17:59:00'),
(4, 2, 3, 88.00, 'Responsive layout works well.', '2026-10-06 00:00:00', '2026-04-02 17:59:00'),
(5, 2, 4, 79.00, 'Good layout, but spacing needs work.', '2026-10-06 00:00:00', '2026-04-02 17:59:00'),
(6, 3, 3, 95.00, 'Very strong SQL understanding.', '2026-10-13 00:00:00', '2026-04-02 17:59:00'),
(7, 3, 4, 81.00, 'Correct queries with small mistakes.', '2026-10-13 00:00:00', '2026-04-02 17:59:00'),
(8, 4, 5, 90.00, 'Clear and realistic project scope.', '2027-02-16 00:00:00', '2026-04-02 17:59:00'),
(9, 4, 3, 45.00, 'very bad project', NULL, '2026-04-02 18:04:44'),
(10, 2, 5, 100.00, 'very good', NULL, '2026-04-02 18:06:08');

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
(1, 'teacher1@example.com', '$2y$12$AMj6kbJIv/ElbvlB5ElJ/eL6NjV6d/5XwyrgERGG4s4IHVblqOKnC', 'Alice', 'Johnson', 'teacher', NULL, '2026-04-02 17:59:00', '2026-04-02 18:01:08'),
(2, 'teacher2@example.com', '$2y$12$AMj6kbJIv/ElbvlB5ElJ/eL6NjV6d/5XwyrgERGG4s4IHVblqOKnC', 'Mark', 'Evans', 'teacher', NULL, '2026-04-02 17:59:00', '2026-04-02 18:01:08'),
(3, 'student1@example.com', '$2y$12$AMj6kbJIv/ElbvlB5ElJ/eL6NjV6d/5XwyrgERGG4s4IHVblqOKnC', 'Emma', 'Brown', 'student', 'S1001', '2026-04-02 17:59:00', '2026-04-02 18:01:08'),
(4, 'student2@example.com', '$2y$12$AMj6kbJIv/ElbvlB5ElJ/eL6NjV6d/5XwyrgERGG4s4IHVblqOKnC', 'Liam', 'Davis', 'student', 'S1002', '2026-04-02 17:59:00', '2026-04-02 18:01:08'),
(5, 'student3@example.com', '$2y$12$AMj6kbJIv/ElbvlB5ElJ/eL6NjV6d/5XwyrgERGG4s4IHVblqOKnC', 'Sophia', 'Miller', 'student', 'S1003', '2026-04-02 17:59:00', '2026-04-02 18:01:08');

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
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
