# FAST University Academic Management System
## Database Systems - Project Report

**Submitted by:** Muhammad Afnan  
**Course:** Database Systems  
**University:** FAST NUCES  
**Semester:** Spring 2026  

---

## 1. Problem Description

Universities handle a lot of data every day — student records, course registrations, exam results, fee payments, attendance, and so on. Most of the time this data is managed using spreadsheets or manual registers which creates problems like duplicate entries, lost records, and no proper way to search or filter information.

The goal of this project is to build a web-based university management system for FAST University that handles all the core academic operations in one place. The system should allow different users (admin, instructors, students, and parents) to log in and access only the data relevant to them.

### Main Problems Addressed

- No centralized system for student records, courses, and results
- Manual attendance and result entry is error-prone
- Parents have no easy way to check their child's progress
- Fee tracking is done manually which leads to missed payments
- No proper search or filtering for student/course data
- No reports or analytics for admin to make decisions

### Target Users

1. **Admin** — manages the entire system (students, instructors, courses, fees, reports)
2. **Instructors** — view assigned classes, enter results, mark attendance
3. **Students** — check enrolled courses, results, attendance, fee status
4. **Parents** — monitor their child's academic progress and fee payments

---

## 2. Database Design

### 2.1 Entities and Attributes

The database consists of the following main entities:

| Entity | Key Attributes |
|--------|---------------|
| Users | id, username, password_hash, email, role, is_active |
| Students | id, user_id, first_name, last_name, email, phone, date_of_birth, class, section, roll_number, status |
| Instructors | id, user_id, first_name, last_name, email, department, qualification, hire_date |
| Parents | id, user_id, first_name, last_name, email, phone, relationship |
| Courses | id, course_code, course_name, department, credits, fee, instructor_id |
| Classes | id, course_id, instructor_id, section, room, schedule, capacity, semester |
| Student_Class_Enrollment | id, student_id, class_id, enrollment_date, status |
| Exams | id, class_id, exam_name, exam_type, exam_date, total_marks, status |
| Exam_Results | id, exam_id, student_id, marks_obtained, percentage, grade |
| Attendance | id, student_id, class_id, attendance_date, status, marked_by |
| Fees | id, student_id, fee_type, amount, due_date, status |
| Payments | id, fee_id, amount_paid, payment_date |
| Notifications | id, user_id, title, message, type, is_read |
| Parent_Student_Link | id, parent_id, student_id, relationship |
| Assignments | id, class_id, title, description, due_date |

### 2.2 Relationships

| Relationship | Type | Description |
|-------------|------|-------------|
| Users to Students | 1:1 | Each student has one user account |
| Users to Instructors | 1:1 | Each instructor has one user account |
| Users to Parents | 1:1 | Each parent has one user account |
| Parents to Students | M:N | Resolved via parent_student_link table (one parent can have multiple children, one student can have multiple guardians) |
| Courses to Classes | 1:M | One course can have multiple sections |
| Classes to Student_Class_Enrollment | 1:M | One class section has many enrolled students |
| Classes to Exams | 1:M | One class can have multiple exams |
| Exams to Exam_Results | 1:M | One exam produces results for many students |
| Students to Fees | 1:M | One student has many fee records |
| Fees to Payments | 1:M | One fee can have multiple payment installments |
| Instructors to Classes | 1:M | One instructor teaches multiple class sections |

### 2.3 Primary Keys

Every table uses an auto-increment `id` column as its primary key.

### 2.4 Foreign Keys

| Table | Foreign Key | References |
|-------|------------|------------|
| students | user_id | users(id) |
| instructors | user_id | users(id) |
| parents | user_id | users(id) |
| parent_student_link | parent_id | parents(id) |
| parent_student_link | student_id | students(id) |
| courses | instructor_id | instructors(id) |
| classes | course_id | courses(id) |
| classes | instructor_id | instructors(id) |
| student_class_enrollment | student_id | students(id) |
| student_class_enrollment | class_id | classes(id) |
| exams | class_id | classes(id) |
| exam_results | exam_id | exams(id) |
| exam_results | student_id | students(id) |
| exam_results | entered_by | users(id) |
| attendance | student_id | students(id) |
| attendance | class_id | classes(id) |
| attendance | marked_by | instructors(id) |
| fees | student_id | students(id) |
| payments | fee_id | fees(id) |
| notifications | user_id | users(id) |

### 2.5 Constraints

