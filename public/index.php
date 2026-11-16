<?php

declare(strict_types=1);

// Built-in web server static asset handler
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($requestUri !== null && $requestUri !== '/') {
    $staticFile = __DIR__ . $requestUri;
    if (file_exists($staticFile) && !is_dir($staticFile)) {
        $ext = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
        $mimes = [
            'css'   => 'text/css; charset=UTF-8',
            'js'    => 'application/javascript; charset=UTF-8',
            'json'  => 'application/json; charset=UTF-8',
            'svg'   => 'image/svg+xml',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'webp'  => 'image/webp',
            'ico'   => 'image/x-icon',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'pdf'   => 'application/pdf',
        ];
        if (isset($mimes[$ext])) {
            header('Content-Type: ' . $mimes[$ext]);
            header('Content-Length: ' . filesize($staticFile));
            header('Cache-Control: public, max-age=86400');
            readfile($staticFile);
            exit;
        }
    }
}

// Class Aid - Main Application Entry Point

$baseDir = dirname(__DIR__);

// 1. Require Composer Autoloader
require_once $baseDir . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\Session;
use App\Core\Request;
use App\Core\Router;
use App\Controllers\InstallController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\StudentController;
use App\Controllers\CallLogController;
use App\Controllers\AttendanceController;
use App\Controllers\ClassController;
use App\Controllers\ResultController;
use App\Controllers\AdminController;
use App\Controllers\SubscriptionController;
use App\Controllers\ReminderController;
use App\Controllers\ProfileController;
use App\Controllers\UpdateController;

// 2. Initialize Configuration and Session
Config::load($baseDir);
Session::start();

// First-Launch VPS Onboarding Check: redirect web visitors to setup wizard if not installed
if (!Config::isInstalled()) {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    if (!str_starts_with($path, '/install') && !str_starts_with($path, '/assets/')) {
        header('Location: /install');
        exit;
    }
}

// Maintenance Mode Check (active during in-place software updates)
$maintenanceFile = $baseDir . '/storage/maintenance.flag';
if (file_exists($maintenanceFile)) {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if (!str_starts_with($uri, '/admin/updates') && !str_starts_with($uri, '/assets/')) {
        http_response_code(503);
        header('Retry-After: 30');
        echo '<!DOCTYPE html><html lang="en" class="dark"><head><meta charset="UTF-8"><title>System Update in Progress - Class Aid</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="/assets/css/glassmorphism.css"><meta http-equiv="refresh" content="5"></head><body class="min-h-screen flex items-center justify-center p-4 text-slate-100"><div class="glass-card max-w-md w-full p-8 text-center space-y-4 border border-indigo-500/30 shadow-2xl"><div class="liquid-spinner mx-auto mb-2"></div><h1 class="text-xl font-extrabold tracking-tight">System Update in Progress</h1><p class="text-xs text-slate-400 leading-relaxed">Class Aid is currently applying an automated in-place software upgrade. This page will automatically refresh once the process completes.</p></div></body></html>';
        exit;
    }
}

