# 🎓 FAST University — Academic Management System

A comprehensive, full-stack university management system built with **PHP 8+**, **MySQL**, and **Tailwind CSS**. Designed with role-based access control for administrators, instructors, students, and parents to manage academic activities end-to-end.

> **Course Project** — Database Systems (FAST NUCES)

---

## 📑 Table of Contents

- [Features](#-features)
- [User Roles & Permissions](#-user-roles--permissions)
- [Tech Stack](#-tech-stack)
- [Database Schema](#-database-schema)
- [Project Structure](#-project-structure)
- [Installation](#-installation)
- [Default Credentials](#-default-credentials)
- [Screenshots](#-screenshots)
- [ERD](#-erd)
- [License](#-license)

---

## ✨ Features

### 1. Student Management
- Add / edit / delete student profiles with full personal details
- Store name, DOB, B-Form/CNIC, parent info, contact, address
- Admission year, class, section assignment
- Student status tracking (active, transferred, left)
- Unique roll number generation per batch
- Batch/year tagging for cohort management

### 2. Teacher (Instructor) Management
- Add / edit / delete instructor profiles
- Assign subjects, departments, and classes
- Contact and employment information (qualification, specialization, hire date)
- Designation tracking (Subject Teacher, Class Incharge, etc.)
- Instructor login for result entry and class management

### 3. Parents Portal
- Secure parent login with child-specific access
- View child's profile, class, section, and assigned subjects
- Access term-wise results with subject-wise marks, grades, and remarks
- Attendance summary and exam schedules
- Fee challan generation and payment status tracking
- Multiple children under one parent account via `parent_student_link`

### 4. Class & Section Management
- Create and manage classes (e.g., BSCS-1, BSCS-2, etc.)
- Assign sections (A, B, C) with capacity management
- Assign instructors to class sections
- Room allocation and scheduling

### 5. Course/Subject Management
- Add and manage courses with department assignment
- Credit hours and fee configuration per course
- Mark as compulsory or elective
- Assign instructors to courses and classes
- Prerequisite tracking

### 6. Term & Exam Management
- Define exam types: quiz, midterm, final, assignment, project
- Set total marks and passing marks per exam
- Schedule exam dates with time slots and room assignment
- Exam status tracking: scheduled → ongoing → completed → cancelled
- Lock/unlock exams for result entry

### 7. Student-Course Mapping (Enrollment)
- Enroll students in class sections
- Track enrollment status: enrolled, dropped, completed
- Auto-link students to courses based on class enrollment
- Teachers see only their assigned subjects/students

### 8. Marks & Result Management
- Teachers enter marks per subject per student
- Auto-calculate percentage and grade
- Generate results per student, class, or subject
- Store historical results term-wise and year-wise
- Admin moderation and entered-by tracking

### 9. Grading & Evaluation Settings
- Configurable grading criteria (A+ = 95%+, A = 90%+, etc.)
- Pass/fail thresholds per subject
- Remarks section per subject or overall
- Pre-seeded grading scale in database

### 10. Reporting & Analytics
- Admin dashboard with system-wide KPIs
- Class-wise and subject-wise result summaries
- Student performance trends across terms
- Attendance analytics
- Fee collection and overdue reports

### 11. Attendance Module
- Daily attendance tracking per class section
- Status options: present, absent, late, excused
- Instructor-marked with audit trail (`marked_by`)
- Unique constraint per student/class/date

### 12. Fee & Payment Management
- Fee types: tuition, exam, library, lab, transport, other
- Payment tracking with due date management
- Fee status: pending, paid, overdue, waived
- Semester-wise fee assignment

### 13. Notifications
- System notifications for all user roles
- Types: info, warning, success, error
- Read/unread status tracking

### 14. Audit & Security
- Session-based authentication with role validation
- Password hashing with bcrypt
- Role-based access control on every page
- Database-level referential integrity with foreign keys
- Indexed tables for query performance

---

## 🔐 User Roles & Permissions

| Role | Access Level |
|---|---|
| **Admin** | Full system access — manage users, courses, classes, enrollments, exams, results, fees, reports |
| **Instructor** | View assigned classes, enter results, manage assignments, mark attendance, instructor dashboard |
| **Student** | View own profile, enrolled courses, results, attendance, fee status, student dashboard |
| **Parent** | Monitor child's academic progress, attendance, fees, notifications via parent dashboard |

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8.0+ |
| **Database** | MySQL 5.7+ / 8.0+ |
| **Frontend** | HTML5, Tailwind CSS (CDN), JavaScript |
| **Icons** | Google Material Symbols |
| **Server** | Apache (XAMPP / WAMP / LAMP) |
| **Architecture** | MVC-inspired with role-based access control |

---

## 🗄 Database Schema

### Core Tables (16 tables)

| Table | Purpose |
|---|---|
| `users` | Authentication and role management |
| `students` | Student profiles with academic details |
| `instructors` | Faculty profiles with qualifications |
| `parents` | Parent/guardian profiles |
| `parent_student_link` | M:N relationship between parents and students |
| `courses` | Course catalog with credits and fees |
| `classes` | Course sections with scheduling |
| `student_class_enrollment` | Student enrollment in class sections |
| `exams` | Exam scheduling and configuration |
| `exam_results` | Student marks and grades per exam |
| `assignments` | Class assignments with due dates |
| `attendance` | Daily attendance records |
| `fees` | Fee records per student per semester |
| `payments` | Payment transactions against fees |
| `notifications` | System messages for all users |

### Key Design Decisions
- **3NF Normalized** — No transitive dependencies
- **Referential Integrity** — CASCADE/SET NULL on all foreign keys
- **Performance Indexed** — Indexes on email, department, status, dates, and foreign keys
- **Audit Ready** — `entered_by` tracking on results, `marked_by` on attendance

> See [ERD.md](ERD.md) for the full Entity Relationship Diagram.

---

## 📁 Project Structure

```
├── .gitignore
├── README.md
├── ERD.md                          # Entity Relationship Diagram (Mermaid)
├── database_schema.sql             # Complete schema + sample data
├── setup.php                       # Browser-based DB setup utility
├── index.php                       # Admin dashboard (entry point)
├── assets/
│   └── images/
│       ├── logo.png                # University logo
│       ├── campus1.jpg             # Login page background
│       └── campus2.jpg             # Login page background
├── auth/
│   ├── login.php                   # Multi-role authentication
│   └── logout.php                  # Session cleanup
├── includes/
│   ├── config.php                  # Session helpers & role checks
│   ├── db.php                      # PDO database connection
│   ├── header.php                  # HTML head + top navigation
│   ├── sidebar.php                 # Role-aware sidebar navigation
│   └── footer.php                  # Page footer
└── pages/
    ├── students.php                # Student CRUD management
    ├── instructors.php             # Instructor CRUD management
    ├── courses.php                 # Course CRUD management
    ├── classes.php                 # Class/section management
    ├── enrollments.php             # Student enrollment management
    ├── exams.php                   # Exam scheduling & results
    ├── reports.php                 # Analytics & reporting
    ├── instructor_dashboard.php    # Instructor portal
    ├── student_dashboard.php       # Student portal
    └── parent_dashboard.php        # Parent portal
```

---

## 🚀 Installation

### Prerequisites
- PHP 8.0+
- MySQL 5.7+
- Apache web server (XAMPP recommended for local dev)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/Mafnan12/-Academic-Management-System.git
   cd -Academic-Management-System
   ```

2. **Deploy to web server**
   Copy the project to your Apache document root:
   ```bash
   # XAMPP (Windows)
   cp -r . C:/xampp/htdocs/fast_sms/

   # Or create a symlink
   ```

3. **Configure database connection**
   Create `includes/db.php` with your credentials:
   ```php
   <?php
   $host = 'localhost';
   $dbname = 'fast_university_management';
   $user = 'root';
   $pass = '';

   try {
       $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
       $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
   } catch (PDOException $e) {
       die("Connection failed: " . $e->getMessage());
   }
   ?>
   ```

4. **Initialize the database**

   **Option A** — Browser setup (recommended):
   ```
   http://localhost/fast_sms/setup.php
   ```

   **Option B** — Direct SQL import:
   ```bash
   mysql -u root -p < database_schema.sql
   ```

5. **Login**
   ```
   http://localhost/fast_sms/auth/login.php
   ```

---

## 🔑 Default Credentials

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `password123` |
| Instructor | `instructor1` | `password123` |
| Student | `student1` | `password123` |
| Parent | `parent1` | `password123` |

> ⚠️ Change default passwords immediately in a production environment.

---

## 📸 Screenshots

*Screenshots of the login page, admin dashboard, student management, and result entry are available in the application itself. Run locally to preview.*

---

## 📊 ERD

The full Entity Relationship Diagram is documented in [ERD.md](ERD.md) using Mermaid syntax, covering all 16 tables with their relationships, data types, and constraints.

---

## 👨‍💻 Author

**Muhammad Afnan** — FAST NUCES

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).