- **UNIQUE** constraints on: username, email (in users), course_code, roll_number, and composite keys like (student_id, class_id) in enrollment
- **NOT NULL** on required fields like names, email, dates
- **ENUM** types for role, status, exam_type, fee_type, gender, attendance status
- **DEFAULT** values for timestamps (CURRENT_TIMESTAMP), status fields (active, pending, etc.)
- **ON DELETE CASCADE** for child records (e.g., deleting a class removes its exams and enrollments)
- **ON DELETE SET NULL** for optional references (e.g., instructor_id in courses)

### 2.6 ERD

The full Entity Relationship Diagram is provided in `ERD.md` file using Mermaid syntax. It shows all 15 entities with their attributes, primary keys, foreign keys, and cardinality of each relationship.

---

## 3. Database Implementation

### 3.1 Tables Created

All tables are implemented in MySQL with proper data types, constraints, foreign keys, and indexes. The complete SQL script is in `database_schema.sql`.

### 3.2 Sample Data

The database comes pre-loaded with realistic sample data:
- 24 users (1 admin, 5 instructors, 15 students, 2 parents)
- 15 students with full profiles
- 6 instructors across different departments
- 9 courses (CS, SE, DS, Cybersecurity, Math)
- 10 class sections
- 60+ student-class enrollments
- 9 exams (quizzes, midterms, finals, assignments)
- 50+ exam results with grades
- 22 attendance records
- 15 fee records with payment statuses
- 3 notification entries

### 3.3 SQL Operations Used

#### CREATE (Table creation)
```sql
CREATE TABLE students (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) DEFAULT NULL,
    first_name varchar(50) NOT NULL,
    ...
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### INSERT (Adding data)
```sql
INSERT INTO students (user_id, first_name, last_name, email, phone, class, section, roll_number, enrollment_date)
VALUES (7, 'Ahmed', 'Ali', 'ahmed.ali@fast.edu.pk', '+92-300-1234567', 'BSCS-4', 'A', 'BSCS-4A-001', '2024-09-01');
```

#### UPDATE (Modifying data)
```sql
-- Update enrolled count in classes based on actual enrollments
UPDATE classes SET enrolled_count = (
    SELECT COUNT(*) FROM student_class_enrollment 
    WHERE class_id = classes.id AND status = 'enrolled'
);
```

#### DELETE (Removing data)
Handled through CASCADE constraints — when a parent record is deleted, all related child records are automatically removed. For example, deleting a class removes its exams, enrollments, and attendance records.

Also used in PHP backend:
```php
$stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
$stmt->execute([$id]);
```

### 3.4 Queries Used

#### Simple Queries
```sql
-- Count all students
SELECT COUNT(*) FROM students;

-- Get students in a specific class
SELECT * FROM students WHERE class = 'BSCS-4' AND section = 'A';

-- Search students by name
SELECT * FROM students WHERE first_name LIKE '%ahmed%' OR last_name LIKE '%ahmed%';
```

#### JOIN Queries
```sql
-- Get recent enrollments with student and course names (3-table JOIN)
SELECT sce.enrollment_date, s.first_name, s.last_name, c.course_name, cl.section
FROM student_class_enrollment sce
JOIN students s ON sce.student_id = s.id
JOIN classes cl ON sce.class_id = cl.id
JOIN courses c ON cl.course_id = c.id
ORDER BY sce.enrollment_date DESC;

-- Get exam results with course and student info (4-table JOIN)
SELECT co.course_code, co.course_name, e.exam_type, e.exam_name,
       COUNT(er.id) as results_entered,
       ROUND(AVG(er.marks_obtained), 1) as avg_marks,
       MAX(er.marks_obtained) as highest_marks,
       MIN(er.marks_obtained) as lowest_marks
