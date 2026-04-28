# FAST University - Student Management System

A fully functional, web-based Student Management System built using **PHP** and **MySQL**, designed with modern Glassmorphism aesthetics (TailwindCSS). This system provides complete CRUD functionality for managing students, courses, instructors, and enrollments.

## 🚀 Features

- **Authentication System**: Secure login system with hashed passwords and role-based access control (Admin / User).
- **Dashboard Analytics**: Real-time stats, recent enrollments, and quick insights using aggregated data.
- **Student & Faculty Management**: Complete CRUD operations for students and instructors.
- **Course & Curriculum Management**: Define courses and allocate instructors dynamically.
- **Enrollment Processing**: Associate students with courses, with safeguards against duplicate registrations.
- **Advanced Reports**: Features SQL subqueries, `INNER JOIN` operations, and a database View (`v_student_course_summary`) for student workload analytics.
- **Modern UI/UX**: Extracted from Stitch, utilizing TailwindCSS, Google Material Symbols, and responsive flex/grid layouts.

## ⚙️ Tech Stack

- **Frontend**: HTML5, CSS3, TailwindCSS (CDN)
- **Backend**: PHP 8.x (Procedural with MVC-lite structural separation)
- **Database**: MySQL (PDO Extension with Prepared Statements)
- **Security**: Password Hashing (`password_hash`), Session hijacking prevention, SQL Injection prevention.

## 📂 Project Structure

```text
/fast_sms
│── /assets
│   └── /images
│       ├── logo.png       (FAST University Logo)
│       ├── campus1.jpg    (Login Background Image)
│       └── campus2.jpg    (Dashboard Banner Image)
│── /auth
│   ├── login.php
│   └── logout.php
│── /includes
│   ├── config.php         (Constants, Flash Messaging, Session Handling)
│   ├── db.php             (PDO Database Connection)
│   ├── header.php         (Tailwind Setup, Top Navigation)
│   ├── sidebar.php        (Sidebar Navigation)
│   └── footer.php
│── /pages
│   ├── students.php       (Student CRUD & Search)
│   ├── courses.php        (Courses CRUD)
│   ├── instructors.php    (Instructors CRUD + Subqueries)
│   ├── enrollments.php    (Enrollment Processing + JOINs)
│   └── reports.php        (Aggregations & Views)
│── index.php              (Admin Dashboard)
└── database.sql           (Schema + Dummy Data)
```

## 🛠️ Setup Instructions

### 1. Database Configuration
1. Open XAMPP/WAMP and start **Apache** and **MySQL**.
2. Open phpMyAdmin (`http://localhost/phpmyadmin`).
3. Import the `database.sql` file provided in the root directory. This will automatically create the `fast_student_management` database, build all 5 normalized tables with indexes, create the view, and insert the dummy data.

### 2. Assets Configuration
Place your university image assets into the `/assets/images/` folder:
- Name the logo as `logo.png`.
- Name the campus pictures as `campus1.jpg` and `campus2.jpg`.

### 3. Application Execution
1. Place the `fast_sms` folder into your XAMPP `htdocs` (or WAMP `www`) directory.
2. Open your browser and navigate to: `http://localhost/fast_sms`
3. Log in using the default administrator credentials:
   - **Username**: `admin`
   - **Password**: `password123`

## 📊 Database Grading Criteria Checked

✅ **Normalized Tables**: 5 distinct tables (`users`, `students`, `instructors`, `courses`, `enrollments`).
✅ **Relationships**: 1-to-Many and Many-to-Many mapped correctly with Foreign Keys (`ON DELETE CASCADE`/`SET NULL`).
✅ **Queries**: Features Subqueries (e.g., getting Instructor course counts) and `INNER JOIN`s (Enrollments).
✅ **Views**: SQL View `v_student_course_summary` is actively utilized in the Reports page.
✅ **Indexes**: Active indexing on `first_name`, `last_name`, and foreign keys.
✅ **Localization**: Date format standardized to Pakistani format (`DD-MM-YYYY`).
