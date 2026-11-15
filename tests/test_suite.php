<?php

declare(strict_types=1);

namespace Tests;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Router;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\ParentCallLog;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Plan;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\Reminder;
use App\Models\Announcement;
use App\Utils\GradeCalculator;
use App\Utils\ImportParser;
use App\Utils\QrCodeGenerator;
use App\Utils\Mailer;
use App\Services\UpdateService;

$baseDir = dirname(__DIR__);
Config::load($baseDir);

// Use isolated test SQLite database for test suite runs
$testDbPath = $baseDir . '/database/test.sqlite';
if (file_exists($testDbPath)) {
    @unlink($testDbPath);
}
Config::set('DB_CONNECTION', 'sqlite');
Config::set('DB_DATABASE', 'database/test.sqlite');
Database::resetInstance();

$totalTests = 0;
$passedTests = 0;
$failedTests = [];

function it(string $description, callable $fn): void
{
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    try {
        $fn();
        $passedTests++;
        echo "  \033[32m✔\033[0m {$description}\n";
    } catch (\Throwable $e) {
        $failedTests[] = "{$description}: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
        echo "  \033[31m✘\033[0m {$description} - FAILED: {$e->getMessage()}\n";
    }
}

echo "\n\033[1;34m=== RUNNING CLASS AID COMPREHENSIVE TEST SUITE ===\033[0m\n\n";

// -------------------------------------------------------------
// Group 1: Core Architecture & Environment
// -------------------------------------------------------------
echo "\033[1m[1. Core Architecture & Database Layer]\033[0m\n";

it('Config loader correctly resolves base settings', function () use ($baseDir) {
    if (Config::get('BASE_DIR') !== $baseDir) {
        throw new \Exception("BASE_DIR mismatch");
    }
    if (Config::get('APP_NAME') !== 'Class Aid') {
        throw new \Exception("APP_NAME mismatch");
    }
});

it('Database migration creates all schema tables cleanly in SQLite', function () {
    Database::runMigrations('sqlite');
    $pdo = Database::getConnection();
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(\PDO::FETCH_COLUMN);
    $requiredTables = [
        'users', 'classes', 'subjects', 'students', 'parent_call_logs',
        'attendance', 'exams', 'marks', 'plans', 'subscriptions',
        'announcements', 'reminders', 'payment_methods', 'invoices'
    ];
    foreach ($requiredTables as $t) {
        if (!in_array($t, $tables, true)) {
            throw new \Exception("Missing database table: {$t}");
        }
    }
});

it('CSRF generator produces 64-char hex tokens and validates matching tokens', function () {
    $token = Csrf::generateToken();
    if (strlen($token) !== 64) {
        throw new \Exception("Token length is not 64");
    }
    if (!Csrf::validate($token)) {
        throw new \Exception("Valid token failed validation");
    }
    if (Csrf::validate('invalid_token_1234567890')) {
        throw new \Exception("Invalid token was mistakenly validated");
    }
});

it('Request isJson detects JSON and AJAX headers correctly', function () {
    $_SERVER['HTTP_ACCEPT'] = 'application/json, text/plain';
    $req = new Request();
    if (!$req->isJson() || !$req->isAjax()) {
        throw new \Exception("isJson and isAjax should be true for application/json accept header");
    }

    $_SERVER['HTTP_ACCEPT'] = 'text/html';
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    $req2 = new Request();
    if (!$req2->isJson() || !$req2->isAjax()) {
        throw new \Exception("isJson and isAjax should be true for XMLHttpRequest");
    }

    unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    $req3 = new Request();
    if ($req3->isJson() || $req3->isAjax()) {
        throw new \Exception("isJson and isAjax should be false for standard HTML request");
    }
});

// -------------------------------------------------------------
// Group 2: SaaS Plans & Payment Gateways
// -------------------------------------------------------------
echo "\n\033[1m[2. SaaS Plans & Dynamic Payment Gateways]\033[0m\n";

it('Plan model seeds default plans and retrieves active tiers', function () {
    Plan::ensureTableAndSeed();
    $plans = Plan::getAllActive();
    if (count($plans) < 5) {
        throw new \Exception("Expected at least 5 active plans, got " . count($plans));
    }
    $trial = Plan::findByCode('trial');
    if (!$trial || (float)$trial['price'] !== 0.0) {
        throw new \Exception("Trial plan missing or price not 0.00");
    }
    $annual = Plan::findByCode('annual');
    if (!$annual || empty($annual['popular'])) {
        throw new \Exception("Annual plan should be marked popular");
    }
});

