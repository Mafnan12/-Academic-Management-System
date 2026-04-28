# FAST University Management System

A comprehensive web-based university management system built with PHP, MySQL, and Tailwind CSS. This system provides role-based access control for administrators, instructors, students, and parents to manage academic activities efficiently.

## 🚀 Features

### Core Functionality
- **Multi-Role Authentication**: Admin, Instructor, Student, and Parent roles with hierarchical permissions
- **Student Management**: Complete student profiles with class, section, roll number, and academic details
- **Course Management**: Create and manage courses with departments and credit hours
- **Class Management**: Organize students into classes and sections
- **Instructor Management**: Assign instructors to courses and classes
- **Enrollment System**: Track student enrollments in classes
- **Examination System**: Schedule and manage exams with results tracking
- **Results Management**: Record and display student exam results
- **Attendance Tracking**: Monitor student attendance (framework ready)
- **Fee Management**: Track student fees and payments
- **Parent Portal**: Parents can monitor their children's progress
- **Reports & Analytics**: Generate comprehensive reports

### User Dashboards
- **Admin Dashboard**: System overview, user management, and analytics
- **Instructor Dashboard**: Class management, student results, and assignments
- **Student Dashboard**: Course enrollment, results, and attendance
- **Parent Dashboard**: Children's progress, fees, and notifications

### Technical Features
- **Responsive Design**: Mobile-friendly interface with glassmorphism effects
- **Secure Authentication**: Session-based login with role validation
- **Database Optimization**: Indexed tables with foreign key constraints
- **Modern UI**: Tailwind CSS with Material Symbols icons
- **Error Handling**: Comprehensive error management and validation

## 🛠 Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache or Nginx web server

### Setup Steps

1. Copy the `fast_sms` folder to your web server root.
2. Create a MySQL database named `fast_university_management`.
3. Open `setup.php` in your browser to create tables and seed sample data.
4. Alternatively, import `database_schema.sql` directly in MySQL.

### Configure the database
Update `includes/db.php` with your database credentials:

```php
$host = 'localhost';
$dbname = 'fast_university_management';
$user = 'root';
$pass = '';
```

### Login
Open `auth/login.php` in the browser.

Default admin credentials:
- Username: `admin`
- Password: `password123`

> `setup.php` chooses `database_schema.sql` if it exists, otherwise it uses `database.sql`.

## 📁 Project Structure

```
fast_sms/
├── auth/
│   ├── login.php
│   └── logout.php
├── includes/
│   ├── config.php
│   ├── db.php
│   ├── footer.php
│   ├── header.php
│   └── sidebar.php
├── pages/
│   ├── classes.php
│   ├── courses.php
│   ├── enrollments.php
│   ├── exams.php
│   ├── instructors.php
│   ├── instructor_dashboard.php
│   ├── parent_dashboard.php
│   ├── reports.php
│   ├── student_dashboard.php
│   └── students.php
├── assets/
│   └── images/
├── database_schema.sql
├── index.php
├── README.md
└── setup.php
```

## 🗄 Database Schema

### Main tables
- `users`
- `students`
- `instructors`
- `parents`
- `courses`
- `classes`
- `student_class_enrollment`
- `exams`
- `exam_results`
- `attendance`
- `fees`
- `notifications`

### Relationships
- Students ↔ Classes via `student_class_enrollment`
- Courses → Classes
- Instructors → Classes
- Parents ↔ Students via `parent_student_link`
- Classes → Exams
- Students → Exam Results and Attendance

## 🔐 Roles and Permissions

### Admin
- Full access across the system
- Manage users, courses, classes, enrollments, exams, and reports

### Instructor
- View assigned classes
- Access instructor dashboard and reports

### Student
- View enrolled courses and results
- Access student dashboard

### Parent
- Monitor child progress from parent dashboard

## 🎨 Design Notes

- Built with Tailwind CSS for responsive UI
- Glassmorphism cards and gradient accents
- Material Symbols icons for navigation
- Uses `assets/images/logo.png`, `campus1.jpg`, and `campus2.jpg`

## 🔧 Configuration

### Database connection
Update `includes/db.php` with your environment settings:

```php
$host = 'localhost';
$dbname = 'fast_university_management';
$user = 'root';
$pass = '';
```

### Session helpers
`includes/config.php` includes functions such as:
- `check_login()`
- `is_admin()`
- `is_instructor()`
- `is_student()`
- `is_parent()`
- `has_permission()`

## 📊 Sample Data

`database_schema.sql` includes seeded sample records for:
- Admin user
- Instructors
- Students
- Courses
- Classes
- Enrollments
- Exams and exam results

## 🚀 Recommended Enhancements

- Add dedicated Instructor and Parent management pages
- Add attendance entry workflows
- Add CSRF protection for form submissions
- Add file upload support for profile photos and assignments
- Add chart-based analytics to the reports page
- Add notifications for users

## 🐛 Troubleshooting

### Common issues
- Database connection failed: check `includes/db.php` and MySQL status
- Login redirect issues: check `BASE_URL` in `includes/config.php`
- Missing images: verify `assets/images/logo.png`, `campus1.jpg`, and `campus2.jpg`

## 📄 License

This project is open source and available under the MIT License.


