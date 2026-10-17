# Product Requirements Document (PRD)
## Project Name: Class Aid (EduAid Suite)
**Target Platform:** Mobile-First Responsive Web Application (PHP 8.4+, Dual SQLite/MySQL, Tailwind CSS Glassmorphism)  
**Primary User:** School Class Teacher managing home class (~30+ students) + 4 subject classes (multi-subject and single-subject support)  
**Future Scope:** Multi-tenant Educational SaaS for Schools & Teachers  
**Date:** October 2026  
**Status:** Approved Specifications (Incorporating User Feedback)  

---

## 1. Executive Summary & Problem Statement

### 1.1 The Problem
Educators face severe administrative burdens managing classroom records across multiple classes and subjects:
1. **Parent Communication Tracking (Critical Pain Point):** Teachers frequently contact parents regarding attendance, misconduct, homework failure, exam marks, or health issues. When queried by school administration, child welfare officers, or parents during PTA meetings ("*When did you inform me about his grades?*"), teachers struggle to retrieve exact dates, contacted persons, and recorded responses.
2. **Attendance Management:** Marking daily attendance, tracking consecutive absences, and flagging chronic absenteeism across 5 different classes is tedious on paper registers.
3. **Academic Results & Grading:** Entering marks for tests, quizzes, mid-terms, and terminal exams across multiple subjects, computing grades and percentages, and generating student-level progress summaries lacks centralization.
4. **Universal Student Identification:** Students move between classes and subjects, requiring a universal, flexible `student_admin_id` (alphanumeric, numeric, or custom format) for accurate tracking.
5. **Mobile Usability:** Teachers do not carry laptops everywhere; they need an interface engineered primarily for **mobile smartphones and tablets** that scales beautifully to desktop web.

### 1.2 The Solution: Class Aid
A high-performance, mobile-first responsive web application engineered in PHP 8.4 with a modern **Glassmorphism UI** (with Dark/Light toggle). It features:
* An audit-grade **Parent Call & Communication Journal** with direct **WhatsApp quick-chat links**, distinct category color highlighting, and instant **Print-Ready / PDF & CSV/Excel export**.
* A **Student 360° Details Pane** with instant typeahead search by Student-Admin ID or name.
* **1-Tap Daily Attendance** with automatic detection and alerts for **3 consecutive absences**.
* **Raw Numerical Mark Entry** with automated percentage and letter grade calculation (`A+, A, B, C, D, F`).
* **Dual Database Engine (SQLite & MySQL)** with environment configuration via `.env` and an **Interactive First-Launch Web Installer** supporting non-root database users.

---

## 2. Key Modules & Functional Specifications

### Module 1: First-Launch Web Installer & Environment Management
* Automatically detects if `.env` exists and if database tables are provisioned.
* If unconfigured, redirects to a sleek, frosted glass **Setup Wizard**:
  * Step 1: System requirements & PHP extension check (`pdo_sqlite`, `pdo_mysql`, etc.).
  * Step 2: Database selection (SQLite for zero-config file storage, or MySQL with custom host, port, DB name, non-root username, and password).
  * Step 3: School & Teacher Profile setup (Teacher Name, Email, Password, School Name).
  * Step 4: Automatic `.env` generation and database migration/seeding.

### Module 2: Glassmorphism Design System & Mobile-First UX
* **Theme Engine:** Customizable with manual and automatic **Dark/Light Mode toggle** stored in local storage and user session.
* **Mobile-First Layout:**
  * Fixed bottom navigation bar on mobile devices (Dashboard, Call Log, Attendance, Students, Marks).
  * Responsive sidebar drawer on desktop.
  * Big touch-friendly buttons (minimum 44x44px touch targets).
  * Frosted glass cards (`backdrop-blur-md`, subtle glowing borders, refined shadows).

### Module 3: Student Directory & Universal Student-Admin ID
* Support for multiple ID formats: alphanumeric (`ADM-2026-001`), numeric (`1042`), or custom school registration numbers.
* Multi-class structure:
  * Primary Class (~30+ students) where teacher is Class Teacher.
  * 4 Other Classes with flexible subject assignment (supports multiple subjects in the same class, or single subject per class).
* **Universal Search & 360° Dossier Pane:**
  * Quick search bar with instant typeahead.
  * Dossier displays student bio, guardian contacts, direct WhatsApp & Call buttons, complete parent call timeline, attendance percentage, and exam grade history.

### Module 4: Parent Communication Journal (Priority Core)
* **Call Categories with Distinct Visual Badging:**
  * 🔴 **Absenteeism / Truancy** (Crimson)
  * 🟠 **Discipline / Misbehavior** (Orange)
  * 🟡 **Homework / Classwork** (Amber)
  * 🔵 **Exam / Test Marks** (Blue)
  * 🟢 **Health / Medical** (Emerald)
  * 🟣 **Appreciation / Praise** (Purple)
  * ⚪ **General / Administrative** (Slate)
* **Direct WhatsApp Integration:**
  * One-tap `https://wa.me/<phone>?text=...` button to open WhatsApp chat with pre-filled greeting and student context to show conversation and messages.
* **Call Outcome & Response Logging:**
  * Contact Person, Relationship (Mother, Father, Guardian), Dialed Number, Date & Time.
  * Teacher discussion notes.
  * Parent response / commitment verbatim notes.
  * Follow-up date and status toggle (Completed / Pending).
* **Reporting & Exporting:**
  * **Print-Ready Clean View & PDF:** Formatted for PTA meetings or submission to School Principal.
  * **Excel / CSV Export:** 1-click download of filtered records.
  * Per-student complete chronological history or per-class batch communication sheet.

### Module 5: Attendance Register & Consecutive Absence Detection
* Fast 1-tap mobile roll call: All students default to "Present".
* Single tap toggles to Absent (`A`), Late (`L`), or Excused (`E`).
* **Chronic Absence Flag:** When a student incurs **3 consecutive absences**, an urgent badge appears with a 1-tap shortcut: "*Log Parent Call for [Student]*".

### Module 6: Examination Results & Gradebook
* Raw numerical mark entry for tests, quizzes, and exams.
* Dynamic calculation of:
  * Percentage = `(Marks Obtained / Max Marks) * 100`
  * Letter Grade scale (`90-100% A+`, `80-89% A`, `70-79% B`, `60-69% C`, `50-59% D`, `<50% F`).
* Student Report Card generator (print-ready) and class ranking summary.

---

## 3. Database Architecture (Dual SQLite & MySQL Support)

The database layer utilizes PHP PDO with ANSI-compliant SQL so that switching between SQLite and MySQL only requires changing `DB_CONNECTION` in `.env`.
Standard non-root MySQL users with `SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX` privileges are fully supported.