it('PaymentMethod model auto-seeds default gateways and computes QR URLs', function () {
    PaymentMethod::ensureTableAndSeed();
    $methods = PaymentMethod::getAllActive();
    if (count($methods) < 3) {
        throw new \Exception("Expected at least 3 active payment methods, got " . count($methods));
    }
    $ep = PaymentMethod::findByCode('easypaisa');
    if (!$ep || empty($ep['qr_url'])) {
        throw new \Exception("EasyPaisa method missing or qr_url not generated");
    }
    $bank = PaymentMethod::findByCode('bank');
    if (!$bank || empty($bank['instructions'])) {
        throw new \Exception("Bank transfer method missing instructions");
    }
});

it('QrCodeGenerator generates deterministic vector SVG QR without external network', function () {
    $svg = QrCodeGenerator::generateVectorSvg('Test Payload');
    if (!str_contains($svg, '<svg') || !str_contains($svg, '</svg>')) {
        throw new \Exception("Generated vector SVG is invalid");
    }
});

// -------------------------------------------------------------
// Group 3: User Accounts & Authentication
// -------------------------------------------------------------
echo "\n\033[1m[3. User Authentication & Password Resets]\033[0m\n";

$testAdminId = 0;
$testTeacherId = 0;

it('Creates Super Admin and Teacher accounts with password hashing', function () use (&$testAdminId, &$testTeacherId) {
    // Unique emails for test run
    $adminEmail = 'admin_' . time() . '@classaid.test';
    $teacherEmail = 'teacher_' . time() . '@classaid.test';

    $testAdminId = User::create([
        'name' => 'Master Super Admin',
        'email' => $adminEmail,
        'password' => 'AdminPass123!',
        'role' => 'super_admin',
        'school_name' => 'Class Aid Central',
        'phone' => '0300-9999999',
        'status' => 'active',
    ]);
    if ($testAdminId <= 0) {
        throw new \Exception("Failed to create admin");
    }

    $testTeacherId = User::create([
        'name' => 'Professor David Clark',
        'email' => $teacherEmail,
        'password' => 'TeacherPass123!',
        'role' => 'teacher',
        'school_name' => 'St. Jude Academy',
        'phone' => '0300-8888888',
        'status' => 'active',
    ]);
    if ($testTeacherId <= 0) {
        throw new \Exception("Failed to create teacher");
    }

    $foundAdmin = User::findByEmail($adminEmail);
    if (!$foundAdmin || !User::verifyPassword('AdminPass123!', $foundAdmin['password_hash'])) {
        throw new \Exception("Admin password verification failed");
    }
});

it('Generates and verifies password reset tokens', function () use (&$testTeacherId) {
    $token = User::generateResetToken($testTeacherId);
    if (empty($token) || strlen($token) !== 64) {
        throw new \Exception("Invalid reset token generated");
    }

    $found = User::findByResetToken($token);
    if (!$found || (int)$found['id'] !== $testTeacherId) {
        throw new \Exception("User not found by valid reset token");
    }

    $resetOk = User::resetPasswordWithToken($token, 'NewSecurePass456!');
    if (!$resetOk) {
        throw new \Exception("Failed to reset password with token");
    }

    // Token should now be consumed
    if (User::findByResetToken($token) !== null) {
        throw new \Exception("Token was not consumed after reset");
    }

    $updatedHash = User::getPasswordHash($testTeacherId);
    if (!User::verifyPassword('NewSecurePass456!', $updatedHash)) {
        throw new \Exception("New password does not verify");
    }
});

// -------------------------------------------------------------
// Group 4: Classroom, Students & Dual DB Search
// -------------------------------------------------------------
echo "\n\033[1m[4. Classroom Roster, Students & Search]\033[0m\n";

$testHomeClassId = 0;
$testStudentId = 0;

it('Creates classes and multiple subjects per class', function () use ($testTeacherId, &$testHomeClassId) {
    $testHomeClassId = SchoolClass::create([
        'teacher_id' => $testTeacherId,
        'name' => 'Grade 10',
        'section' => 'A',
        'academic_year' => '2026-2027',
        'is_home_class' => 1,
    ]);
    if ($testHomeClassId <= 0) {
        throw new \Exception("Failed to create home class");
    }

    $mathId = Subject::create(['class_id' => $testHomeClassId, 'name' => 'Mathematics', 'code' => 'MTH-10']);
    $csId = Subject::create(['class_id' => $testHomeClassId, 'name' => 'Computer Science', 'code' => 'CS-10']);
    if ($mathId <= 0 || $csId <= 0) {
        throw new \Exception("Failed to create subjects");
    }

    $subjects = Subject::getByClass($testHomeClassId);
    if (count($subjects) !== 2) {
        throw new \Exception("Expected 2 subjects in class, got " . count($subjects));
    }
});

