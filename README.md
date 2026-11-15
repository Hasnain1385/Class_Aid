# Class Aid 🎓

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.2-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/Database-SQLite%203%20%7C%20MySQL%208-00758F?style=flat-square&logo=mysql&logoColor=white)](https://www.sqlite.org/)
[![UI Style](https://img.shields.io/badge/Design-Liquid%20Glassmorphism-6366F1?style=flat-square)](https://github.com/Hasnain1385/Class_Aid)
[![Tests](https://img.shields.io/badge/Tests-26%2F26%20Passing%20(100%25)-10B981?style=flat-square&logo=checkmarx&logoColor=white)](https://github.com/Hasnain1385/Class_Aid)
[![License](https://img.shields.io/badge/License-MIT-blue?style=flat-square)](LICENSE)
[![GitHub Release](https://img.shields.io/github/v/release/Hasnain1385/Class_Aid?style=flat-square&color=8B5CF6)](https://github.com/Hasnain1385/Class_Aid/releases)

**Class Aid** is a modern, high-performance School & Classroom Management System and Teacher SaaS Platform built with native PHP 8.2+. Engineered for multi-class educators and school administrators, it pairs an ultra-clean **Liquid Glass** interface with seamless Dark/Light theming, zero heavy framework overhead, dual-database flexibility (SQLite / MySQL), and an automated in-place update engine directly synchronized with GitHub Releases.

---

## 🌟 Key Features

### 💎 Liquid Glassmorphism UI
- **Zero-Flicker Dual Theme Engine:** Smooth, instantaneous transitions between Light (`#f8fafc`) and Dark (`#090d16`) palettes with localStorage persistence and CSS custom properties (`--bg-primary`, `--card-bg`, `--card-border`, `--glass-blur`).
- **Clean Aesthetic & High Contrast:** Ergonomic contrast ratios eliminate washed-out text and cluttered layouts; utilizes crisp [Lucide](https://lucide.dev/) vector iconography without distracting emojis.
- **Micro-Interactions & Skeletons:** Polished loading ladders, smooth transitions, toast alerts, and responsive modal dialogues designed for desktop and mobile viewports.

### 📋 Student Roster & Academic Dossier
- **Universal Alphanumeric Admin ID:** Automatic or custom identifier generation per student for permanent academic records.
- **Comprehensive Dossiers:** Student profiles with emergency contacts, guardian details, enrollment status, medical notes, and classroom assignments.
- **Fast Dual-DB Search:** Real-time search across first name, last name, full concatenated name, or Admin ID with SQL-optimized indexing for both SQLite and MySQL.

### ⏱️ Attendance Tracking & Early Intervention
- **Streamlined Roll Call:** Record daily attendance statuses (Present, Absent, Late, Excused) with one click.
- **Automated Absence Alert:** Built-in intelligence automatically detects and flags students with **3 or more consecutive absences**, empowering educators to intervene before students fall behind.

### 📞 Parent Communication Journal & Auditing
- **Interaction Logging:** Detailed tracking of phone calls, in-person parent conferences, and messages.
- **Categorization:** Log communications by type (*Academic*, *Behavioral*, *Attendance*, *General*).
- **Accountability & Follow-ups:** Record actionable commitments, target follow-up deadlines, and outcome auditing.

### 📊 Automated Gradebook & Report Cards
- **`GradeCalculator` Engine:** Standardized computation of percentages, letter grades (A+, A, B, C, D, F), and GPA points.
- **Batch Mark Entry:** Rapid entry grid for classroom exams, midterms, and finals.
- **Student Performance Dossier:** Individual academic report cards displaying completed tests, percentage rankings, and subject breakdown.

### 📥 Smart Import Parser (CSV & OCR Text)
- **Flexible CSV Importer:** Dynamic header resolution parses student rosters regardless of column ordering.
- **OCR Raw Text Parser:** Ingests unformatted optical character recognition (OCR) mark sheets and text outputs, matching student records fuzzy/exact to populate examination marks automatically.

### 💳 SaaS Multi-Tier Subscriptions & Payment Gateways
- **Configurable Subscription Tiers:** Built-in support for Free Trial, Starter, Pro, and Enterprise tiers with custom student limits and feature gates.
- **Dynamic Payment Gateways:** Configure local and international payment methods (Bank Transfer, JazzCash, EasyPaisa, NayaPay, SadaPay, PayPal, Stripe manual instructions).
- **Offline Vector SVG QR Code Engine:** Generates deterministic SVG payment QR codes natively on the server without external network dependencies or third-party tracking.
- **Proof-of-Payment Verification:** Subscribers upload transaction receipts; Super Admins review, approve, or reject submissions via a unified administrative queue.
- **Automated Invoicing:** Generates downloadable invoices with automated dispatch via SMTP mailer.

### 🔔 Reminders & Broadcast Announcements
- **Educator Task Reminders:** Lightweight personal to-do manager with priority tags, completion toggles, and due-date alerts.
- **Audience-Filtered Announcements:** Super Admins can publish broadcast announcements targeted to *All Users* or *Teachers Only*, accompanied by real-time notification badges.

### 🔄 In-Place Self-Updating Engine
- **GitHub Release Integration:** Automatically checks the GitHub repository (`Hasnain1385/Class_Aid`) for new releases, changelogs, and asset updates.
- **Atomic Extraction & Rollback Snapshots:** Creates automatic zip backups in `storage/backups/` before updates.
- **Strict Protected Paths Whitelist:** Hardened filters ensure sensitive files (`.env`, `database/`, `storage/uploads/`, and configuration keys) are **never overwritten or altered** during updates.
- **CLI & Web Execution:** Trigger updates directly from the Admin Dashboard or via command line (`php update.php` / `./bin/update`).

---

## 🏗️ Architecture & Technology Stack

| Layer | Technology | Details |
| :--- | :--- | :--- |
| **Backend** | Native PHP 8.2+ | Strict types (`declare(strict_types=1)`), PSR-4 autoloading, custom lightweight MVC router. |
| **Database** | SQLite 3 & MySQL 8+ | Native PDO abstraction layer with unified schemas (`database/schema_sqlite.sql`, `database/schema_mysql.sql`). |
| **Frontend** | Vanilla ES6+ & CSS3 | Custom Liquid Glass design system, Lucide SVG icons, zero bulky runtime frameworks. |
| **Security** | CSRF & Bcrypt | Per-session 64-char hex CSRF tokens, HTTP-only secure cookie flags, Bcrypt password hashing. |
| **QR Engine** | Native SVG Generator | Deterministic vector matrix generator in PHP (zero third-party API calls). |
| **Testing** | Custom Test Harness | Full end-to-end integration and unit test suite (`tests/test_suite.php`). |

---

## 📁 Project Directory Structure

```text
Class_Aid/
├── bin/
│   └── update                 # CLI executable for in-place updates
├── database/
│   ├── schema_mysql.sql       # MySQL / MariaDB schema definition
│   └── schema_sqlite.sql      # SQLite schema definition
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   └── glassmorphism.css  # Liquid Glass design tokens & styles
│   │   └── js/
│   │       └── app.js             # Theme switcher, modals, Lucide initialization
│   ├── uploads/               # User avatars and payment receipts
│   └── index.php              # Application front controller & static router
├── src/
│   ├── Controllers/           # Application controllers (Auth, Admin, Student, etc.)
│   ├── Core/                  # Kernel (Router, Database, Request, Response, Csrf, Session)
│   ├── Models/                # Database entities (User, Student, Attendance, Exam, Plan)
│   ├── Services/              # Core business services (UpdateService)
│   └── Utils/                 # Utility helpers (GradeCalculator, Mailer, QrCodeGenerator)
├── storage/
│   ├── backups/               # Automated snapshots created prior to updates
│   ├── logs/                  # Application runtime error logs
│   └── updates/               # Staged release packages
├── tests/
│   └── test_suite.php         # 26-step automated test suite
├── views/
│   ├── admin/                 # Super Admin control panels
│   ├── attendance/            # Roll call & absence logs
│   ├── calls/                 # Parent communication journal
│   ├── dashboard/             # Teacher dashboard
│   ├── layouts/               # Blade-style PHP layouts (main, admin, auth, install)
│   ├── students/              # Student roster, dossiers, and imports
│   └── subscription/          # Billing, plans, and payment proof submission
├── .env.example               # Configuration template
├── composer.json              # Dependency & test definition
├── update.php                 # Direct in-place updater script
└── version.json               # Current build metadata & update track
```

---

## 🚀 Quick Start Guide

### Prerequisites
- **PHP** >= 8.2
- **PHP Extensions:** `pdo`, `pdo_sqlite` (or `pdo_mysql`), `curl`, `zip`, `mbstring`, `fileinfo`
- **Composer** >= 2.0
- Web server (Nginx, Apache, or PHP built-in CLI server)

### 1. Clone & Install Dependencies
```bash
git clone https://github.com/Hasnain1385/Class_Aid.git
cd Class_Aid
composer install
```

### 2. Configure Environment
Copy the sample environment file:
```bash
cp .env.example .env
```
Open `.env` and set your preferred database connection:
```ini
APP_NAME="Class Aid"
APP_ENV=production
APP_URL=http://localhost:8000
APP_INSTALLED=false

# For SQLite:
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# For MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_NAME=class_aid
# DB_USER=your_db_user
# DB_PASSWORD=your_db_password
```

### 3. Run the Web Setup Wizard
Start the PHP built-in development server:
```bash
php -S 0.0.0.0:8000 -t public
# Or from root directory:
# php -S 0.0.0.0:8000 public/index.php
```

Navigate to `http://localhost:8000/install` in your browser. The automated setup wizard will:
1. Verify system environment requirements & write permissions.
2. Automatically run migrations to create all database tables.
3. Provision your **Super Admin** master credentials.

---

## 🔄 Self-Updating via GitHub Releases

Class Aid includes an in-place updater connected directly to GitHub Releases (`Hasnain1385/Class_Aid`).

### From the Admin Panel:
Navigate to **Admin Dashboard -> System Updates** to inspect version details, compare commit hashes, view changelogs, and perform a 1-click update.

### From the Command Line:
```bash
php update.php
# Or using the binary wrapper:
chmod +x bin/update
./bin/update
```

> [!NOTE]
> **Safety Guarantee:** Before extracting release files, the updater creates a timestamped zip archive in `storage/backups/`. Protected files (`.env`, `database/*`, `storage/uploads/*`) are strictly preserved.

---

## 🧪 Running the Test Suite

Class Aid comes with an end-to-end integration test suite covering database transactions, CSRF validation, password hashing, grading calculations, CSV/OCR parsing, and the in-place updater:

```bash
composer test
```
*or directly:*
```bash
php tests/test_suite.php
```

**Test Coverage Summary:**
```text
=== RUNNING CLASS AID COMPREHENSIVE TEST SUITE ===
[1. Core Architecture & Database Layer]         ✔ 4 tests passed
[2. SaaS Plans & Dynamic Payment Gateways]      ✔ 3 tests passed
[3. User Authentication & Password Resets]      ✔ 2 tests passed
[4. Classroom Roster, Students & Search]        ✔ 3 tests passed
[5. Attendance Roll Call & Consecutive Absence] ✔ 1 test passed
[6. Parent Communication Journal & Auditing]    ✔ 1 test passed
[7. Gradebook & Automated Grade Calculation]    ✔ 2 tests passed
[8. Smart Import Parser (CSV & OCR Text)]       ✔ 2 tests passed
[9. SaaS Billing, Subscriptions & Invoicing]    ✔ 2 tests passed
[10. Reminders, Broadcasts & Notifications]     ✔ 2 tests passed
[11. In-Place Update Module & GitHub Releases]  ✔ 4 tests passed
=====================================================
Total Tests: 26 | Passed: 26 | Failed: 0 (100% Clean)
```

---

## 🔒 Security & Best Practices

- **CSRF Defense:** Every state-altering HTTP request (`POST`, `PUT`, `DELETE`) is guarded by strict token verification.
- **SQL Injection Prevention:** 100% of database queries use PDO prepared statements with parameterized inputs.
- **Zero Credentials in Git:** The `.gitignore` is pre-configured to strictly exclude `.env`, SQLite database files, backup archives, and user upload storage.
- **Production Web Server Tip:** For Apache or Nginx deployments, ensure the web server document root points specifically to the `public/` directory so that source code and database files remain inaccessible from the web.

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).
Built with precision for educators and school administrators worldwide.