// 3. Error reporting based on APP_DEBUG
if (Config::get('APP_DEBUG', false)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// 4. Initialize Router
$router = new Router();

// --- Setup / Installer Routes ---
$router->get('/install', [InstallController::class, 'showInstall']);
$router->post('/install/process', [InstallController::class, 'processInstall']);

// --- Auth Routes ---
$router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest']);
$router->get('/register', [AuthController::class, 'showRegister'], ['guest']);
$router->post('/register', [AuthController::class, 'register'], ['guest']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword'], ['guest']);
$router->post('/forgot-password', [AuthController::class, 'sendResetLink'], ['guest']);
$router->get('/reset-password', [AuthController::class, 'showResetPassword'], ['guest']);
$router->post('/reset-password', [AuthController::class, 'resetPassword'], ['guest']);
$router->get('/logout', [AuthController::class, 'logout']);

// --- Super Admin SaaS Portal Routes ---
$router->get('/admin', [AdminController::class, 'dashboard'], ['super_admin']);
$router->get('/admin/teachers', [AdminController::class, 'teachers'], ['super_admin']);
$router->get('/admin/teachers/create', [AdminController::class, 'createTeacher'], ['super_admin']);
$router->post('/admin/teachers', [AdminController::class, 'storeTeacher'], ['super_admin']);
$router->get('/admin/teachers/{id}/edit', [AdminController::class, 'editTeacher'], ['super_admin']);
$router->post('/admin/teachers/{id}', [AdminController::class, 'updateTeacher'], ['super_admin']);
$router->post('/admin/teachers/{id}/toggle', [AdminController::class, 'toggleTeacherStatus'], ['super_admin']);
$router->post('/admin/teachers/{id}/send-reset', [AdminController::class, 'sendPasswordReset'], ['super_admin']);
$router->get('/admin/plans', [AdminController::class, 'plans'], ['super_admin']);
$router->get('/admin/plans/create', [AdminController::class, 'createPlan'], ['super_admin']);
$router->post('/admin/plans', [AdminController::class, 'storePlan'], ['super_admin']);
$router->get('/admin/plans/{id}/edit', [AdminController::class, 'editPlan'], ['super_admin']);
$router->post('/admin/plans/{id}', [AdminController::class, 'updatePlan'], ['super_admin']);
$router->post('/admin/plans/{id}/toggle', [AdminController::class, 'togglePlan'], ['super_admin']);
$router->post('/admin/plans/{id}/delete', [AdminController::class, 'deletePlan'], ['super_admin']);
$router->get('/admin/subscriptions', [AdminController::class, 'subscriptions'], ['super_admin']);
$router->post('/admin/subscriptions/{id}/extend', [AdminController::class, 'extendSubscription'], ['super_admin']);
$router->post('/admin/subscriptions/{id}/approve', [AdminController::class, 'approvePayment'], ['super_admin']);
$router->post('/admin/subscriptions/{id}/reject', [AdminController::class, 'rejectPayment'], ['super_admin']);
$router->post('/admin/subscriptions/{id}/delete', [AdminController::class, 'deleteSubscription'], ['super_admin']);
$router->get('/admin/invoices', [AdminController::class, 'invoices'], ['super_admin']);
$router->get('/admin/payment-methods', [AdminController::class, 'paymentMethods'], ['super_admin']);
$router->post('/admin/payment-methods', [AdminController::class, 'storePaymentMethod'], ['super_admin']);
$router->post('/admin/payment-methods/{id}', [AdminController::class, 'updatePaymentMethod'], ['super_admin']);
$router->post('/admin/payment-methods/{id}/toggle', [AdminController::class, 'togglePaymentMethod'], ['super_admin']);
$router->post('/admin/payment-methods/{id}/delete', [AdminController::class, 'deletePaymentMethod'], ['super_admin']);
$router->get('/admin/settings/mail', [AdminController::class, 'mailSettings'], ['super_admin']);
$router->post('/admin/settings/mail', [AdminController::class, 'saveMailSettings'], ['super_admin']);
$router->post('/admin/settings/mail/test', [AdminController::class, 'testMail'], ['super_admin']);
$router->get('/admin/announcements', [AdminController::class, 'announcements'], ['super_admin']);
$router->post('/admin/announcements', [AdminController::class, 'storeAnnouncement'], ['super_admin']);
$router->post('/admin/announcements/{id}/delete', [AdminController::class, 'deleteAnnouncement'], ['super_admin']);
$router->get('/admin/reminders', [AdminController::class, 'reminders'], ['super_admin']);
$router->post('/admin/reminders/broadcast', [AdminController::class, 'broadcastReminder'], ['super_admin']);

// --- In-Place Update Module Routes (Super Admin) ---
$router->get('/admin/updates', [UpdateController::class, 'index'], ['super_admin']);
$router->post('/admin/updates/check', [UpdateController::class, 'check'], ['super_admin']);
$router->post('/admin/updates/apply', [UpdateController::class, 'apply'], ['super_admin']);
$router->get('/admin/updates/logs', [UpdateController::class, 'streamLogs'], ['super_admin']);
$router->post('/admin/updates/upload', [UpdateController::class, 'upload'], ['super_admin']);
$router->post('/admin/updates/backup', [UpdateController::class, 'createBackup'], ['super_admin']);
$router->post('/admin/updates/restore', [UpdateController::class, 'restoreBackup'], ['super_admin']);
$router->post('/admin/updates/backup/delete', [UpdateController::class, 'deleteBackup'], ['super_admin']);
$router->get('/admin/updates/backup/download/{filename}', [UpdateController::class, 'downloadBackup'], ['super_admin']);
$router->post('/admin/updates/settings', [UpdateController::class, 'saveSettings'], ['super_admin']);

// --- Teacher Subscription & Billing Routes ---
$router->get('/subscription', [SubscriptionController::class, 'index'], ['auth']);
$router->post('/subscription/checkout', [SubscriptionController::class, 'checkout'], ['auth']);
$router->get('/subscription/paywall', [SubscriptionController::class, 'paywall'], ['auth']);
$router->post('/subscription/verify', [SubscriptionController::class, 'verifyPayment'], ['auth']);
$router->get('/subscription/invoice/{id}', [SubscriptionController::class, 'showInvoice'], ['auth']);

// --- Teacher Reminders & To-Dos Routes ---
$router->get('/reminders', [ReminderController::class, 'index'], ['auth']);
$router->post('/reminders', [ReminderController::class, 'store'], ['auth']);
$router->post('/reminders/{id}/toggle', [ReminderController::class, 'toggle'], ['auth']);
$router->post('/reminders/{id}/delete', [ReminderController::class, 'delete'], ['auth']);

// --- User Profile & Account Settings Routes ---
$router->get('/profile', [ProfileController::class, 'show'], ['auth']);
$router->post('/profile', [ProfileController::class, 'update'], ['auth']);
$router->post('/profile/password', [ProfileController::class, 'updatePassword'], ['auth']);
$router->post('/profile/avatar/remove', [ProfileController::class, 'removeAvatar'], ['auth']);

// --- Dashboard Routes ---
$router->get('/', [DashboardController::class, 'index'], ['auth']);
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);

// --- Student Directory Routes ---
$router->get('/students', [StudentController::class, 'index'], ['auth']);
$router->get('/students/create', [StudentController::class, 'create'], ['auth']);
$router->post('/students', [StudentController::class, 'store'], ['auth']);
$router->get('/students/import', [StudentController::class, 'bulkImport'], ['auth']);
$router->post('/students/import/scan', [StudentController::class, 'bulkImportScan'], ['auth']);
$router->post('/students/import/commit', [StudentController::class, 'bulkImportCommit'], ['auth']);
$router->get('/students/import/template', [StudentController::class, 'downloadTemplate'], ['auth']);
$router->get('/students/search/api', [StudentController::class, 'searchApi'], ['auth']);
$router->get('/students/print/roster', [StudentController::class, 'printRoster'], ['auth']);
$router->get('/api/students/{id}/attendance-history', [StudentController::class, 'attendanceHistoryApi'], ['auth']);
$router->get('/students/{id}', [StudentController::class, 'show'], ['auth']);
$router->get('/students/{id}/edit', [StudentController::class, 'edit'], ['auth']);
$router->post('/students/{id}', [StudentController::class, 'update'], ['auth']);

// --- Parent Communication Journal Routes ---
$router->get('/calls', [CallLogController::class, 'index'], ['auth']);
$router->get('/calls/create', [CallLogController::class, 'create'], ['auth']);
$router->post('/calls', [CallLogController::class, 'store'], ['auth']);
$router->post('/calls/toggle-status/{id}', [CallLogController::class, 'toggleStatus'], ['auth']);
$router->get('/calls/export/csv', [CallLogController::class, 'exportCsv'], ['auth']);
$router->get('/calls/print/audit', [CallLogController::class, 'printAudit'], ['auth']);
$router->get('/calls/print/student/{id}', [CallLogController::class, 'printStudent'], ['auth']);
$router->get('/calls/print/class/{id}', [CallLogController::class, 'printClass'], ['auth']);

// --- Attendance Register & Reports Routes ---
$router->get('/attendance', [AttendanceController::class, 'index'], ['auth']);
$router->get('/attendance/sheet/{id}', [AttendanceController::class, 'sheet'], ['auth']);
$router->post('/attendance/sheet/{id}', [AttendanceController::class, 'save'], ['auth']);
$router->get('/attendance/reports', [AttendanceController::class, 'reports'], ['auth']);
$router->get('/attendance/print/report', [AttendanceController::class, 'printReport'], ['auth']);
$router->get('/attendance/print/sheet/{id}', [AttendanceController::class, 'printSheet'], ['auth']);

// --- Classes & Subjects Management Routes ---
$router->get('/classes', [ClassController::class, 'index'], ['auth']);
$router->get('/classes/promotion', [ClassController::class, 'promotion'], ['auth']);
$router->post('/classes/promotion', [ClassController::class, 'processPromotion'], ['auth']);
$router->post('/classes', [ClassController::class, 'store'], ['auth']);
$router->post('/classes/update/{id}', [ClassController::class, 'update'], ['auth']);
$router->post('/classes/delete/{id}', [ClassController::class, 'delete'], ['auth']);
$router->post('/classes/subjects', [ClassController::class, 'storeSubject'], ['auth']);
$router->post('/classes/subjects/delete/{id}', [ClassController::class, 'deleteSubject'], ['auth']);
$router->post('/classes/clean-mock-data', [ClassController::class, 'cleanMockData'], ['auth']);

// --- Overdue Task & Parent Followup Notifications API ---
$router->get('/api/notifications/overdue', [DashboardController::class, 'overdueNotificationsApi'], ['auth']);

// --- Examinations & Results Routes ---
$router->get('/results', [ResultController::class, 'index'], ['auth']);
$router->post('/results/exams', [ResultController::class, 'createExam'], ['auth']);
$router->get('/results/import', [ResultController::class, 'bulkImport'], ['auth']);
$router->post('/results/import/scan', [ResultController::class, 'bulkImportScan'], ['auth']);
$router->post('/results/import/commit', [ResultController::class, 'bulkImportCommit'], ['auth']);
$router->get('/results/import/template/{id}', [ResultController::class, 'downloadTemplate'], ['auth']);
$router->get('/results/enter/{id}', [ResultController::class, 'enterMarks'], ['auth']);
$router->post('/results/enter/{id}', [ResultController::class, 'saveMarks'], ['auth']);
$router->get('/results/print/class-matrix', [ResultController::class, 'printClassMatrix'], ['auth']);
$router->get('/results/print/exam/{id}', [ResultController::class, 'printExam'], ['auth']);
$router->get('/results/report/{id}', [ResultController::class, 'studentReport'], ['auth']);

// 5. Dispatch Request
$request = new Request();
$router->dispatch($request);