it('Creates student with universal alphanumeric Admin ID and retrieves dossier', function () use (&$testHomeClassId, &$testStudentId) {
    $adminId = 'ADM-' . time() . '-001';
    $testStudentId = Student::create([
        'student_admin_id' => $adminId,
        'class_id' => $testHomeClassId,
        'first_name' => 'Zainab',
        'last_name' => 'Ahmed',
        'gender' => 'Female',
        'dob' => '2010-06-15',
        'primary_guardian_name' => 'Mrs. Fatima Ahmed',
        'primary_guardian_relation' => 'Mother',
        'primary_phone' => '+1 555-0199',
        'secondary_phone' => '+1 555-0198',
        'guardian_email' => 'fatima.ahmed@example.com',
        'status' => 'active',
    ]);
    if ($testStudentId <= 0) {
        throw new \Exception("Failed to create student");
    }

    $stu = Student::findById($testStudentId);
    if (!$stu || $stu['first_name'] !== 'Zainab' || $stu['class_name'] !== 'Grade 10') {
        throw new \Exception("Student dossier lookup failed");
    }
});

it('Dual-DB student search matches by First Name, Last Name, and Full Name', function () use (&$testStudentId) {
    $byFirst = Student::search('Zainab');
    if (empty($byFirst)) {
        throw new \Exception("Search by first name failed");
    }

    $byLast = Student::search('Ahmed');
    if (empty($byLast)) {
        throw new \Exception("Search by last name failed");
    }

    $byFull = Student::search('Zainab Ahmed');
    if (empty($byFull)) {
        throw new \Exception("Search by full name concat failed");
    }
});

it('Supports editing class details, deleting subjects, and removing classes cleanly', function () use ($testTeacherId) {
    // 1. Create temporary class for editing and removal test
    $tempClassId = SchoolClass::create([
        'teacher_id' => $testTeacherId,
        'name' => 'Grade 8',
        'section' => 'C',
        'academic_year' => '2026-2027',
        'is_home_class' => 0,
    ]);
    if ($tempClassId <= 0) {
        throw new \Exception("Failed to create temporary class");
    }

    // 2. Add subject to temporary class
    $tempSubId = Subject::create([
        'class_id' => $tempClassId,
        'name' => 'History',
        'code' => 'HIS-08'
    ]);
    if ($tempSubId <= 0) {
        throw new \Exception("Failed to create subject in temp class");
    }

    // 3. Edit class details
    $updated = SchoolClass::update($tempClassId, [
        'name' => 'Grade 8 Advanced',
        'section' => 'A1',
        'academic_year' => '2026-2027',
        'is_home_class' => 0,
    ]);
    if (!$updated) {
        throw new \Exception("Failed to update class details");
    }

    $verified = SchoolClass::findById($tempClassId);
    if (!$verified || $verified['name'] !== 'Grade 8 Advanced' || $verified['section'] !== 'A1') {
        throw new \Exception("Class update verification mismatch");
    }

    // 4. Delete subject
    $subDeleted = Subject::delete($tempSubId);
    if (!$subDeleted || Subject::findById($tempSubId) !== null) {
        throw new \Exception("Failed to delete subject");
    }

    // 5. Delete class
    $classDeleted = SchoolClass::delete($tempClassId);
    if (!$classDeleted || SchoolClass::findById($tempClassId) !== null) {
        throw new \Exception("Failed to delete class");
    }
});

// -------------------------------------------------------------
// Group 5: Attendance & Consecutive Absence Alerts
// -------------------------------------------------------------
echo "\n\033[1m[5. Attendance Roll Call & Consecutive Absence Flags]\033[0m\n";

it('Records daily attendance and flags 3 consecutive absences correctly', function () use ($testStudentId, $testHomeClassId) {
    $d1 = date('Y-m-d', strtotime('-3 days'));
    $d2 = date('Y-m-d', strtotime('-2 days'));
    $d3 = date('Y-m-d', strtotime('-1 days'));

    Attendance::record($testStudentId, $testHomeClassId, $d1, 'A', 'Unexcused flu');
    Attendance::record($testStudentId, $testHomeClassId, $d2, 'A', 'Fever continued');
    Attendance::record($testStudentId, $testHomeClassId, $d3, 'A', 'Severe fever');

    $consecutive = Student::getConsecutiveAbsences($testStudentId);
    if ($consecutive !== 3) {
        throw new \Exception("Expected consecutive absences to be 3, got {$consecutive}");
    }

    // Now record Present today - consecutive absences counter must immediately reset to 0
    $dToday = date('Y-m-d');
    Attendance::record($testStudentId, $testHomeClassId, $dToday, 'P', 'Returned to class');
    $consecutiveReset = Student::getConsecutiveAbsences($testStudentId);
    if ($consecutiveReset !== 0) {
        throw new \Exception("Expected consecutive absences to reset to 0 after Present, got {$consecutiveReset}");
    }
});

// -------------------------------------------------------------
// Group 6: Parent Communication Journal
// -------------------------------------------------------------
echo "\n\033[1m[6. Parent Communication Journal & Auditing]\033[0m\n";

