-- SQLite Schema for Class Aid

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT DEFAULT 'teacher',
    school_name TEXT DEFAULT 'School',
    status TEXT DEFAULT 'active',
    phone TEXT,
    avatar TEXT,
    reset_token TEXT,
    reset_token_expires_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    teacher_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    section TEXT NOT NULL,
    academic_year TEXT NOT NULL,
    is_home_class INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    code TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_admin_id TEXT NOT NULL UNIQUE,
    class_id INTEGER NOT NULL,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    gender TEXT,
    dob DATE,
    primary_guardian_name TEXT NOT NULL,
    primary_guardian_relation TEXT DEFAULT 'Parent',
    primary_phone TEXT NOT NULL,
    secondary_phone TEXT,
    guardian_email TEXT,
    address TEXT,
    status TEXT DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS parent_call_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    teacher_id INTEGER NOT NULL,
    contact_person_name TEXT NOT NULL,
    relationship TEXT NOT NULL,
    phone_dialed TEXT NOT NULL,
    call_timestamp DATETIME NOT NULL,
    call_type TEXT DEFAULT 'Phone Call',
    call_category TEXT NOT NULL,
    discussion_summary TEXT NOT NULL,
    parent_response TEXT NOT NULL,
    follow_up_required INTEGER DEFAULT 0,
    follow_up_date DATE,
    status TEXT DEFAULT 'Completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS attendance (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    class_id INTEGER NOT NULL,
    attendance_date DATE NOT NULL,
    session_type TEXT DEFAULT 'morning',
    status TEXT NOT NULL,
    remarks TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(student_id, attendance_date, session_type),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS exams (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    subject_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    term TEXT NOT NULL,
    max_marks REAL NOT NULL DEFAULT 100.0,
    pass_marks REAL NOT NULL DEFAULT 40.0,
    exam_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS marks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    marks_obtained REAL NOT NULL,
    percentage REAL NOT NULL,
    grade TEXT NOT NULL,
    remarks TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(exam_id, student_id),
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS plans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    cycle TEXT NOT NULL DEFAULT 'monthly',
    days INTEGER,
    price REAL NOT NULL DEFAULT 0.0,
    currency TEXT DEFAULT 'USD',
    discount TEXT,
    popular INTEGER DEFAULT 0,
    features TEXT,
    badge TEXT,
    is_active INTEGER DEFAULT 1,
    sort_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS subscriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    plan_code TEXT NOT NULL,
    plan_name TEXT NOT NULL,
    billing_cycle TEXT NOT NULL,
    price REAL NOT NULL DEFAULT 0.0,
    currency TEXT DEFAULT 'USD',
    status TEXT NOT NULL DEFAULT 'active',
    starts_at DATETIME NOT NULL,
    expires_at DATETIME,
    auto_renew INTEGER DEFAULT 1,
    payment_method TEXT DEFAULT 'card',
    payment_reference TEXT,
    payment_proof TEXT,
    pending_plan TEXT,
    pending_price REAL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS announcements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    type TEXT DEFAULT 'info',
    target_role TEXT DEFAULT 'all',
    is_published INTEGER DEFAULT 1,
    starts_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS reminders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    due_date DATE NOT NULL,
    due_time TEXT,
    priority TEXT DEFAULT 'medium',
    status TEXT DEFAULT 'pending',
    category TEXT DEFAULT 'general',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payment_methods (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL UNIQUE,
    title TEXT NOT NULL,
    account_title TEXT,
    account_number TEXT,
    qr_payload TEXT,
    qr_image TEXT,
    instructions TEXT,
    currency TEXT DEFAULT 'PKR',
    conversion_rate REAL DEFAULT 280.0,
    is_active INTEGER DEFAULT 1,
    sort_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS invoices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_number TEXT NOT NULL UNIQUE,
    user_id INTEGER NOT NULL,
    subscription_id INTEGER,
    plan_code TEXT NOT NULL,
    plan_name TEXT NOT NULL,
    billing_cycle TEXT NOT NULL,
    amount REAL NOT NULL,
    currency TEXT DEFAULT 'USD',
    payment_method TEXT DEFAULT 'easypaisa',
    payment_reference TEXT,
    payment_proof TEXT,
    status TEXT NOT NULL DEFAULT 'unpaid',
    issue_date DATE NOT NULL,
    due_date DATE,
    paid_at DATETIME,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL
);

-- Performance Indexes for low RAM environments & rapid lookups
CREATE INDEX IF NOT EXISTS idx_classes_teacher ON classes(teacher_id);
CREATE INDEX IF NOT EXISTS idx_subjects_class ON subjects(class_id);
CREATE INDEX IF NOT EXISTS idx_students_class ON students(class_id);
CREATE INDEX IF NOT EXISTS idx_students_admin_id ON students(student_admin_id);
CREATE INDEX IF NOT EXISTS idx_attendance_student_date ON attendance(student_id, attendance_date);
CREATE INDEX IF NOT EXISTS idx_attendance_class_date ON attendance(class_id, attendance_date);
CREATE INDEX IF NOT EXISTS idx_attendance_status ON attendance(status);
CREATE INDEX IF NOT EXISTS idx_marks_exam_student ON marks(exam_id, student_id);
CREATE INDEX IF NOT EXISTS idx_marks_student ON marks(student_id);
CREATE INDEX IF NOT EXISTS idx_exams_subject ON exams(subject_id);
CREATE INDEX IF NOT EXISTS idx_parent_call_logs_student ON parent_call_logs(student_id);
CREATE INDEX IF NOT EXISTS idx_parent_call_logs_teacher ON parent_call_logs(teacher_id);
CREATE INDEX IF NOT EXISTS idx_subscriptions_user ON subscriptions(user_id);
CREATE INDEX IF NOT EXISTS idx_invoices_user ON invoices(user_id);
CREATE INDEX IF NOT EXISTS idx_reminders_user ON reminders(user_id, due_date);
