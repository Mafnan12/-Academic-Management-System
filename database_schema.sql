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
('instructor2', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'instructor', 'ayesha.tariq@fast.edu.pk'),
('instructor3', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'instructor', 'bilal.ahmed@fast.edu.pk'),
('instructor4', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'instructor', 'sara.kamal@fast.edu.pk'),
('instructor5', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'instructor', 'hassan.raza@fast.edu.pk'),
('student1', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'ahmed.ali@fast.edu.pk'),
('student2', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'fatima.khan@fast.edu.pk'),
('student3', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'bilal.ahmed@fast.edu.pk'),
('student4', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'ayesha.tariq@fast.edu.pk'),
('student5', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'usman.sheikh@fast.edu.pk'),
('student6', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'zara.imran@fast.edu.pk'),
('student7', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'hamza.khan@fast.edu.pk'),
('student8', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'sana.javed@fast.edu.pk'),
('student9', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'ali.raza@fast.edu.pk'),
('student10', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'maria.butt@fast.edu.pk'),
('student11', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'saad.ahmed@fast.edu.pk'),
('student12', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'hira.khan@fast.edu.pk'),
('student13', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'farhan.ali@fast.edu.pk'),
('student14', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'nida.shah@fast.edu.pk'),
('student15', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'student', 'umer.khan@fast.edu.pk'),
('parent1', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'parent', 'parent.ali@gmail.com'),
('parent2', '$2y$10$qvHJAohA3Q8oXf.0XxmThO/9kFGsd0TU.xKQ86KnI5mySJWP6Lo3a', 'parent', 'parent.khan@gmail.com');

-- Students
INSERT INTO `students` (`user_id`, `first_name`, `last_name`, `email`, `phone`, `date_of_birth`, `gender`, `class`, `section`, `roll_number`, `enrollment_date`, `guardian_name`, `guardian_phone`) VALUES
(7, 'Ahmed', 'Ali', 'ahmed.ali@fast.edu.pk', '+92-300-1234567', '2000-05-15', 'male', 'BSCS-4', 'A', 'BSCS-4A-001', '2020-09-01', 'Muhammad Ali', '+92-300-7654321'),
(8, 'Fatima', 'Khan', 'fatima.khan@fast.edu.pk', '+92-301-1234567', '2001-03-20', 'female', 'BSCS-4', 'A', 'BSCS-4A-002', '2020-09-01', 'Khan Sahib', '+92-301-7654321'),
(9, 'Bilal', 'Ahmed', 'bilal.ahmed@fast.edu.pk', '+92-302-1234567', '2000-08-10', 'male', 'BSCS-4', 'B', 'BSCS-4B-001', '2020-09-01', 'Ahmed Khan', '+92-302-7654321'),
(10, 'Ayesha', 'Tariq', 'ayesha.tariq@fast.edu.pk', '+92-303-1234567', '2001-01-25', 'female', 'BSCS-3', 'A', 'BSCS-3A-001', '2019-09-01', 'Tariq Mahmood', '+92-303-7654321'),
(11, 'Usman', 'Sheikh', 'usman.sheikh@fast.edu.pk', '+92-304-1234567', '2000-11-30', 'male', 'BSCS-3', 'B', 'BSCS-3B-001', '2019-09-01', 'Sheikh Ahmed', '+92-304-7654321'),
(NULL, 'Zara', 'Imran', 'zara.imran@fast.edu.pk', '+92-305-1234567', '2001-07-12', 'female', 'BSCS-4', 'A', 'BSCS-4A-003', '2020-09-01', 'Imran Khan', '+92-305-7654321'),
(NULL, 'Hamza', 'Khan', 'hamza.khan@fast.edu.pk', '+92-306-1234567', '2000-09-18', 'male', 'BSCS-4', 'B', 'BSCS-4B-002', '2020-09-01', 'Khan Ahmed', '+92-306-7654321'),
(NULL, 'Sana', 'Javed', 'sana.javed@fast.edu.pk', '+92-307-1234567', '2001-04-05', 'female', 'BSCS-3', 'A', 'BSCS-3A-002', '2019-09-01', 'Javed Iqbal', '+92-307-7654321'),
(NULL, 'Ali', 'Raza', 'ali.raza@fast.edu.pk', '+92-308-1234567', '2000-12-22', 'male', 'BSCS-3', 'B', 'BSCS-3B-002', '2019-09-01', 'Raza Ahmed', '+92-308-7654321'),
(NULL, 'Maria', 'Butt', 'maria.butt@fast.edu.pk', '+92-309-1234567', '2001-06-30', 'female', 'BSCS-2', 'A', 'BSCS-2A-001', '2018-09-01', 'Butt Sahib', '+92-309-7654321'),
(NULL, 'Saad', 'Ahmed', 'saad.ahmed@fast.edu.pk', '+92-310-1234567', '2000-02-14', 'male', 'BSCS-2', 'B', 'BSCS-2B-001', '2018-09-01', 'Ahmed Raza', '+92-310-7654321'),
(NULL, 'Hira', 'Khan', 'hira.khan@fast.edu.pk', '+92-311-1234567', '2001-08-25', 'female', 'BSCS-2', 'A', 'BSCS-2A-002', '2018-09-01', 'Khan Ali', '+92-311-7654321'),
(NULL, 'Farhan', 'Ali', 'farhan.ali@fast.edu.pk', '+92-312-1234567', '2000-10-08', 'male', 'BSCS-2', 'B', 'BSCS-2B-002', '2018-09-01', 'Ali Khan', '+92-312-7654321'),
(NULL, 'Nida', 'Shah', 'nida.shah@fast.edu.pk', '+92-313-1234567', '2001-11-15', 'female', 'BSCS-1', 'A', 'BSCS-1A-001', '2017-09-01', 'Shah Javed', '+92-313-7654321'),
(NULL, 'Umer', 'Khan', 'umer.khan@fast.edu.pk', '+92-314-1234567', '2000-01-03', 'male', 'BSCS-1', 'B', 'BSCS-1B-001', '2017-09-01', 'Khan Butt', '+92-314-7654321');