it('Logs parent interaction with categories and follow-up commitments', function () use ($testTeacherId, $testStudentId) {
    $callId = ParentCallLog::create([
        'student_id' => $testStudentId,
        'teacher_id' => $testTeacherId,
        'contact_person_name' => 'Mrs. Fatima Ahmed',
        'relationship' => 'Mother',
        'phone_dialed' => '+1 555-0199',
        'call_timestamp' => date('Y-m-d H:i:s'),
        'call_type' => 'Phone Call',
        'call_category' => 'Absenteeism',
        'discussion_summary' => 'Informed parent regarding 3-day absence due to fever.',
        'parent_response' => 'Mother promised to send physician medical certificate tomorrow.',
        'follow_up_required' => 1,
        'follow_up_date' => date('Y-m-d', strtotime('+1 day')),
        'status' => 'Pending Follow-up',
    ]);
    if ($callId <= 0) {
        throw new \Exception("Failed to log parent call");
    }

    $calls = ParentCallLog::getByStudent($testStudentId);
    if (count($calls) < 1) {
        throw new \Exception("Failed to retrieve student calls");
    }

    $pending = ParentCallLog::getPendingFollowups($testTeacherId);
    if (count($pending) < 1) {
        throw new \Exception("Pending follow-up was not found in teacher pending list");
    }

    // Toggle status to Resolved
    ParentCallLog::updateStatus($callId, 'Resolved');
    $updated = ParentCallLog::findById($callId);
    if ($updated['status'] !== 'Resolved') {
        throw new \Exception("Status update to Resolved failed");
    }
});

// -------------------------------------------------------------
// Group 7: Gradebook & Automated Letter Grades
// -------------------------------------------------------------
echo "\n\033[1m[7. Gradebook & Automated Grade Calculation]\033[0m\n";

it('GradeCalculator computes exact percentages and standardized letter grades', function () {
    $aPlus = GradeCalculator::calculate(95.0, 100.0, 40.0);
    if ($aPlus['grade'] !== 'A+' || $aPlus['percentage'] !== 95.0 || !$aPlus['is_passed']) {
        throw new \Exception("95% should be A+");
    }

    $bGrade = GradeCalculator::calculate(74.0, 100.0, 40.0);
    if ($bGrade['grade'] !== 'B' || $bGrade['percentage'] !== 74.0 || !$bGrade['is_passed']) {
        throw new \Exception("74% should be B");
    }

    $fGrade = GradeCalculator::calculate(35.0, 100.0, 40.0);
    if ($fGrade['grade'] !== 'F' || $fGrade['is_passed']) {
        throw new \Exception("35% should be F and fail");
    }
});

it('Records exam batch marks and produces student academic report cards', function () use ($testHomeClassId, $testStudentId) {
    $subjects = Subject::getByClass($testHomeClassId);
    $subId = (int)$subjects[0]['id'];

    $examId = Exam::create([
        'subject_id' => $subId,
        'title' => 'Term 1 Midterm Exam',
        'term' => 'Term 1',
        'max_marks' => 100.0,
        'pass_marks' => 40.0,
        'exam_date' => date('Y-m-d'),
    ]);
    if ($examId <= 0) {
        throw new \Exception("Failed to create exam");
    }

    Mark::recordBatch($examId, 100.0, 40.0, [
        $testStudentId => ['score' => 88.0, 'remarks' => 'Superb analysis in geometry.']
    ]);

    $studentMarks = Mark::getByStudent($testStudentId);
    if (count($studentMarks) < 1) {
        throw new \Exception("Student marks record missing");
    }
    if ($studentMarks[0]['grade'] !== 'A') {
        throw new \Exception("Letter grade should be A, got " . $studentMarks[0]['grade']);
    }
});

// -------------------------------------------------------------
// Group 8: Smart Import Parser (CSV & OCR)
// -------------------------------------------------------------
echo "\n\033[1m[8. Smart Import Parser (CSV & OCR Text)]\033[0m\n";

it('ImportParser detects CSV columns and parses student rosters', function () {
    $csv = "student_admin_id,first_name,last_name,gender,primary_phone,primary_guardian_name\n" .
           "ADM-2026-101,Arthur,Dent,Male,555-0101,Ford Dent\n" .
           "ADM-2026-102,Tricia,McMillan,Female,555-0102,Zaphod McMillan\n";

    $students = ImportParser::parseStudents($csv, 1);
    if (count($students) !== 2) {
        throw new \Exception("Expected 2 parsed students, got " . count($students));
    }
    if ($students[0]['student_admin_id'] !== 'ADM-2026-101' || $students[0]['first_name'] !== 'Arthur') {
        throw new \Exception("First parsed student data incorrect");
    }
});

