-- phpMyAdmin SQL Dump
-- Database: `fast_student_management`

CREATE DATABASE IF NOT EXISTS `fast_student_management`;
USE `fast_student_management`;

-- --------------------------------------------------------

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert users (Password for both is 'password123')
-- password_hash generated using PHP password_hash('password123', PASSWORD_DEFAULT)
INSERT INTO `users` (`username`, `password_hash`, `role`) VALUES
('admin', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'admin'),
('viewer', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'user');

-- --------------------------------------------------------

-- Table structure for table `instructors`
CREATE TABLE `instructors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `department` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert instructors
INSERT INTO `instructors` (`first_name`, `last_name`, `email`, `department`) VALUES
('Ali', 'Khan', 'ali.khan@fast.edu.pk', 'Computer Science'),
('Ayesha', 'Tariq', 'ayesha.tariq@fast.edu.pk', 'Software Engineering'),
('Bilal', 'Ahmed', 'bilal.ahmed@fast.edu.pk', 'Artificial Intelligence'),
('Zainab', 'Raza', 'zainab.raza@fast.edu.pk', 'Computer Science'),
('Usman', 'Sheikh', 'usman.sheikh@fast.edu.pk', 'Data Science');

-- --------------------------------------------------------

-- Table structure for table `students`
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) NOT NULL,
  `dob` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Index on Student Name for search performance
CREATE INDEX `idx_student_name` ON `students` (`first_name`, `last_name`);
CREATE INDEX `idx_student_department` ON `students` (`department`);

-- Insert students
INSERT INTO `students` (`first_name`, `last_name`, `email`, `phone`, `department`, `dob`) VALUES
('Hassan', 'Ali', 'hassan.ali@nu.edu.pk', '0300-1234567', 'Computer Science', '2002-05-14'),
('Fatima', 'Noor', 'fatima.noor@nu.edu.pk', '0301-1234567', 'Software Engineering', '2001-11-23'),
('Ahmed', 'Raza', 'ahmed.raza@nu.edu.pk', '0302-1234567', 'Artificial Intelligence', '2003-02-10'),
('Zahra', 'Batool', 'zahra.batool@nu.edu.pk', '0303-1234567', 'Data Science', '2002-08-19'),
('Omar', 'Farooq', 'omar.farooq@nu.edu.pk', '0304-1234567', 'Computer Science', '2001-04-05'),
('Sara', 'Khan', 'sara.khan@nu.edu.pk', '0305-1234567', 'Software Engineering', '2003-09-12'),
('Hamza', 'Malik', 'hamza.malik@nu.edu.pk', '0306-1234567', 'Artificial Intelligence', '2002-12-01'),
('Maryam', 'Tariq', 'maryam.tariq@nu.edu.pk', '0307-1234567', 'Data Science', '2001-07-22'),
('Abdullah', 'Sheikh', 'abdullah.sheikh@nu.edu.pk', '0308-1234567', 'Computer Science', '2002-03-15'),
('Khadija', 'Zain', 'khadija.zain@nu.edu.pk', '0309-1234567', 'Software Engineering', '2003-06-30'),
('Talha', 'Javed', 'talha.javed@nu.edu.pk', '0310-1234567', 'Artificial Intelligence', '2001-10-08'),
('Amina', 'Qureshi', 'amina.qureshi@nu.edu.pk', '0311-1234567', 'Data Science', '2002-01-25');

-- --------------------------------------------------------

-- Table structure for table `courses`
CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(20) NOT NULL UNIQUE,
  `course_name` varchar(100) NOT NULL,
  `credits` int(11) NOT NULL,
  `fee` decimal(10,2) NOT NULL DEFAULT 15000.00,
  `department` varchar(100) NOT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`instructor_id`) REFERENCES `instructors`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `idx_course_instructor` ON `courses` (`instructor_id`);

-- Insert courses
INSERT INTO `courses` (`course_code`, `course_name`, `credits`, `fee`, `department`, `instructor_id`) VALUES
('CS101', 'Introduction to Computing', 3, 20000.00, 'Computer Science', 1),
('SE201', 'Software Requirement Engineering', 3, 18000.00, 'Software Engineering', 2),
('AI301', 'Machine Learning', 4, 25000.00, 'Artificial Intelligence', 3),
('CS202', 'Data Structures', 4, 22000.00, 'Computer Science', 4),
('DS401', 'Big Data Analytics', 3, 21000.00, 'Data Science', 5),
('SE302', 'Software Quality Assurance', 3, 18000.00, 'Software Engineering', 2),
('AI402', 'Deep Learning', 3, 25000.00, 'Artificial Intelligence', 3),
('CS303', 'Database Systems', 4, 22000.00, 'Computer Science', 1);

-- --------------------------------------------------------

-- Table structure for table `enrollments`
CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_enrollment` (`student_id`, `course_id`),
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `idx_enrollment_student` ON `enrollments` (`student_id`);
CREATE INDEX `idx_enrollment_course` ON `enrollments` (`course_id`);

-- Insert enrollments
INSERT INTO `enrollments` (`student_id`, `course_id`) VALUES
(1, 1), (1, 4), (1, 8),
(2, 2), (2, 6),
(3, 3), (3, 7),
(4, 5),
(5, 1), (5, 4),
(6, 2), (6, 6),
(7, 3), (7, 7),
(8, 5), (8, 8),
(9, 1), (9, 4), (9, 8),
(10, 2), (10, 6),
(11, 3), (11, 7),
(12, 5);

-- --------------------------------------------------------

-- CREATE VIEW: student_course_summary
CREATE OR REPLACE VIEW `v_student_course_summary` AS
SELECT 
    s.id AS student_id,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    s.department,
    COUNT(e.course_id) AS total_courses_enrolled,
    SUM(c.credits) AS total_credits
FROM 
    students s
LEFT JOIN enrollments e ON s.id = e.student_id
LEFT JOIN courses c ON e.course_id = c.id
GROUP BY 
    s.id, s.first_name, s.last_name, s.department;