FROM exams e
JOIN classes cl ON e.class_id = cl.id
JOIN courses co ON cl.course_id = co.id
LEFT JOIN exam_results er ON e.id = er.exam_id
GROUP BY e.id, co.course_code, co.course_name, e.exam_type, e.exam_name;
```

#### Subqueries
```sql
-- Students taking more credits than the average student
SELECT sub.student_name, sub.class, sub.total_credits
FROM (
    SELECT CONCAT(s.first_name, ' ', s.last_name) AS student_name,
           s.class,
           COALESCE(SUM(co.credits), 0) AS total_credits
    FROM students s
    LEFT JOIN student_class_enrollment sce ON s.id = sce.student_id AND sce.status = 'enrolled'
    LEFT JOIN classes cl ON sce.class_id = cl.id
    LEFT JOIN courses co ON cl.course_id = co.id
    GROUP BY s.id, s.first_name, s.last_name, s.class
) sub
WHERE sub.total_credits > (
    SELECT AVG(inner_sub.total_credits)
    FROM (
        SELECT COALESCE(SUM(co2.credits), 0) AS total_credits
        FROM students s2
        LEFT JOIN student_class_enrollment sce2 ON s2.id = sce2.student_id
        LEFT JOIN classes cl2 ON sce2.class_id = cl2.id
        LEFT JOIN courses co2 ON cl2.course_id = co2.id
        GROUP BY s2.id
    ) inner_sub
)
ORDER BY sub.total_credits DESC;
```

#### Aggregate Functions (Reports)
```sql
-- Students per class (GROUP BY + COUNT)
SELECT class, COUNT(*) as count FROM students
WHERE class IS NOT NULL
GROUP BY class ORDER BY count DESC;

-- Enrollments per course (GROUP BY + COUNT + JOIN)
SELECT co.course_code, co.course_name, COUNT(sce.student_id) as enrolled_count
FROM courses co
LEFT JOIN classes cl ON co.id = cl.course_id
LEFT JOIN student_class_enrollment sce ON cl.id = sce.class_id AND sce.status = 'enrolled'
GROUP BY co.id, co.course_code, co.course_name
ORDER BY enrolled_count DESC;

-- Fee collection summary
SELECT SUM(amount) as total_collected FROM fees WHERE status = 'paid';
SELECT SUM(amount) as total_pending FROM fees WHERE status = 'pending';
```

### 3.5 Views

```sql
-- Student performance view (joins students, results, exams, classes, courses)
CREATE OR REPLACE VIEW v_student_performance AS
SELECT
    s.id AS student_id,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    c.course_name,
    e.exam_type,
    r.marks_obtained,
    r.grade,
    r.percentage
FROM students s
JOIN results r ON s.id = r.student_id
JOIN exams e ON r.exam_id = e.id
JOIN classes cl ON e.class_id = cl.id
JOIN courses c ON cl.course_id = c.id;

-- Class attendance view (aggregate attendance data per class)
CREATE OR REPLACE VIEW v_class_attendance AS
SELECT
    cl.id AS class_id,
    c.course_name,
    cl.section,
    COUNT(DISTINCT sce.student_id) AS total_students,
    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_count,
    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) AS absent_count
FROM classes cl
JOIN courses c ON cl.course_id = c.id
JOIN student_class_enrollment sce ON cl.id = sce.class_id
LEFT JOIN attendance a ON sce.id = a.student_class_id
GROUP BY cl.id, c.course_name, cl.section;
```

### 3.6 Indexes

```sql
-- Indexes for faster queries on frequently searched columns
CREATE INDEX idx_student_user ON students (user_id);
CREATE INDEX idx_instructor_user ON instructors (user_id);
CREATE INDEX idx_parent_user ON parents (user_id);
CREATE INDEX idx_course_instructor ON courses (instructor_id);
CREATE INDEX idx_class_course ON classes (course_id);
CREATE INDEX idx_class_instructor ON classes (instructor_id);
CREATE INDEX idx_enrollment_student ON student_class_enrollment (student_id);
CREATE INDEX idx_enrollment_class ON student_class_enrollment (class_id);
CREATE INDEX idx_exam_class ON exams (class_id);
CREATE INDEX idx_result_student ON results (student_id);
CREATE INDEX idx_result_exam ON results (exam_id);
CREATE INDEX idx_fee_student ON fees (student_id);
CREATE INDEX idx_payment_fee ON payments (fee_id);
CREATE INDEX idx_notification_user ON notifications (user_id);