it('ImportParser parses OCR text mark sheets and matches enrolled students', function () use ($testStudentId) {
    $stu = Student::findById($testStudentId);
    $adminId = $stu['student_admin_id'];

    $rawOcrText = "Midterm Results Grade 10\n" .
                  "{$adminId}   Zainab Ahmed   92.5   Excellent algebra work\n";

    $parsed = ImportParser::parseMarks($rawOcrText, 1, [$stu], 100.0, 40.0);
    if (count($parsed) !== 1) {
        throw new \Exception("Expected 1 parsed mark row, got " . count($parsed));
    }
    if ($parsed[0]['student_id'] !== $testStudentId || (float)$parsed[0]['score'] !== 92.5) {
        throw new \Exception("OCR mark parsed incorrectly");
    }
});

// -------------------------------------------------------------
// Group 9: SaaS Subscriptions, Invoicing & Verification
// -------------------------------------------------------------
echo "\n\033[1m[9. SaaS Billing, Subscriptions & Invoice Verification]\033[0m\n";

it('Handles subscription creation, trial mode unlocking, and Super Admin payment approval', function () use ($testTeacherId) {
    $subId = Subscription::create([
        'user_id' => $testTeacherId,
        'plan_code' => 'trial',
        'plan_name' => '14-Day Free Trial (Pending Annual Plan)',
        'billing_cycle' => 'trial',
        'price' => 0.00,
        'status' => 'trial',
        'payment_method' => 'easypaisa',
        'payment_reference' => 'TRX-EP-888999',
        'pending_plan' => 'annual',
        'pending_price' => 89.99,
        'notes' => 'Registered via EasyPaisa',
    ]);
    if ($subId <= 0) {
        throw new \Exception("Failed to create subscription");
    }

    // Teacher should be unlocked in trial mode
    if (!Subscription::isUserUnlocked($testTeacherId)) {
        throw new \Exception("Teacher should be unlocked during trial");
    }

    // Super Admin approves payment
    $approved = Subscription::approvePayment($subId, 'Payment verified via EasyPaisa TRX: TRX-EP-888999');
    if (!$approved) {
        throw new \Exception("Super Admin approval failed");
    }

    $active = Subscription::getActiveForUser($testTeacherId);
    if ($active['plan_code'] !== 'annual' || $active['status'] !== 'active') {
        throw new \Exception("Subscription was not switched to active annual plan");
    }
});

it('Generates formatted invoice and records payment proof', function () use ($testTeacherId) {
    $invId = Invoice::create([
        'user_id' => $testTeacherId,
        'plan_code' => 'annual',
        'plan_name' => 'Annual Professional Plan',
        'billing_cycle' => 'annual',
        'amount' => 89.99,
        'payment_method' => 'easypaisa',
        'payment_reference' => 'TRX-EP-888999',
        'status' => 'paid',
        'paid_at' => date('Y-m-d H:i:s'),
    ]);
    if ($invId <= 0) {
        throw new \Exception("Failed to generate invoice");
    }

    $inv = Invoice::findById($invId);
    if (!$inv || !str_starts_with($inv['invoice_number'], 'INV-')) {
        throw new \Exception("Invoice number format invalid");
    }
});

// -------------------------------------------------------------
// Group 10: Reminders & Broadcast Announcements
// -------------------------------------------------------------
echo "\n\033[1m[10. Reminders, Broadcast Announcements & Notification Badges]\033[0m\n";

it('Creates, lists and toggles user reminders', function () use ($testTeacherId) {
    $remId = Reminder::create([
        'user_id' => $testTeacherId,
        'title' => 'Submit Term 1 Final Marks to Principal',
        'due_date' => date('Y-m-d', strtotime('+3 days')),
        'priority' => 'high',
        'category' => 'exam',
    ]);
    if ($remId <= 0) {
        throw new \Exception("Failed to create reminder");
    }

    $pendingCount = Reminder::countPending($testTeacherId);
    if ($pendingCount < 1) {
        throw new \Exception("Pending reminders count should be >= 1");
    }

    Reminder::toggleStatus($remId, $testTeacherId);
    $reminders = Reminder::getByUser($testTeacherId);
    $found = array_values(array_filter($reminders, fn($r) => (int)$r['id'] === $remId));
    if (empty($found) || $found[0]['status'] !== 'completed') {
        throw new \Exception("Reminder toggle to completed failed");
    }
});

it('Dispatches announcements with audience filtering (all vs teacher)', function () use ($testAdminId) {
    $annId = Announcement::create([
        'title' => 'Term 2 Academic Calendar Published',
        'content' => 'Please download the term 2 academic schedule from your dashboard.',
        'type' => 'info',
        'target_role' => 'teacher',
        'is_published' => 1,
        'created_by' => $testAdminId,
    ]);
    if ($annId <= 0) {
        throw new \Exception("Failed to create announcement");
    }

    $teacherAnnouncements = Announcement::getAllPublished('teacher');
    $found = array_filter($teacherAnnouncements, fn($a) => (int)$a['id'] === $annId);
    if (empty($found)) {
        throw new \Exception("Announcement not visible to teacher role");
    }
});

