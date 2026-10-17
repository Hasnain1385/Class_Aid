# Class Aid - MVP Implementation Specification (Updated)

## 1. System Architecture Overview

* **Backend Engine:** PHP 8.4 (Vanilla modern PHP, strict typing `declare(strict_types=1);`, clean MVC architecture, zero bloat, lightning performance).
* **Database Layer:** PDO supporting both **SQLite 3** (`database/database.sqlite`) and **MySQL 8.x** (with standard non-root user support).
* **Environment Configuration:** `.env` parsed securely by a lightweight `Config` loader.
* **First-Launch Wizard:** Automatic installer at `/install` when `.env` is absent or `APP_INSTALLED=false`.
* **Frontend:** Mobile-first responsive Glassmorphism layout using Tailwind CSS CDN + custom frosted glass CSS engine, Lucide icons, Dark/Light mode switcher.
* **Exports:** Native CSV stream generator + print-optimized CSS for PDF printing.

---

## 2. Environment (.env) Variables

```env
APP_NAME="Class Aid"
APP_ENV=production
APP_KEY=base64_secret_key_here
APP_DEBUG=false
APP_URL=http://localhost:8000
APP_INSTALLED=false

# Dual Database Support: 'sqlite' or 'mysql'
DB_CONNECTION=sqlite

# SQLite settings
DB_DATABASE=database/database.sqlite

# MySQL settings (used when DB_CONNECTION=mysql)
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=class_aid
DB_USER=teacher_user
DB_PASSWORD=secret_password
```

---

## 3. Core Database Tables (ANSI SQL Compatible)

1. `users`: Teacher / admin accounts (supports non-root MySQL user).
2. `classes`: Home class + subject classes (flag `is_home_class`).
3. `subjects`: Multiple subjects per class or single subject.
4. `students`: Universal `student_admin_id` (alphanumeric/numeric), contact info, guardian details.
5. `parent_call_logs`: Date/time, category with visual badge, contact person, dialed phone, discussion notes, parent response/commitment, follow-up date, WhatsApp linkable.
6. `attendance`: Daily records per student per class (`P`, `A`, `L`, `E`), consecutive absence counter.
7. `exams`: Exam/test definitions with max marks and pass marks.
8. `marks`: Numerical marks scored, automatic percentage, and computed letter grade (`A+` to `F`).

---

## 4. Mobile-First Glassmorphism UI Architecture

- **Mobile Viewport:**
  - Sticky glassmorphic top header with quick student search bar and theme toggle.
  - Sticky glassmorphic bottom navigation bar with 5 primary touch tabs:
    1. 📊 **Dashboard**
    2. 📞 **Call Log**
    3. 📅 **Attendance**
    4. 👥 **Students**
    5. 📝 **Marks**
  - High-contrast touch targets, swipe-friendly cards, and modal bottom-sheets for mobile ease of use.
- **Desktop/Tablet Viewport:**
  - Glassmorphic side navigation with collapsible menu.
  - Wide multi-column data grids with spreadsheet keyboard navigation for mark entry.
- **Dark/Light Mode:**
  - Seamless toggle with state stored in `localStorage` and synchronized across all views.
  - Dark: Deep slate/midnight blue glass with translucent cards (`rgba(15, 23, 42, 0.75)`).
  - Light: Soft radiant ambient mesh with frosted white cards (`rgba(255, 255, 255, 0.75)`).

---

## 5. Module Implementation Phases

- **Module 1: Foundation, Installer & Auth**
  - First-launch web installer detecting missing `.env`.
  - Step-by-step setup: Environment check, SQLite/MySQL DB setup, Admin teacher account setup, migration runner.
  - Auth system: Login, Logout, CSRF protection, Session management.
  - Mobile-first Glassmorphic Master Layout with Dark/Light toggle.

- **Module 2: Universal Student Directory & Classes**
  - Class management (Home class + 4 subject classes; support multiple subjects per class).
  - Student registration with flexible `student_admin_id` (alphanumeric/numeric).
  - Instant typeahead search by ID, name, or phone.
  - Student 360° Dossier View (Overview, Calls, Attendance, Results).

- **Module 3: Parent Communication Journal (Priority Core)**
  - Quick Call Logging Modal & Form with 7 highlighted categories.
  - Direct WhatsApp quick-action link (`https://wa.me/...`).
  - Discussion notes & Parent Response/Commitment fields.
  - One-click Print/PDF view and CSV/Excel export (per student and per class).

- **Module 4: Attendance Register & Consecutive Absence Detection**
  - Fast mobile roll call (1-tap toggle `P`, `A`, `L`, `E`).
  - Detection and alert badge for **3 consecutive absences**.
  - Direct 1-tap "Log Parent Call" button from attendance alert.
  - Monthly attendance percentage & reporting.

- **Module 5: Examinations, Mark Entry & Gradebook**
  - Exam creation per subject/class.
  - Numerical mark entry grid with automatic calculation of percentage and letter grades (`A+` through `F`).
  - Printable student report cards and class marksheets.

- **Module 6: Dashboard & Quick Actions Cockpit**
  - Glassmorphic metric cards (Total students, Today's attendance %, Pending follow-ups, Recent calls).
  - Quick action floating buttons for mobile.