-- Additional inline indexes
INDEX idx_username (username)     -- in users table
INDEX idx_email (email)           -- in users, students, instructors
INDEX idx_class_section (class, section) -- in students
INDEX idx_roll_number (roll_number)      -- in students
INDEX idx_exam_date (exam_date)          -- in exams
INDEX idx_status (status)                -- in exams, fees, enrollments
```

---

## 4. Backend (PHP)

### 4.1 Database Connection

The project connects to MySQL using PHP PDO (PHP Data Objects). PDO is used because it supports prepared statements which prevent SQL injection.

```php
$pdo = new PDO("mysql:host=localhost;dbname=fast_university_management;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
```

### 4.2 Dynamic SQL Queries

All queries are executed dynamically based on user input using prepared statements:

```php
// Example: Search students dynamically
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
}
$students = $stmt->fetchAll();
```

```php
// Example: Insert new student with prepared statement
$stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, email, phone, class, section, enrollment_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$firstName, $lastName, $email, $phone, $class, $section, date('Y-m-d')]);
```

### 4.3 Session Management

PHP sessions handle user authentication. After login, the user's role is stored in the session and checked on every page to enforce access control.

---

## 5. Frontend

### 5.1 User Interface

- Built with HTML5 and Tailwind CSS for a clean, responsive design
- Google Material Symbols for icons
- Works on desktop and mobile screens

### 5.2 Forms for Data Entry

Every module has forms for adding and editing records:
- Student registration form (name, email, class, section, etc.)
- Course creation form
- Exam scheduling form
- Result entry form

### 5.3 Tables for Display

Data is displayed in HTML tables with:
- Sortable columns
- Color-coded badges for status (active, pending, paid, etc.)
- Hover effects for better readability

### 5.4 Validation

- Required fields are validated before form submission
- Email format validation
- Role-based access — pages check if the logged-in user has permission
- PHP-side validation before any database operation

---

## 6. Mandatory Features

### 6.1 Login System (Authentication)

- Multi-role login page (admin, instructor, student, parent)
- Passwords stored as bcrypt hashes
- Session-based authentication with role validation on every page
- Logout clears the session

### 6.2 Search and Filtering

- Search bar on students, courses, and instructors pages
- Filter by class, section, department
- Dynamic search using LIKE queries

### 6.3 Reports (Aggregate Functions)

The reports page shows:
- Students per class (COUNT + GROUP BY)
- Enrollments per course (COUNT + GROUP BY + JOIN)
- Student workload summary (SUM of credits)
- Above average workload detection (using subquery with AVG)
- Exam results summary (AVG, MAX, MIN marks per exam)
- Fee collection totals (SUM)

### 6.4 Dashboard

- **Admin dashboard**: Total students, courses, instructors, classes, scheduled exams, enrollments, fees collected, pending fees. Also shows recent enrollments and newly added students.
- **Instructor dashboard**: Assigned classes, students in their classes, upcoming exams
- **Student dashboard**: Enrolled courses, results, attendance, fee status
- **Parent dashboard**: Child's academic progress, attendance, fee payments

---

## 7. File Structure

```
fast_sms/
├── database_schema.sql         -- Complete SQL script with tables + sample data
├── ERD.md                      -- Entity Relationship Diagram
├── Project_Report.md           -- This report
├── README.md                   -- Project documentation
├── index.php                   -- Entry point / Admin dashboard
├── assets/
│   └── images/
│       ├── logo.png
│       ├── campus1.png
│       └── campus2.png
├── auth/
│   ├── login.php               -- Login page
│   └── logout.php              -- Logout handler
├── includes/
│   ├── config.php              -- Session helpers and role checks
│   ├── db.php                  -- Database connection (PDO)
│   ├── header.php              -- HTML head and top navigation
│   ├── sidebar.php             -- Sidebar menu (role-aware)
│   └── footer.php              -- Page footer
└── pages/
    ├── students.php            -- Student CRUD
    ├── instructors.php         -- Instructor CRUD
    ├── courses.php             -- Course CRUD
    ├── classes.php             -- Class management
    ├── enrollments.php         -- Enrollment management
    ├── exams.php               -- Exam scheduling and results
    ├── reports.php             -- Reports and analytics
    ├── instructor_dashboard.php
    ├── student_dashboard.php
    └── parent_dashboard.php
```

---

## 8. How to Run

1. Install XAMPP (Apache + MySQL)
2. Copy the `fast_sms` folder to `C:/xampp/htdocs/`
3. Start Apache and MySQL from XAMPP Control Panel
4. Open phpMyAdmin and import `database_schema.sql`
5. Open browser and go to: `http://localhost/fast_sms/auth/login.php`
6. Login with: **username:** `admin` / **password:** `password123`

---

## 9. Tools Used

- PHP 8.0 (Backend)
- MySQL 8.0 (Database)
- HTML5 + Tailwind CSS (Frontend)
- XAMPP (Local server)
- phpMyAdmin (Database management)
- VS Code (Code editor)