// -------------------------------------------------------------
// Group 12: Dynamic Domains, Attendance Dashboard & SaaS Lifecycle
// -------------------------------------------------------------
echo "\n\033[1m[12. Dynamic Domains, Attendance Dashboard & SaaS Lifecycles]\033[0m\n";

it('Request resolves base URL dynamically across custom domains and reverse proxies', function () {
    // 1. Standard custom domain with HTTPS
    $_SERVER['HTTP_HOST'] = 'portal.institution.edu';
    $_SERVER['HTTPS'] = 'on';
    unset($_SERVER['HTTP_X_FORWARDED_PROTO'], $_SERVER['SERVER_PORT']);
    $req1 = new Request();
    if ($req1->getBaseUrl() !== 'https://portal.institution.edu') {
        throw new \Exception("Expected https://portal.institution.edu, got " . $req1->getBaseUrl());
    }

    // 2. Reverse proxy with X-Forwarded-Proto
    $_SERVER['HTTP_HOST'] = 'classaid.myschool.com:8443';
    unset($_SERVER['HTTPS']);
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
    $req2 = new Request();
    if ($req2->getBaseUrl() !== 'https://classaid.myschool.com:8443') {
        throw new \Exception("Proxy HTTPS detection failed: " . $req2->getBaseUrl());
    }

    // 3. Clean up
    unset($_SERVER['HTTP_HOST'], $_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO']);
});

it('Attendance getCustomReport generates scoped analytics for whole class, single student, and multi-selection', function () use ($testHomeClassId, $testStudentId) {
    // Record another student in this class
    $secondStuId = Student::create([
        'student_admin_id' => 'ADM-RPT-002',
        'class_id' => $testHomeClassId,
        'first_name' => 'Tariq',
        'last_name' => 'Mahmood',
        'primary_guardian_name' => 'Mr. Mahmood',
        'primary_phone' => '+1 555-0202',
    ]);

    $d1 = date('Y-m-d', strtotime('-5 days'));
    $d2 = date('Y-m-d', strtotime('-4 days'));

    Attendance::record($testStudentId, $testHomeClassId, $d1, 'P');
    Attendance::record($secondStuId, $testHomeClassId, $d1, 'A');

    Attendance::record($testStudentId, $testHomeClassId, $d2, 'P');
    Attendance::record($secondStuId, $testHomeClassId, $d2, 'P');

    $from = date('Y-m-d', strtotime('-7 days'));
    $to = date('Y-m-d');

    // 1. Whole class report
    $fullReport = Attendance::getCustomReport($testHomeClassId, $from, $to, []);
    if (count($fullReport['students']) < 2) {
        throw new \Exception("Expected at least 2 students in full report");
    }
    if ($fullReport['summary']['total_sessions_taken'] < 2) {
        throw new \Exception("Expected at least 2 sessions taken");
    }

    // 2. Single student report
    $singleReport = Attendance::getCustomReport($testHomeClassId, $from, $to, [$testStudentId]);
    if (count($singleReport['students']) !== 1 || (int)$singleReport['students'][0]['id'] !== $testStudentId) {
        throw new \Exception("Single student filter mismatch");
    }
    if ($singleReport['students'][0]['present_count'] < 2) {
        throw new \Exception("Student should have at least 2 present marks");
    }

    // 3. Multi-select report
    $multiReport = Attendance::getCustomReport($testHomeClassId, $from, $to, [$testStudentId, $secondStuId]);
    if (count($multiReport['students']) !== 2) {
        throw new \Exception("Multi-selection should include both students");
    }
});

it('Auto plan extension extends active plans from existing expiry date and reactivates suspended users', function () {
    // Create teacher
    $subTeacherId = User::create([
        'name' => 'Renewal Teacher',
        'email' => 'renewal_' . time() . '@school.test',
        'password' => 'Password123!',
        'role' => 'teacher',
        'status' => 'suspended', // Suspended account
    ]);

    // Active subscription expiring in 15 days
    $initialExpiry = date('Y-m-d H:i:s', strtotime('+15 days'));
    $subId = Subscription::create([
        'user_id' => $subTeacherId,
        'plan_code' => 'monthly',
        'price' => 9.99,
        'status' => 'active',
        'starts_at' => date('Y-m-d H:i:s'),
        'expires_at' => $initialExpiry,
    ]);

    // Create and mark paid invoice for 30-day monthly plan
    $invId = Invoice::create([
        'user_id' => $subTeacherId,
        'subscription_id' => $subId,
        'plan_code' => 'monthly',
        'amount' => 9.99,
        'status' => 'unpaid',
    ]);

    $paid = Invoice::markPaid($invId, 'TRX-PAID-001');
    if (!$paid) {
        throw new \Exception("Failed to mark invoice as paid");
    }

    $updatedSub = Subscription::findById($subId);
    $expectedMinExpiry = strtotime('+44 days'); // 15 days + 30 days = 45 days
    if (strtotime($updatedSub['expires_at']) < $expectedMinExpiry) {
        throw new \Exception("Auto plan extension did not extend from existing future expiry date");
    }

    $updatedUser = User::findById($subTeacherId);
    if ($updatedUser['status'] !== 'active') {
        throw new \Exception("Suspended user was not reactivated upon invoice payment");
    }
});

it('Auto-generates renewal invoices 7 days before expiry and suspends past grace period without removing data', function () {
    $teacherId = User::create([
        'name' => 'Expiring Teacher',
        'email' => 'expiring_' . time() . '@school.test',
        'password' => 'Password123!',
        'role' => 'teacher',
        'status' => 'active',
    ]);

    // Subscription expiring in 5 days (within 7-day pre-expiry window)
    $expiringSoon = date('Y-m-d H:i:s', strtotime('+5 days'));
    $subId = Subscription::create([
        'user_id' => $teacherId,
        'plan_code' => 'annual',
        'price' => 89.99,
        'status' => 'active',
        'starts_at' => date('Y-m-d H:i:s', strtotime('-360 days')),
        'expires_at' => $expiringSoon,
    ]);

    // Trigger renewal engine
    Subscription::checkAndProcessRenewals($teacherId);

    // Verify invoice was generated
    $unpaid = Invoice::getUnpaidForSubscription($subId);
    if (!$unpaid) {
        throw new \Exception("Renewal invoice was not automatically generated 7 days before expiry");
    }
    // Verify due date has 7 days grace period after expiry
    $expectedDueDate = date('Y-m-d', strtotime($expiringSoon . ' +7 days'));
    if ($unpaid['due_date'] !== $expectedDueDate) {
        throw new \Exception("Invoice due date should be 7 days grace period after expiry date: expected {$expectedDueDate}, got {$unpaid['due_date']}");
    }

    // Now test past grace period (expired 10 days ago, past 7 days grace)
    $pastGraceExpiry = date('Y-m-d H:i:s', strtotime('-10 days'));
    Subscription::update($subId, [
        'expires_at' => $pastGraceExpiry,
        'status' => 'active',
    ]);

    Subscription::checkAndProcessRenewals($teacherId);

    // Verify account is suspended WITHOUT deleting data
    $suspendedUser = User::findById($teacherId);
    if ($suspendedUser['status'] !== 'suspended') {
        throw new \Exception("User should be suspended past 7 days grace period");
    }

    // Verify user record and subscription still exist intact
    if (Subscription::findById($subId) === null) {
        throw new \Exception("Subscription must not be deleted upon suspension");
    }
});

// -------------------------------------------------------------
// Group 11: In-Place Update Module & GitHub Release Engine
// -------------------------------------------------------------
echo "\n\033[1m[11. In-Place Update Module & GitHub Release Engine]\033[0m\n";

it('Protected paths filter strictly safeguards .env, database, and uploads', function () {
    if (!UpdateService::isProtectedPath('.env')) {
        throw new \Exception(".env must be marked as protected");
    }
    if (!UpdateService::isProtectedPath('database/database.sqlite')) {
        throw new \Exception("database.sqlite must be protected");
    }
    if (!UpdateService::isProtectedPath('public/uploads/avatars/test.jpg')) {
        throw new \Exception("User uploads must be protected");
    }
    if (!UpdateService::isProtectedPath('storage/backups/snapshot.zip')) {
        throw new \Exception("Storage files must be protected");
    }
    if (UpdateService::isProtectedPath('src/Core/Config.php')) {
        throw new \Exception("Core source code should not be protected from update");
    }
    if (UpdateService::isProtectedPath('views/dashboard/index.php')) {
        throw new \Exception("Views should not be protected from update");
    }
});

it('Checks for GitHub updates with mock payload and detects new versions', function () {
    // Ensure version is 1.0.0 for deterministic test
    $baseDir = UpdateService::getBaseDir();
    @file_put_contents($baseDir . '/version.json', json_encode([
        'version' => '1.0.0',
        'release_name' => 'Class Aid v1.0.0 (Liquid Glass)',
        'release_date' => '2026-11-15',
        'commit' => 'mock123',
        'channel' => 'stable',
        'github_repo' => 'Hasnain1385/Class_Aid',
        'github_branch' => 'main'
    ], JSON_PRETTY_PRINT));
    Config::set('APP_VERSION', '1.0.0');

    // Inject mock HTTP response
    UpdateService::$mockHttpClient = function (string $url, array $headers): array {
        if (str_contains($url, '/releases/latest')) {
            return [
                'status' => 200,
                'body' => json_encode([
                    'tag_name' => 'v1.2.0',
                    'name' => 'Release 1.2.0 - Next Gen Enhancements',
                    'body' => 'Changelog: New updates and performance fixes.',
                    'published_at' => '2026-11-15T15:00:00Z',
                    'zipball_url' => 'https://example.com/download/v1.2.0.zip',
                    'html_url' => 'https://github.com/class-aid/class-aid/releases/tag/v1.2.0',
                ]),
                'headers' => [],
            ];
        }
        return ['status' => 404, 'body' => '', 'headers' => []];
    };

    $check = UpdateService::checkForUpdates(true);
    UpdateService::$mockHttpClient = null; // Reset

    if (!$check['has_update']) {
        throw new \Exception("UpdateService should have detected new update (v1.2.0 > current)");
    }
    if ($check['latest_version'] !== '1.2.0') {
        throw new \Exception("Expected latest version 1.2.0, got {$check['latest_version']}");
    }
    if ($check['download_url'] !== 'https://example.com/download/v1.2.0.zip') {
        throw new \Exception("Download URL mismatch");
    }
});

it('Generates safety backup archive and registers snapshot metadata', function () {
    $bk = UpdateService::createBackup('Test automated suite snapshot');
    if (empty($bk['filename']) || !file_exists(UpdateService::getBaseDir() . '/storage/backups/' . $bk['filename'])) {
        throw new \Exception("Backup zip was not written to storage/backups");
    }
    if ($bk['size'] <= 0) {
        throw new \Exception("Backup size is 0 bytes");
    }

    $allBackups = UpdateService::getBackups();
    $found = array_filter($allBackups, fn($b) => $b['filename'] === $bk['filename']);
    if (empty($found)) {
        throw new \Exception("Backup metadata not recorded in backups.json");
    }
});

it('Performs in-place zip extraction, preserving protected files and updating version', function () {
    $baseDir = UpdateService::getBaseDir();
    $tempZipPath = $baseDir . '/storage/updates/test_package_' . time() . '.zip';

    // Backup original version.json
    $originalVersionContent = file_get_contents($baseDir . '/version.json');

    // Create a mock update zip package with a top-level wrapper directory (simulating GitHub zipball)
    $zip = new \ZipArchive();
    $zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
    $wrapper = 'class-aid-1.2.0';
    $zip->addEmptyDir($wrapper);
    $zip->addFromString("{$wrapper}/version.json", json_encode([
        'version' => '1.2.0',
        'release_name' => 'v1.2.0 Mock Update',
        'release_date' => '2026-11-15',
        'commit' => 'mock123',
        'channel' => 'stable'
    ]));
    // Add a file that tries to overwrite a protected file (.env) - must be ignored!
    $zip->addFromString("{$wrapper}/.env", 'MALICIOUS_OVERWRITE=true');
    // Add a legitimate updated file
    $zip->addFromString("{$wrapper}/src/version_test_marker.txt", 'in_place_update_verified');
    $zip->close();

    // Verify .env content before update
    $envContentBefore = file_get_contents($baseDir . '/.env');

    // Apply update using this custom package
    $result = UpdateService::applyUpdate($tempZipPath);
    if (!$result['success']) {
        throw new \Exception("applyUpdate failed: " . ($result['error'] ?? 'Unknown'));
    }

    // Verify protected .env was NOT overwritten
    $envContentAfter = file_get_contents($baseDir . '/.env');
    if ($envContentBefore !== $envContentAfter) {
        throw new \Exception("Protected .env file was modified during in-place update!");
    }

    // Verify legitimate file was extracted
    $markerFile = $baseDir . '/src/version_test_marker.txt';
    if (!file_exists($markerFile) || trim((string)file_get_contents($markerFile)) !== 'in_place_update_verified') {
        throw new \Exception("Legitimate update file was not extracted!");
    }

    // Verify version.json was updated
    $info = UpdateService::getCurrentVersionInfo();
    if ($info['version'] !== '1.2.0') {
        throw new \Exception("Expected version to be 1.2.0, got {$info['version']}");
    }

    // Clean up test marker and temp zip
    @unlink($markerFile);
    @unlink($tempZipPath);

    // Restore original version.json exactly as it was
    file_put_contents($baseDir . '/version.json', $originalVersionContent);
    Config::set('APP_VERSION', '1.0.0');
});

// -------------------------------------------------------------
// Summary
// -------------------------------------------------------------
echo "\n\033[1;34m=====================================================\033[0m\n";
echo "Total Tests: {$totalTests} | Passed: \033[32m{$passedTests}\033[0m | Failed: " . (count($failedTests) > 0 ? "\033[31m" . count($failedTests) . "\033[0m" : "\033[32m0\033[0m") . "\n";

// Clean up test database
Database::resetInstance();
if (file_exists($testDbPath)) {
    @unlink($testDbPath);
}

if (!empty($failedTests)) {
    echo "\n\033[31mFailures:\033[0m\n";
    foreach ($failedTests as $f) {
        echo " - {$f}\n";
    }
    exit(1);
} else {
    echo "\033[1;32mALL CLASS AID SUITE TESTS PASSED 100% CLEANLY!\033[0m\n";
    exit(0);
}