-- Instructors
INSERT INTO `instructors` (`user_id`, `first_name`, `last_name`, `email`, `phone`, `department`, `qualification`, `hire_date`, `salary`) VALUES
(2, 'Ali', 'Khan', 'ali.khan@fast.edu.pk', '+92-321-1234567', 'Computer Science', 'PhD Computer Science', '2018-01-15', 150000.00),
(3, 'Ayesha', 'Tariq', 'ayesha.tariq@fast.edu.pk', '+92-322-1234567', 'Software Engineering', 'MS Software Engineering', '2019-02-01', 120000.00),
(4, 'Bilal', 'Ahmed', 'bilal.ahmed@fast.edu.pk', '+92-323-1234567', 'Artificial Intelligence', 'PhD AI', '2017-08-15', 160000.00),
(NULL, 'Sara', 'Kamal', 'sara.kamal@fast.edu.pk', '+92-324-1234567', 'Data Science', 'PhD Data Science', '2020-03-10', 140000.00),
(NULL, 'Hassan', 'Raza', 'hassan.raza@fast.edu.pk', '+92-325-1234567', 'Cyber Security', 'MS Cyber Security', '2019-08-20', 130000.00),
(NULL, 'Dr. Fatima', 'Bukhari', 'fatima.bukhari@fast.edu.pk', '+92-326-1234567', 'Mathematics', 'PhD Mathematics', '2016-09-01', 145000.00);

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
('CS401', 'Artificial Intelligence', 'Computer Science', 'Machine learning and AI fundamentals', 4, 25000.00, 3, 4, 'Fall 2024'),
('SE201', 'Software Engineering', 'Software Engineering', 'Software development lifecycle', 3, 16000.00, 2, 3, 'Fall 2024'),
('SE301', 'Web Development', 'Software Engineering', 'Full-stack web development', 3, 19000.00, 4, 3, 'Fall 2024'),
('DS201', 'Data Science Fundamentals', 'Data Science', 'Statistics and data analysis', 3, 21000.00, 5, 3, 'Fall 2024'),
('CSY301', 'Network Security', 'Cyber Security', 'Cyber security principles', 3, 20000.00, 6, 3, 'Fall 2024'),
('MATH101', 'Discrete Mathematics', 'Mathematics', 'Mathematical foundations for CS', 3, 15000.00, 7, 3, 'Fall 2024');

