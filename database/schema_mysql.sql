-- MySQL Schema for Class Aid (Compatible with non-root user privileges)

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(30) DEFAULT 'teacher',
    school_name VARCHAR(150) DEFAULT 'School',
    status VARCHAR(30) DEFAULT 'active',
    phone VARCHAR(50) NULL,
    avatar VARCHAR(255) NULL,
    reset_token VARCHAR(100) NULL,
    reset_token_expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    section VARCHAR(20) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    is_home_class TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_classes_teacher (teacher_id),
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(30) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_subjects_class (class_id),
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_admin_id VARCHAR(60) NOT NULL UNIQUE,
    class_id INT NOT NULL,
    first_name VARCHAR(60) NOT NULL,
    last_name VARCHAR(60) NOT NULL,
    gender VARCHAR(10) NULL,
    dob DATE NULL,
    primary_guardian_name VARCHAR(120) NOT NULL,
    primary_guardian_relation VARCHAR(40) DEFAULT 'Parent',
    primary_phone VARCHAR(40) NOT NULL,
    secondary_phone VARCHAR(40) NULL,
    guardian_email VARCHAR(150) NULL,
    address TEXT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_students_class (class_id),
    INDEX idx_students_admin_id (student_admin_id),
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS parent_call_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    teacher_id INT NOT NULL,
    contact_person_name VARCHAR(120) NOT NULL,
    relationship VARCHAR(40) NOT NULL,
    phone_dialed VARCHAR(40) NOT NULL,
    call_timestamp DATETIME NOT NULL,
    call_type VARCHAR(40) DEFAULT 'Phone Call',
    call_category VARCHAR(60) NOT NULL,
    discussion_summary TEXT NOT NULL,
    parent_response TEXT NOT NULL,
    follow_up_required TINYINT(1) DEFAULT 0,
    follow_up_date DATE NULL,
    status VARCHAR(30) DEFAULT 'Completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_call_student (student_id),
    INDEX idx_call_teacher (teacher_id),
    INDEX idx_call_date (call_timestamp),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    session_type VARCHAR(20) DEFAULT 'morning',
    status VARCHAR(10) NOT NULL,
    remarks VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_date_session (student_id, attendance_date, session_type),
    INDEX idx_attendance_date (attendance_date),
    INDEX idx_attendance_class (class_id),
    INDEX idx_attendance_session (session_type),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    title VARCHAR(120) NOT NULL,
    term VARCHAR(60) NOT NULL,
    max_marks DECIMAL(6,2) NOT NULL DEFAULT 100.00,
    pass_marks DECIMAL(6,2) NOT NULL DEFAULT 40.00,
    exam_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_exams_subject (subject_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    marks_obtained DECIMAL(6,2) NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    grade VARCHAR(10) NOT NULL,
    remarks VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_exam_student (exam_id, student_id),
    INDEX idx_marks_student (student_id),
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    cycle VARCHAR(50) NOT NULL DEFAULT 'monthly',
    days INT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    currency VARCHAR(10) DEFAULT 'USD',
    discount VARCHAR(100) NULL,
    popular TINYINT(1) DEFAULT 0,
    features TEXT NULL,
    badge VARCHAR(100) NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_plan_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_code VARCHAR(50) NOT NULL,
    plan_name VARCHAR(100) NOT NULL,
    billing_cycle VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    currency VARCHAR(10) DEFAULT 'USD',
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    starts_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    auto_renew TINYINT(1) DEFAULT 1,
    payment_method VARCHAR(50) DEFAULT 'card',
    payment_reference VARCHAR(100) NULL,
    payment_proof VARCHAR(255) NULL,
    pending_plan VARCHAR(50) NULL,
    pending_price DECIMAL(10,2) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subs_user (user_id),
    INDEX idx_subs_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    target_role VARCHAR(50) DEFAULT 'all',
    is_published TINYINT(1) DEFAULT 1,
    starts_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ann_status (is_published, target_role),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    due_date DATE NOT NULL,
    due_time VARCHAR(20) NULL,
    priority VARCHAR(20) DEFAULT 'medium',
    status VARCHAR(20) DEFAULT 'pending',
    category VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rem_user (user_id, status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(100) NOT NULL,
    account_title VARCHAR(150) NULL,
    account_number VARCHAR(100) NULL,
    qr_payload TEXT NULL,
    qr_image VARCHAR(255) NULL,
    instructions TEXT NULL,
    currency VARCHAR(10) DEFAULT 'PKR',
    conversion_rate DECIMAL(10,2) DEFAULT 280.00,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(100) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    subscription_id INT NULL,
    plan_code VARCHAR(50) NOT NULL,
    plan_name VARCHAR(150) NOT NULL,
    billing_cycle VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    payment_method VARCHAR(50) DEFAULT 'easypaisa',
    payment_reference VARCHAR(100) NULL,
    payment_proof VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'unpaid',
    issue_date DATE NOT NULL,
    due_date DATE NULL,
    paid_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_inv_user (user_id),
    INDEX idx_inv_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
