-- FAST University Management System Database Schema
-- Complete database structure with all tables, relationships, and sample data

CREATE DATABASE IF NOT EXISTS `fast_university_management`;
USE `fast_university_management`;

-- Users table for authentication
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','instructor','student','parent') NOT NULL DEFAULT 'student',
  `email` varchar(100) NOT NULL UNIQUE,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Students table
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text,
  `class` varchar(20) NOT NULL, -- e.g., '10th Grade', 'BSCS-1'
  `section` varchar(10) NOT NULL, -- e.g., 'A', 'B', 'C'
  `roll_number` varchar(20) UNIQUE,
  `status` enum('active','transferred','left') NOT NULL DEFAULT 'active',
  `bform_cnic` varchar(50) DEFAULT NULL,
  `parent_info` text,
  `admission_year` varchar(20) DEFAULT NULL,
  `batch_year` varchar(20) DEFAULT NULL,
  `enrollment_date` date NOT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `guardian_email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_email` (`email`),
  INDEX `idx_class_section` (`class`, `section`),
  INDEX `idx_roll_number` (`roll_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Instructors table
CREATE TABLE `instructors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) NOT NULL,
  `qualification` varchar(200) DEFAULT NULL,
  `specialization` varchar(200) DEFAULT NULL,
  `hire_date` date NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_email` (`email`),
  INDEX `idx_department` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parents table
CREATE TABLE `parents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `occupation` varchar(100) DEFAULT NULL,
  `relationship` enum('father','mother','guardian') NOT NULL DEFAULT 'guardian',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parent-Student relationship table