-- Classes
INSERT INTO `classes` (`course_id`, `instructor_id`, `section`, `room_number`, `room`, `schedule`, `capacity`, `academic_year`, `semester`) VALUES
(1, 1, 'A', 'CS-101', 'CR-101', 'Mon/Wed 09:00-10:30', 30, '2024-2025', 'Fall 2024'),
(1, 1, 'B', 'CS-102', 'CR-102', 'Tue/Thu 09:00-10:30', 30, '2024-2025', 'Fall 2024'),
(2, 2, 'A', 'CS-201', 'CR-201', 'Mon/Wed 11:00-12:30', 25, '2024-2025', 'Fall 2024'),
(3, 3, 'A', 'CS-301', 'CR-301', 'Tue/Thu 11:00-12:30', 30, '2024-2025', 'Fall 2024'),
(4, 3, 'A', 'CS-401', 'CR-401', 'Mon/Wed 14:00-15:30', 25, '2024-2025', 'Fall 2024'),
(5, 2, 'A', 'SE-201', 'CR-501', 'Tue/Thu 14:00-15:30', 30, '2024-2025', 'Fall 2024'),
(6, 4, 'A', 'SE-301', 'CR-601', 'Wed/Fri 09:00-10:30', 28, '2024-2025', 'Fall 2024'),
(7, 5, 'A', 'DS-201', 'CR-701', 'Mon/Wed 16:00-17:30', 25, '2024-2025', 'Fall 2024'),
(8, 6, 'A', 'CSY-301', 'CR-801', 'Tue/Thu 16:00-17:30', 30, '2024-2025', 'Fall 2024'),
(9, 7, 'A', 'MATH-101', 'CR-901', 'Wed/Fri 11:00-12:30', 35, '2024-2025', 'Fall 2024');

-- Student-Class Enrollments
INSERT INTO `student_class_enrollment` (`student_id`, `class_id`, `enrollment_date`) VALUES
(1, 1, '2024-09-01'), (1, 3, '2024-09-01'), (1, 4, '2024-09-01'), (1, 5, '2024-09-01'),
(2, 1, '2024-09-01'), (2, 3, '2024-09-01'), (2, 6, '2024-09-01'), (2, 7, '2024-09-01'),
(3, 2, '2024-09-01'), (3, 4, '2024-09-01'), (3, 8, '2024-09-01'), (3, 9, '2024-09-01'),
(4, 3, '2024-09-01'), (4, 4, '2024-09-01'), (4, 5, '2024-09-01'), (4, 6, '2024-09-01'),
(5, 3, '2024-09-01'), (5, 4, '2024-09-01'), (5, 7, '2024-09-01'), (5, 8, '2024-09-01'),
(6, 1, '2024-09-01'), (6, 2, '2024-09-01'), (6, 5, '2024-09-01'), (6, 9, '2024-09-01'),
(7, 2, '2024-09-01'), (7, 3, '2024-09-01'), (7, 6, '2024-09-01'), (7, 7, '2024-09-01'),
(8, 4, '2024-09-01'), (8, 5, '2024-09-01'), (8, 8, '2024-09-01'), (8, 9, '2024-09-01'),
(9, 1, '2024-09-01'), (9, 6, '2024-09-01'), (9, 7, '2024-09-01'), (9, 8, '2024-09-01'),
(10, 2, '2024-09-01'), (10, 3, '2024-09-01'), (10, 4, '2024-09-01'), (10, 5, '2024-09-01'),
(11, 6, '2024-09-01'), (11, 7, '2024-09-01'), (11, 8, '2024-09-01'), (11, 9, '2024-09-01'),
(12, 1, '2024-09-01'), (12, 2, '2024-09-01'), (12, 3, '2024-09-01'), (12, 4, '2024-09-01'),
(13, 5, '2024-09-01'), (13, 6, '2024-09-01'), (13, 7, '2024-09-01'), (13, 8, '2024-09-01'),
(14, 1, '2024-09-01'), (14, 2, '2024-09-01'), (14, 9, '2024-09-01'), (14, 10, '2024-09-01'),
(15, 3, '2024-09-01'), (15, 4, '2024-09-01'), (15, 5, '2024-09-01'), (15, 6, '2024-09-01');

