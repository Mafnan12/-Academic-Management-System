# Entity Relationship Diagram

## FAST University Academic Management System

```mermaid
erDiagram
    USERS ||--o{ STUDENTS : "has"
    USERS ||--o{ INSTRUCTORS : "has"
    USERS ||--o{ PARENTS : "has"

    STUDENTS ||--o{ STUDENT_CLASS_ENROLLMENT : "enrolled_in"
    STUDENT_CLASS_ENROLLMENT ||--o{ ATTENDANCE : "has"
    STUDENT_CLASS_ENROLLMENT ||--o{ EXAM_RESULTS : "has"

    INSTRUCTORS ||--o{ CLASSES : "teaches"

    COURSES ||--o{ CLASSES : "has"
    CLASSES ||--o{ STUDENT_CLASS_ENROLLMENT : "has_students"
    CLASSES ||--o{ EXAMS : "has"
    CLASSES ||--o{ ASSIGNMENTS : "has"

    EXAMS ||--o{ EXAM_RESULTS : "produces"

    PARENTS ||--o{ PARENT_STUDENT_LINK : "links"
    STUDENTS ||--o{ PARENT_STUDENT_LINK : "links"

    STUDENTS ||--o{ FEES : "pays"
    FEES ||--o{ PAYMENTS : "has"

    USERS ||--o{ NOTIFICATIONS : "receives"

    USERS {
        int id PK
        varchar username
        varchar password_hash
        varchar email
        enum role "admin | instructor | student | parent"
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    STUDENTS {
        int id PK
        int user_id FK
        varchar first_name
        varchar last_name
        varchar email
        varchar phone
        date date_of_birth
        enum gender "male | female | other"
        text address
        varchar class
        varchar section
        varchar roll_number
        enum status "active | transferred | left"
        varchar bform_cnic
        text parent_info
        varchar admission_year
        varchar batch_year
        date enrollment_date
        varchar guardian_name
        varchar guardian_phone
        varchar guardian_email
        timestamp created_at
    }

    INSTRUCTORS {
        int id PK
        int user_id FK
        varchar first_name
        varchar last_name
        varchar email
        varchar phone
        varchar department
        varchar qualification
        varchar specialization
        date hire_date
        decimal salary
        timestamp created_at
    }

    PARENTS {
        int id PK
        int user_id FK
        varchar first_name
        varchar last_name
        varchar email
        varchar phone
        text address
        varchar occupation
        enum relationship "father | mother | guardian"
        timestamp created_at
    }

    PARENT_STUDENT_LINK {
        int id PK
        int parent_id FK
        int student_id FK
        enum relationship "father | mother | guardian"
        boolean is_primary
        timestamp created_at
    }

    COURSES {
        int id PK
        varchar course_code
        varchar course_name
        varchar department
        text description
        int credits
        decimal fee
        int instructor_id FK
        int credit_hours
        varchar semester
        boolean is_active
        timestamp created_at
    }

    CLASSES {
        int id PK
        int course_id FK
        int instructor_id FK
        varchar section
        varchar room_number
        varchar room
        varchar schedule
        int capacity
        int enrolled_count
        varchar academic_year
        varchar semester
        boolean is_active
        timestamp created_at
    }

    STUDENT_CLASS_ENROLLMENT {
        int id PK
        int student_id FK
        int class_id FK
        date enrollment_date
        enum status "enrolled | dropped | completed"
        varchar grade
        decimal gpa
        timestamp created_at
    }

    EXAMS {
        int id PK
        int class_id FK
        varchar exam_name
        enum exam_type "quiz | midterm | final | assignment | project"
        date exam_date
        time start_time
        time end_time
        decimal total_marks
        decimal passing_marks
        varchar room_number
        enum status "scheduled | ongoing | completed | cancelled"
        timestamp created_at
    }

    EXAM_RESULTS {
        int id PK
        int exam_id FK
        int student_id FK
        decimal marks_obtained
        decimal percentage
        varchar grade
        text remarks
        int entered_by FK
        timestamp submitted_at
        timestamp created_at
    }

    ASSIGNMENTS {
        int id PK
        int class_id FK
        varchar title
        text description
        date due_date
        timestamp created_at
    }

    ATTENDANCE {
        int id PK
        int student_id FK
        int class_id FK
        date attendance_date
        enum status "present | absent | late | excused"
        int marked_by FK
        text remarks
        timestamp created_at
    }

    FEES {
        int id PK
        int student_id FK
        enum fee_type "tuition | exam | library | lab | transport | other"
        decimal amount
        date due_date
        date paid_date
        decimal paid_amount
        enum status "pending | paid | overdue | waived"
        varchar semester
        text description
        timestamp created_at
    }

    PAYMENTS {
        int id PK
        int fee_id FK
        decimal amount_paid
        date payment_date
        timestamp created_at
    }

    NOTIFICATIONS {
        int id PK
        int user_id FK
        varchar title
        text message
        enum type "info | warning | success | error"
        boolean is_read
        timestamp created_at
    }
```

## Key Relationships

| Relationship | Type | Description |
|---|---|---|
| Users → Students/Instructors/Parents | 1:1 | Each role profile links to a user account |
| Parents ↔ Students | M:N | Via `parent_student_link` junction table |
| Courses → Classes | 1:N | Each course has multiple sections |
| Classes → Student Enrollment | 1:N | Students enroll in class sections |
| Classes → Exams | 1:N | Exams belong to specific class sections |
| Exams → Results | 1:N | Each exam produces results per student |
| Students → Fees | 1:N | Fee records per student per semester |
| Students → Attendance | 1:N | Daily attendance per class |

## Normalization Notes

- **3NF Compliant**: No transitive dependencies
- **Junction Tables**: `parent_student_link` and `student_class_enrollment` resolve M:N relationships
- **Referential Integrity**: All foreign keys enforce CASCADE or SET NULL on delete
- **Indexing**: Performance indexes on frequently queried columns (email, department, status, dates)