CREATE TABLE `parent_student_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `relationship` enum('father','mother','guardian') NOT NULL DEFAULT 'guardian',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`parent_id`) REFERENCES `parents`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_parent_student` (`parent_id`, `student_id`),
  INDEX `idx_student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Courses table
CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(20) NOT NULL UNIQUE,
  `course_name` varchar(200) NOT NULL,
  `department` varchar(100) NOT NULL,
  `description` text,
  `credits` int(11) NOT NULL DEFAULT 3,
  `fee` decimal(10,2) NOT NULL DEFAULT 15000.00,
  `instructor_id` int(11) DEFAULT NULL,
  `credit_hours` int(11) NOT NULL DEFAULT 3,
  `semester` varchar(20) DEFAULT NULL, -- e.g., 'Fall 2024', 'Spring 2024'
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_course_code` (`course_code`),
  FOREIGN KEY (`instructor_id`) REFERENCES `instructors`(`id`) ON DELETE SET NULL,
  INDEX `idx_department` (`department`),
  INDEX `idx_semester` (`semester`),
  INDEX `idx_instructor_id` (`instructor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Classes table (sections of courses)
CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `section` varchar(10) NOT NULL, -- e.g., 'A', 'B', 'C'
  `room_number` varchar(20) DEFAULT NULL,
  `room` varchar(20) DEFAULT NULL,
  `schedule` varchar(200) DEFAULT NULL, -- e.g., 'Mon/Wed 10:00-11:30'
  `capacity` int(11) NOT NULL DEFAULT 30,
  `enrolled_count` int(11) NOT NULL DEFAULT 0,
  `academic_year` varchar(20) DEFAULT NULL,
  `semester` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`instructor_id`) REFERENCES `instructors`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_course_section_semester` (`course_id`, `section`, `semester`),
  INDEX `idx_instructor_id` (`instructor_id`),
  INDEX `idx_semester` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student-Class Enrollment table
CREATE TABLE `student_class_enrollment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `status` enum('enrolled','dropped','completed') NOT NULL DEFAULT 'enrolled',
  `grade` varchar(5) DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_student_class` (`student_id`, `class_id`),
  INDEX `idx_student_id` (`student_id`),
  INDEX `idx_class_id` (`class_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exams table
CREATE TABLE `exams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `exam_name` varchar(200) NOT NULL,
  `exam_type` enum('quiz','midterm','final','assignment','project') NOT NULL DEFAULT 'quiz',
  `exam_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `total_marks` decimal(5,2) NOT NULL DEFAULT 100.00,
  `passing_marks` decimal(5,2) DEFAULT NULL,
  `room_number` varchar(20) DEFAULT NULL,
  `status` enum('scheduled','ongoing','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  INDEX `idx_class_id` (`class_id`),
  INDEX `idx_exam_date` (`exam_date`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exam Results table
CREATE TABLE `exam_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `marks_obtained` decimal(5,2) DEFAULT NULL,
  `percentage` decimal(5,2) DEFAULT NULL,
  `grade` varchar(5) DEFAULT NULL,
  `remarks` text,
  `entered_by` int(11) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`exam_id`) REFERENCES `exams`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`entered_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_exam_student` (`exam_id`, `student_id`),
  INDEX `idx_student_id` (`student_id`),
  INDEX `idx_marks_obtained` (`marks_obtained`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Assignments table
CREATE TABLE `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  INDEX `idx_class_id` (`class_id`),
  INDEX `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments table
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fee_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`fee_id`) REFERENCES `fees`(`id`) ON DELETE CASCADE,
  INDEX `idx_fee_id` (`fee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance table
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL DEFAULT 'present',
  `marked_by` int(11) DEFAULT NULL, -- instructor_id who marked attendance
  `remarks` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`marked_by`) REFERENCES `instructors`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_student_class_date` (`student_id`, `class_id`, `attendance_date`),
  INDEX `idx_class_id` (`class_id`),
  INDEX `idx_attendance_date` (`attendance_date`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fees table
CREATE TABLE `fees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `fee_type` enum('tuition','exam','library','lab','transport','other') NOT NULL DEFAULT 'tuition',
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','paid','overdue','waived') NOT NULL DEFAULT 'pending',
  `semester` varchar(20) DEFAULT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  INDEX `idx_student_id` (`student_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_due_date` (`due_date`),
  INDEX `idx_semester` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','success','error') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_is_read` (`is_read`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data

-- Users
INSERT INTO `users` (`username`, `password_hash`, `role`, `email`) VALUES
('admin', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'admin', 'admin@fast.edu.pk'),
('instructor1', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'instructor', 'ali.khan@fast.edu.pk'),
('student1', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'ahmed.ali@fast.edu.pk'),
('parent1', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'parent', 'parent.ali@gmail.com');

-- Students
INSERT INTO `students` (`user_id`, `first_name`, `last_name`, `email`, `phone`, `date_of_birth`, `gender`, `class`, `section`, `roll_number`, `enrollment_date`, `guardian_name`, `guardian_phone`) VALUES
(3, 'Ahmed', 'Ali', 'ahmed.ali@fast.edu.pk', '+92-300-1234567', '2000-05-15', 'male', 'BSCS-4', 'A', 'BSCS-4A-001', '2020-09-01', 'Muhammad Ali', '+92-300-7654321'),
(NULL, 'Fatima', 'Khan', 'fatima.khan@fast.edu.pk', '+92-301-1234567', '2001-03-20', 'female', 'BSCS-4', 'A', 'BSCS-4A-002', '2020-09-01', 'Khan Sahib', '+92-301-7654321'),
(NULL, 'Bilal', 'Ahmed', 'bilal.ahmed@fast.edu.pk', '+92-302-1234567', '2000-08-10', 'male', 'BSCS-4', 'B', 'BSCS-4B-001', '2020-09-01', 'Ahmed Khan', '+92-302-7654321'),
(NULL, 'Ayesha', 'Tariq', 'ayesha.tariq@fast.edu.pk', '+92-303-1234567', '2001-01-25', 'female', 'BSCS-3', 'A', 'BSCS-3A-001', '2019-09-01', 'Tariq Mahmood', '+92-303-7654321'),
(NULL, 'Usman', 'Sheikh', 'usman.sheikh@fast.edu.pk', '+92-304-1234567', '2000-11-30', 'male', 'BSCS-3', 'B', 'BSCS-3B-001', '2019-09-01', 'Sheikh Ahmed', '+92-304-7654321');

-- Instructors
INSERT INTO `instructors` (`user_id`, `first_name`, `last_name`, `email`, `phone`, `department`, `qualification`, `hire_date`, `salary`) VALUES
(2, 'Ali', 'Khan', 'ali.khan@fast.edu.pk', '+92-321-1234567', 'Computer Science', 'PhD Computer Science', '2018-01-15', 150000.00),
(NULL, 'Ayesha', 'Tariq', 'ayesha.tariq@fast.edu.pk', '+92-322-1234567', 'Software Engineering', 'MS Software Engineering', '2019-02-01', 120000.00),
(NULL, 'Bilal', 'Ahmed', 'bilal.ahmed@fast.edu.pk', '+92-323-1234567', 'Artificial Intelligence', 'PhD AI', '2017-08-15', 160000.00);

-- Parents
INSERT INTO `parents` (`user_id`, `first_name`, `last_name`, `email`, `phone`, `relationship`) VALUES
(4, 'Muhammad', 'Ali', 'parent.ali@gmail.com', '+92-300-7654321', 'father');

-- Parent-Student Links
INSERT INTO `parent_student_link` (`parent_id`, `student_id`, `relationship`, `is_primary`) VALUES
(1, 1, 'father', 1);

-- Courses
INSERT INTO `courses` (`course_code`, `course_name`, `department`, `description`, `credits`, `fee`, `instructor_id`, `credit_hours`, `semester`) VALUES
('CS101', 'Introduction to Programming', 'Computer Science', 'Basic programming concepts using C++', 3, 20000.00, 1, 3, 'Fall 2024'),
('CS201', 'Data Structures', 'Computer Science', 'Advanced data structures and algorithms', 4, 22000.00, 2, 4, 'Fall 2024'),
('CS301', 'Database Systems', 'Computer Science', 'Relational databases and SQL', 3, 18000.00, 3, 3, 'Fall 2024'),
('SE201', 'Software Engineering', 'Software Engineering', 'Software development lifecycle', 3, 16000.00, 2, 3, 'Fall 2024');

-- Classes
INSERT INTO `classes` (`course_id`, `instructor_id`, `section`, `room_number`, `room`, `schedule`, `capacity`, `academic_year`, `semester`) VALUES
(1, 1, 'A', 'CS-101', 'CR-101', 'Mon/Wed 09:00-10:30', 30, '2024-2025', 'Fall 2024'),
(1, 1, 'B', 'CS-102', 'CR-102', 'Tue/Thu 09:00-10:30', 30, '2024-2025', 'Fall 2024'),
(2, 2, 'A', 'CS-201', 'CR-201', 'Mon/Wed 11:00-12:30', 25, '2024-2025', 'Fall 2024'),
(3, 3, 'A', 'CS-301', 'CR-301', 'Tue/Thu 11:00-12:30', 30, '2024-2025', 'Fall 2024');

-- Student-Class Enrollments
INSERT INTO `student_class_enrollment` (`student_id`, `class_id`, `enrollment_date`) VALUES
(1, 1, '2024-09-01'),
(1, 3, '2024-09-01'),
(1, 4, '2024-09-01'),
(2, 1, '2024-09-01'),
(2, 3, '2024-09-01'),
(3, 2, '2024-09-01'),
(3, 4, '2024-09-01'),
(4, 3, '2024-09-01'),
(4, 4, '2024-09-01'),
(5, 3, '2024-09-01'),
(5, 4, '2024-09-01');

-- Exams
INSERT INTO `exams` (`class_id`, `exam_name`, `exam_type`, `exam_date`, `start_time`, `end_time`, `total_marks`, `passing_marks`, `room_number`) VALUES
(1, 'Midterm Exam - CS101', 'midterm', '2024-10-15', '10:00:00', '12:00:00', 50.00, 25.00, 'CS-101'),
(3, 'Quiz 1 - Data Structures', 'quiz', '2024-09-25', '11:00:00', '12:00:00', 20.00, 10.00, 'CS-201'),
(4, 'Assignment 1 - Database Systems', 'assignment', '2024-10-01', NULL, NULL, 100.00, 50.00, NULL);

-- Exam Results
INSERT INTO `exam_results` (`exam_id`, `student_id`, `marks_obtained`, `percentage`, `grade`) VALUES
(1, 1, 42.00, 84.00, 'A'),
(1, 2, 38.00, 76.00, 'B+'),
(2, 1, 18.00, 90.00, 'A'),
(2, 2, 16.00, 80.00, 'A-'),
(3, 1, 85.00, 85.00, 'A'),
(3, 4, 78.00, 78.00, 'B+');

-- Attendance Records
INSERT INTO `attendance` (`student_id`, `class_id`, `attendance_date`, `status`, `marked_by`) VALUES
(1, 1, '2024-09-02', 'present', 1),
(1, 1, '2024-09-04', 'present', 1),
(1, 1, '2024-09-09', 'late', 1),
(2, 1, '2024-09-02', 'present', 1),
(2, 1, '2024-09-04', 'absent', 1);

-- Fees
INSERT INTO `fees` (`student_id`, `fee_type`, `amount`, `due_date`, `status`, `semester`, `description`) VALUES
(1, 'tuition', 50000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(1, 'exam', 2000.00, '2024-10-01', 'pending', 'Fall 2024', 'Midterm Exam Fee'),
(2, 'tuition', 50000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(3, 'tuition', 50000.00, '2024-09-01', 'overdue', 'Fall 2024', 'Semester Tuition Fee');

-- Notifications
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`) VALUES
(3, 'Welcome to FAST University', 'Welcome! Your account has been created successfully.', 'success'),
(3, 'Exam Reminder', 'Your CS101 midterm exam is scheduled for October 15, 2024.', 'info'),
(4, 'Fee Payment Due', 'Tuition fee payment is due for your child Ahmed Ali.', 'warning');

-- Update enrolled counts
UPDATE classes SET enrolled_count = (
    SELECT COUNT(*) FROM student_class_enrollment WHERE class_id = classes.id AND status = 'enrolled'
);