-- Exams
INSERT INTO `exams` (`class_id`, `exam_name`, `exam_type`, `exam_date`, `start_time`, `end_time`, `total_marks`, `passing_marks`, `room_number`) VALUES
(1, 'Midterm Exam - CS101', 'midterm', '2024-10-15', '10:00:00', '12:00:00', 50.00, 25.00, 'CS-101'),
(3, 'Quiz 1 - Data Structures', 'quiz', '2024-09-25', '11:00:00', '12:00:00', 20.00, 10.00, 'CS-201'),
(4, 'Assignment 1 - Database Systems', 'assignment', '2024-10-01', NULL, NULL, 100.00, 50.00, NULL),
(5, 'AI Midterm Exam', 'midterm', '2024-10-20', '14:00:00', '16:00:00', 50.00, 25.00, 'CS-401'),
(6, 'Software Engineering Quiz', 'quiz', '2024-09-30', '15:00:00', '16:00:00', 25.00, 12.50, 'SE-201'),
(7, 'Web Dev Assignment', 'assignment', '2024-10-05', NULL, NULL, 100.00, 50.00, NULL),
(8, 'Data Science Midterm', 'midterm', '2024-10-25', '16:00:00', '18:00:00', 50.00, 25.00, 'DS-201'),
(9, 'Network Security Quiz', 'quiz', '2024-10-02', '17:00:00', '18:00:00', 20.00, 10.00, 'CSY-301'),
(10, 'Math Final Exam', 'final', '2024-12-15', '09:00:00', '12:00:00', 100.00, 40.00, 'MATH-101');

-- Exam Results
INSERT INTO `exam_results` (`exam_id`, `student_id`, `marks_obtained`, `percentage`, `grade`) VALUES
(1, 1, 42.00, 84.00, 'A'), (1, 2, 38.00, 76.00, 'B+'), (1, 6, 45.00, 90.00, 'A'), (1, 9, 35.00, 70.00, 'B'), (1, 14, 40.00, 80.00, 'A-'),
(2, 1, 18.00, 90.00, 'A'), (2, 2, 16.00, 80.00, 'A-'), (2, 4, 15.00, 75.00, 'B+'), (2, 5, 17.00, 85.00, 'A'), (2, 7, 14.00, 70.00, 'B'),
(3, 1, 85.00, 85.00, 'A'), (3, 4, 78.00, 78.00, 'B+'), (3, 5, 92.00, 92.00, 'A'), (3, 8, 75.00, 75.00, 'B+'), (3, 10, 88.00, 88.00, 'A'),
(4, 1, 45.00, 90.00, 'A'), (4, 4, 42.00, 84.00, 'A'), (4, 5, 38.00, 76.00, 'B+'), (4, 8, 40.00, 80.00, 'A-'), (4, 12, 35.00, 70.00, 'B'),
(5, 2, 22.00, 88.00, 'A'), (5, 4, 20.00, 80.00, 'A-'), (5, 5, 18.00, 72.00, 'B+'), (5, 7, 23.00, 92.00, 'A'), (5, 9, 19.00, 76.00, 'B+'),
(6, 2, 90.00, 90.00, 'A'), (6, 4, 85.00, 85.00, 'A'), (6, 5, 78.00, 78.00, 'B+'), (6, 7, 92.00, 92.00, 'A'), (6, 9, 80.00, 80.00, 'A-'),
(7, 2, 45.00, 90.00, 'A'), (7, 5, 42.00, 84.00, 'A'), (7, 7, 38.00, 76.00, 'B+'), (7, 9, 40.00, 80.00, 'A-'), (7, 11, 35.00, 70.00, 'B'),
(8, 3, 22.00, 88.00, 'A'), (8, 5, 20.00, 80.00, 'A-'), (8, 8, 18.00, 72.00, 'B+'), (8, 11, 23.00, 92.00, 'A'), (8, 13, 19.00, 76.00, 'B+'),
(9, 3, 85.00, 85.00, 'A'), (9, 5, 78.00, 78.00, 'B+'), (9, 8, 92.00, 92.00, 'A'), (9, 11, 75.00, 75.00, 'B+'), (9, 13, 88.00, 88.00, 'A'),
(10, 14, 85.00, 85.00, 'A'), (10, 15, 78.00, 78.00, 'B+'), (10, 1, 92.00, 92.00, 'A'), (10, 2, 75.00, 75.00, 'B+'), (10, 3, 88.00, 88.00, 'A');

-- Attendance Records
INSERT INTO `attendance` (`student_id`, `class_id`, `attendance_date`, `status`, `marked_by`) VALUES
(1, 1, '2024-09-02', 'present', 1), (1, 1, '2024-09-04', 'present', 1), (1, 1, '2024-09-09', 'late', 1), (1, 1, '2024-09-11', 'present', 1),
(1, 3, '2024-09-03', 'present', 2), (1, 3, '2024-09-05', 'present', 2), (1, 3, '2024-09-10', 'absent', 2),
(2, 1, '2024-09-02', 'present', 1), (2, 1, '2024-09-04', 'absent', 1), (2, 1, '2024-09-09', 'present', 1),
(2, 3, '2024-09-03', 'present', 2), (2, 3, '2024-09-05', 'late', 2), (2, 3, '2024-09-10', 'present', 2),
(3, 2, '2024-09-02', 'present', 1), (3, 2, '2024-09-04', 'present', 1), (3, 4, '2024-09-03', 'absent', 3),
(4, 3, '2024-09-03', 'present', 2), (4, 4, '2024-09-03', 'late', 3), (4, 5, '2024-09-04', 'present', 3),
(5, 3, '2024-09-03', 'present', 2), (5, 4, '2024-09-03', 'present', 3), (5, 6, '2024-09-04', 'absent', 2);

-- Fees
INSERT INTO `fees` (`student_id`, `fee_type`, `amount`, `due_date`, `status`, `semester`, `description`) VALUES
(1, 'tuition', 50000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(1, 'exam', 2000.00, '2024-10-01', 'pending', 'Fall 2024', 'Midterm Exam Fee'),
(2, 'tuition', 50000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(3, 'tuition', 50000.00, '2024-09-01', 'overdue', 'Fall 2024', 'Semester Tuition Fee'),
(4, 'tuition', 48000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(5, 'tuition', 48000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(6, 'tuition', 50000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(7, 'tuition', 50000.00, '2024-09-01', 'pending', 'Fall 2024', 'Semester Tuition Fee'),
(8, 'tuition', 48000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(9, 'tuition', 48000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(10, 'tuition', 45000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(11, 'tuition', 45000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(12, 'tuition', 45000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(13, 'tuition', 45000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(14, 'tuition', 40000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee'),
(15, 'tuition', 40000.00, '2024-09-01', 'paid', 'Fall 2024', 'Semester Tuition Fee');

-- Notifications
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`) VALUES
(3, 'Welcome to FAST University', 'Welcome! Your account has been created successfully.', 'success'),
(3, 'Exam Reminder', 'Your CS101 midterm exam is scheduled for October 15, 2024.', 'info'),
(4, 'Fee Payment Due', 'Tuition fee payment is due for your child Ahmed Ali.', 'warning');

-- Update enrolled counts
UPDATE classes SET enrolled_count = (
    SELECT COUNT(*) FROM student_class_enrollment WHERE class_id = classes.id AND status = 'enrolled'
);