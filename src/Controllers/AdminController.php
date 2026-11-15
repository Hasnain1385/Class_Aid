<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Announcement;
use App\Models\Reminder;
use App\Models\PaymentMethod;
use App\Models\Invoice;
use App\Models\Plan;
use App\Utils\Mailer;
use App\Core\Config;

class AdminController
{
    public function dashboard(Request $request): void
    {
        $stats = Subscription::getSaaSStats();
        $recentTeachers = array_slice(User::getAllTeachers(), 0, 6);
        $recentSubs = array_slice(Subscription::getAll(), 0, 6);
        $expiring = Subscription::getExpiringSoon(7);
        $announcements = Announcement::getAllAdmin();

        $pendingVerificationsCount = count(Subscription::getAll(['status' => 'pending_verification']));

        View::render('admin.dashboard', [
            'title' => 'SaaS Command Center',
            'stats' => $stats,
            'recentTeachers' => $recentTeachers,
            'recentSubs' => $recentSubs,
            'expiring' => $expiring,
            'announcements' => $announcements,
            'plans' => Plan::getAllActive(),
            'pendingVerificationsCount' => $pendingVerificationsCount,
        ], 'admin');
    }

    public function teachers(Request $request): void
    {
        $teachers = User::getAllTeachers();

        View::render('admin.teachers', [
            'title' => 'Manage Teachers & Workspaces',
            'teachers' => $teachers,
            'plans' => Subscription::getPlans(),
        ], 'admin');
    }

    public function createTeacher(Request $request): void
    {
        View::render('admin.teacher_create', [
            'title' => 'Provision New Teacher Account',
            'plans' => Subscription::getPlans(),
        ], 'admin');
    }

    public function storeTeacher(Request $request): void
    {
        $name = trim((string) $request->input('name'));
        $email = strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password');
        $schoolName = trim((string) $request->input('school_name', 'School'));
        $phone = trim((string) $request->input('phone', ''));
        $planCode = (string) $request->input('plan_code', 'annual');

        if (empty($name) || empty($email) || empty($password)) {
            Session::flash('error', 'Please fill in Name, Email, and Password.');
            Response::redirect('/admin/teachers/create');
        }

        $existing = User::findByEmail($email);
        if ($existing) {
            Session::flash('error', "Teacher with email {$email} already exists.");
            Response::redirect('/admin/teachers/create');
        }

        $userId = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'teacher',
            'school_name' => $schoolName,
            'status' => 'active',
            'phone' => $phone,
        ]);

        Subscription::create([
            'user_id' => $userId,
            'plan_code' => $planCode,
            'payment_method' => 'admin_provisioned',
            'notes' => 'Account manually provisioned by Super Admin',
        ]);

        Session::flash('success', "Teacher {$name} provisioned successfully on {$planCode} plan.");
        Response::redirect('/admin/teachers');
    }

    public function editTeacher(Request $request, string $id): void
    {
        $teacher = User::findById((int) $id);
        if (!$teacher || $teacher['role'] !== 'teacher') {
            Session::flash('error', 'Teacher not found.');
            Response::redirect('/admin/teachers');
        }

        $sub = Subscription::getActiveForUser((int) $id);
        $subHistory = Subscription::getByUser((int) $id);

        View::render('admin.teacher_edit', [
            'title' => 'Edit Teacher: ' . $teacher['name'],
            'teacher' => $teacher,
            'currentSub' => $sub,
            'subHistory' => $subHistory,
            'plans' => Subscription::getPlans(),
        ], 'admin');
    }

    public function updateTeacher(Request $request, string $id): void
    {
        $tId = (int) $id;
        $teacher = User::findById($tId);
        if (!$teacher) {
            Session::flash('error', 'Teacher not found.');
            Response::redirect('/admin/teachers');
        }

        $name = trim((string) $request->input('name'));
        $email = strtolower(trim((string) $request->input('email')));
        $schoolName = trim((string) $request->input('school_name'));
        $phone = trim((string) $request->input('phone'));
        $status = (string) $request->input('status', 'active');
        $password = (string) $request->input('password');

        $data = [
            'name' => $name,
            'email' => $email,
            'school_name' => $schoolName,
            'phone' => $phone,
            'status' => $status,
        ];
        if (!empty($password)) {
            $data['password'] = $password;
        }

        User::update($tId, $data);

        // Check if plan modification was requested
        $newPlan = $request->input('change_plan');
        if (!empty($newPlan) && Subscription::getPlan($newPlan)) {
            Subscription::create([
                'user_id' => $tId,
                'plan_code' => $newPlan,
                'payment_method' => 'admin_upgrade',
                'notes' => 'Plan updated by Super Admin',
            ]);
        }

        Session::flash('success', "Teacher records for {$name} updated successfully.");
        Response::redirect("/admin/teachers/{$tId}/edit");
    }

    public function toggleTeacherStatus(Request $request, string $id): void
    {
        $tId = (int) $id;
        $teacher = User::findById($tId);
        if (!$teacher || $teacher['role'] === 'super_admin') {
            Session::flash('error', 'Invalid teacher account.');
            Response::redirect('/admin/teachers');
        }

        $newStatus = ($teacher['status'] === 'active') ? 'suspended' : 'active';
        User::updateStatus($tId, $newStatus);

        $action = ($newStatus === 'active') ? 'reactivated' : 'suspended';
        Session::flash('success', "Teacher {$teacher['name']} has been {$action}.");
        Response::redirect('/admin/teachers');
    }

    public function sendPasswordReset(Request $request, string $id): void
    {
        $tId = (int) $id;
        $teacher = User::findById($tId);
        if (!$teacher) {
            Session::flash('error', 'Teacher account not found.');
            Response::redirect('/admin/teachers');
            return;
        }

        $token = User::generateResetToken($tId);
        $baseUrl = $request->getBaseUrl();
        $resetUrl = "{$baseUrl}/reset-password?token=" . urlencode($token);

        Mailer::sendPasswordResetEmail($teacher, $resetUrl);

        Session::flash('success', "Password reset link generated for {$teacher['name']}! Direct URL: {$resetUrl}");
        Session::flash('last_reset_link', $resetUrl);
        Session::flash('last_reset_teacher', $teacher['name']);
        Response::redirect('/admin/teachers');
    }

    public function subscriptions(Request $request): void
    {
        $planFilter = $request->query('plan');
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $subscriptions = Subscription::getAll([
            'plan' => $planFilter,
            'status' => $statusFilter,
            'search' => $search,
        ]);

        $pendingVerifications = Subscription::getAll([
            'status' => 'pending_verification',
        ]);

        View::render('admin.subscriptions', [
            'title' => 'Subscription Tracking & Management',
            'subscriptions' => $subscriptions,
            'pendingVerifications' => $pendingVerifications,
            'plans' => Subscription::getPlans(),
            'planFilter' => $planFilter,
            'statusFilter' => $statusFilter,
            'searchQuery' => $search,
        ], 'admin');
    }

    public function approvePayment(Request $request, string $id): void
    {
        $subId = (int) $id;
        $sub = Subscription::findById($subId);
        if (!$sub) {
            Session::flash('error', 'Subscription record not found.');
            Response::redirect('/admin/subscriptions');
            return;
        }

        $ok = Subscription::approvePayment($subId);
        if ($ok) {
            $teacher = User::findById((int) $sub['user_id']);
            $teacherName = $teacher['name'] ?? 'Teacher';
            Session::flash('success', "Payment approved! {$teacherName}'s account has been successfully switched to their selected plan, invoice marked paid, and receipt emailed.");
        } else {
            Session::flash('error', 'Failed to approve payment.');
        }

        Response::redirect('/admin/subscriptions');
    }

    public function rejectPayment(Request $request, string $id): void
    {
        $subId = (int) $id;
        Subscription::rejectPayment($subId);
        Session::flash('warning', 'Payment submission marked as rejected.');
        Response::redirect('/admin/subscriptions');
    }

    public function paymentMethods(Request $request): void
    {
        $methods = PaymentMethod::getAll();

        View::render('admin.payment_methods', [
            'title' => 'Payment Gateways & EasyPaisa QR Setup',
            'methods' => $methods,
        ], 'admin');
    }

    public function storePaymentMethod(Request $request): void
    {
        $code = strtolower(trim((string) $request->input('code')));
        $title = trim((string) $request->input('title'));
        $accountTitle = trim((string) $request->input('account_title'));
        $accountNumber = trim((string) $request->input('account_number'));
        $qrPayload = trim((string) $request->input('qr_payload'));
        $instructions = trim((string) $request->input('instructions'));

        if (empty($code) || empty($title) || empty($accountTitle)) {
            Session::flash('error', 'Please provide Code, Title, and Account Title.');
            Response::redirect('/admin/payment-methods');
            return;
        }

        $existing = PaymentMethod::findByCode($code);
        if ($existing) {
            Session::flash('error', "A payment method with code '{$code}' already exists.");
            Response::redirect('/admin/payment-methods');
            return;
        }

        $qrImage = null;
        if ($request->hasFile('qr_image')) {
            $file = $request->file('qr_image');
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'svg'], true)) {
                $uploadDir = Config::get('BASE_DIR', dirname(__DIR__, 2)) . '/public/uploads/qr';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $filename = 'custom_qr_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
                    $qrImage = '/uploads/qr/' . $filename;
                }
            }
        }

        PaymentMethod::create([
            'code' => $code,
            'title' => $title,
            'account_title' => $accountTitle,
            'account_number' => $accountNumber,
            'qr_payload' => $qrPayload,
            'qr_image' => $qrImage,
            'instructions' => $instructions,
        ]);

        Session::flash('success', "Payment gateway '{$title}' created successfully.");
        Response::redirect('/admin/payment-methods');
    }

    public function updatePaymentMethod(Request $request, string $id): void
    {
        $mId = (int) $id;
        $title = trim((string) $request->input('title'));
        $accountTitle = trim((string) $request->input('account_title'));
        $accountNumber = trim((string) $request->input('account_number'));
        $qrPayload = trim((string) $request->input('qr_payload'));
        $instructions = trim((string) $request->input('instructions'));
        $conversionRate = (float) $request->input('conversion_rate', 280.0);

        $data = [
            'title' => $title,
            'account_title' => $accountTitle,
            'account_number' => $accountNumber,
            'qr_payload' => $qrPayload,
            'instructions' => $instructions,
            'conversion_rate' => $conversionRate,
        ];

        if ($request->hasFile('qr_image')) {
            $file = $request->file('qr_image');
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'svg'], true)) {
                $uploadDir = Config::get('BASE_DIR', dirname(__DIR__, 2)) . '/public/uploads/qr';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $filename = 'custom_qr_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
                    $data['qr_image'] = '/uploads/qr/' . $filename;
                }
            }
        }

        PaymentMethod::update($mId, $data);
        Session::flash('success', "Gateway '{$title}' and QR code details updated.");
        Response::redirect('/admin/payment-methods');
    }

    public function togglePaymentMethod(Request $request, string $id): void
    {
        PaymentMethod::toggle((int) $id);
        Session::flash('success', 'Payment method status toggled.');
        Response::redirect('/admin/payment-methods');
    }

    public function deletePaymentMethod(Request $request, string $id): void
    {
        PaymentMethod::delete((int) $id);
        Session::flash('success', 'Payment gateway removed.');
        Response::redirect('/admin/payment-methods');
    }

    public function mailSettings(Request $request): void
    {
        $mailSettings = [
            'MAIL_HOST' => Config::get('MAIL_HOST', ''),
            'MAIL_PORT' => Config::get('MAIL_PORT', 587),
            'MAIL_USERNAME' => Config::get('MAIL_USERNAME', ''),
            'MAIL_PASSWORD' => Config::get('MAIL_PASSWORD', ''),
            'MAIL_ENCRYPTION' => Config::get('MAIL_ENCRYPTION', 'tls'),
            'MAIL_FROM_ADDRESS' => Config::get('MAIL_FROM_ADDRESS', 'billing@classaid.com'),
            'MAIL_FROM_NAME' => Config::get('MAIL_FROM_NAME', 'Class Aid Suite'),
        ];

        View::render('admin.mail_settings', [
            'title' => 'SMTP Mail Configuration',
            'mailSettings' => $mailSettings,
        ], 'admin');
    }

    public function saveMailSettings(Request $request): void
    {
        $envUpdates = [
            'MAIL_HOST' => trim((string) $request->input('mail_host')),
            'MAIL_PORT' => trim((string) $request->input('mail_port', '587')),
            'MAIL_USERNAME' => trim((string) $request->input('mail_username')),
            'MAIL_PASSWORD' => (string) $request->input('mail_password'),
            'MAIL_ENCRYPTION' => strtolower(trim((string) $request->input('mail_encryption', 'tls'))),
            'MAIL_FROM_ADDRESS' => trim((string) $request->input('mail_from_address')),
            'MAIL_FROM_NAME' => trim((string) $request->input('mail_from_name')),
        ];

        Config::writeEnv($envUpdates);
        Config::load(Config::get('BASE_DIR'));

        Session::flash('success', 'SMTP settings saved and updated in .env successfully.');
        Response::redirect('/admin/settings/mail');
    }

    public function testMail(Request $request): void
    {
        $testEmail = trim((string) $request->input('test_email'));
        if (empty($testEmail)) {
            Session::flash('error', 'Please enter a valid recipient test email.');
            Response::redirect('/admin/settings/mail');
            return;
        }

        $result = Mailer::send(
            $testEmail,
            'Test Recipient',
            'Class Aid SMTP Connection Test',
            '<h2>Class Aid SMTP Operational!</h2><p>This confirms your SMTP host, port, authentication, and TLS handshake are functioning properly.</p>'
        );

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', 'SMTP Dispatch Failed: ' . $result['message']);
        }

        Response::redirect('/admin/settings/mail');
    }

    public function extendSubscription(Request $request, string $id): void
    {
        $subId = (int) $id;
        $days = (int) $request->input('days', 30);

        Subscription::extend($subId, $days);
        Session::flash('success', "Subscription extended by {$days} days.");
        Response::redirect('/admin/subscriptions');
    }

    public function deleteSubscription(Request $request, string $id): void
    {
        $subId = (int) $id;
        $sub = Subscription::findById($subId);
        if ($sub) {
            Subscription::delete($subId);
            Session::flash('success', "Subscription #{$subId} removed successfully.");
        } else {
            Session::flash('error', "Subscription not found.");
        }
        Response::redirect('/admin/subscriptions');
    }

    public function announcements(Request $request): void
    {
        $announcements = Announcement::getAllAdmin();

        View::render('admin.announcements', [
            'title' => 'System Broadcasts & Announcements',
            'announcements' => $announcements,
            'types' => Announcement::TYPES,
        ], 'admin');
    }

    public function storeAnnouncement(Request $request): void
    {
        $title = trim((string) $request->input('title'));
        $content = trim((string) $request->input('content'));
        $type = (string) $request->input('type', 'info');
        $targetRole = (string) $request->input('target_role', 'all');
        $expiresAt = $request->input('expires_at') ? $request->input('expires_at') . ' 23:59:59' : null;

        if (empty($title) || empty($content)) {
            Session::flash('error', 'Announcement title and content cannot be blank.');
            Response::redirect('/admin/announcements');
        }

        Announcement::create([
            'title' => $title,
            'content' => $content,
            'type' => $type,
            'target_role' => $targetRole,
            'is_published' => 1,
            'expires_at' => $expiresAt,
            'created_by' => Session::userId(),
        ]);

        Session::flash('success', 'Announcement published live to all teachers.');
        Response::redirect('/admin/announcements');
    }

    public function deleteAnnouncement(Request $request, string $id): void
    {
        Announcement::delete((int) $id);
        Session::flash('success', 'Announcement deleted.');
        Response::redirect('/admin/announcements');
    }

    public function reminders(Request $request): void
    {
        $teachers = User::getAllTeachers();

        View::render('admin.reminders', [
            'title' => 'Broadcast Reminders & Notices',
            'teachers' => $teachers,
            'categories' => Reminder::CATEGORIES,
        ], 'admin');
    }

    public function broadcastReminder(Request $request): void
    {
        $target = (string) $request->input('target', 'all');
        $title = trim((string) $request->input('title'));
        $desc = trim((string) $request->input('description'));
        $dueDate = (string) $request->input('due_date', date('Y-m-d'));
        $priority = (string) $request->input('priority', 'medium');
        $category = (string) $request->input('category', 'general');

        if (empty($title)) {
            Session::flash('error', 'Reminder title cannot be blank.');
            Response::redirect('/admin/reminders');
        }

        if ($target === 'all') {
            $teachers = User::getAllTeachers();
            foreach ($teachers as $t) {
                Reminder::create([
                    'user_id' => (int) $t['id'],
                    'title' => $title,
                    'description' => $desc,
                    'due_date' => $dueDate,
                    'priority' => $priority,
                    'category' => $category,
                ]);
            }
            Session::flash('success', 'Reminder broadcast to all active teachers.');
        } else {
            $teacherId = (int) $target;
            Reminder::create([
                'user_id' => $teacherId,
                'title' => $title,
                'description' => $desc,
                'due_date' => $dueDate,
                'priority' => $priority,
                'category' => $category,
            ]);
            Session::flash('success', 'Reminder sent directly to teacher.');
        }

        Response::redirect('/admin/reminders');
    }

    public function plans(Request $request): void
    {
        $plans = Plan::getAll();
        View::render('admin.plans.index', [
            'title' => 'SaaS Subscription Plans Management',
            'plans' => $plans,
        ], 'admin');
    }

    public function createPlan(Request $request): void
    {
        View::render('admin.plans.create', [
            'title' => 'Add New SaaS Plan',
        ], 'admin');
    }

    public function storePlan(Request $request): void
    {
        $code = strtolower(trim((string) $request->input('code')));
        $name = trim((string) $request->input('name'));
        $cycle = strtolower(trim((string) $request->input('cycle', 'monthly')));
        $price = (float) $request->input('price', 0);
        $days = $request->input('days');
        $discount = trim((string) $request->input('discount', ''));
        $popular = (bool) $request->input('popular', false);
        $features = trim((string) $request->input('features', ''));
        $badge = trim((string) $request->input('badge', ''));

        if (empty($code) || empty($name)) {
            Session::flash('error', 'Plan code and name are required.');
            Response::redirect('/admin/plans/create');
            return;
        }

        $existing = Plan::findByCode($code);
        if ($existing) {
            Session::flash('error', "A plan with code '{$code}' already exists.");
            Response::redirect('/admin/plans/create');
            return;
        }

        Plan::create([
            'code' => $code,
            'name' => $name,
            'cycle' => $cycle,
            'price' => $price,
            'days' => ($days !== '' && $days !== null) ? (int)$days : null,
            'discount' => $discount ?: null,
            'popular' => $popular ? 1 : 0,
            'features' => $features,
            'badge' => $badge ?: null,
            'is_active' => 1,
            'sort_order' => (int) $request->input('sort_order', 10),
        ]);

        Session::flash('success', "Plan '{$name}' created successfully.");
        Response::redirect('/admin/plans');
    }

    public function editPlan(Request $request, string $id): void
    {
        $plan = Plan::findById((int) $id);
        if (!$plan) {
            Session::flash('error', 'Plan not found.');
            Response::redirect('/admin/plans');
            return;
        }

        View::render('admin.plans.edit', [
            'title' => "Edit Plan - {$plan['name']}",
            'plan' => $plan,
        ], 'admin');
    }

    public function updatePlan(Request $request, string $id): void
    {
        $planId = (int) $id;
        $plan = Plan::findById($planId);
        if (!$plan) {
            Session::flash('error', 'Plan not found.');
            Response::redirect('/admin/plans');
            return;
        }

        $name = trim((string) $request->input('name'));
        $cycle = strtolower(trim((string) $request->input('cycle', 'monthly')));
        $price = (float) $request->input('price', 0);
        $days = $request->input('days');
        $discount = trim((string) $request->input('discount', ''));
        $popular = (bool) $request->input('popular', false);
        $features = trim((string) $request->input('features', ''));
        $badge = trim((string) $request->input('badge', ''));

        if (empty($name)) {
            Session::flash('error', 'Plan name is required.');
            Response::redirect("/admin/plans/{$planId}/edit");
            return;
        }

        Plan::update($planId, [
            'name' => $name,
            'cycle' => $cycle,
            'price' => $price,
            'days' => ($days !== '' && $days !== null) ? (int)$days : null,
            'discount' => $discount ?: null,
            'popular' => $popular ? 1 : 0,
            'features' => $features,
            'badge' => $badge ?: null,
            'sort_order' => (int) $request->input('sort_order', $plan['sort_order']),
        ]);

        Session::flash('success', "Plan '{$name}' updated successfully.");
        Response::redirect('/admin/plans');
    }

    public function togglePlan(Request $request, string $id): void
    {
        $planId = (int) $id;
        Plan::toggle($planId);
        Session::flash('success', 'Plan status toggled.');
        Response::redirect('/admin/plans');
    }

    public function deletePlan(Request $request, string $id): void
    {
        $planId = (int) $id;
        $plan = Plan::findById($planId);
        if ($plan && in_array($plan['code'], ['trial', 'monthly', 'annual'], true)) {
            Session::flash('error', 'Core subscription plans cannot be deleted. You can deactivate them instead.');
            Response::redirect('/admin/plans');
            return;
        }

        Plan::delete($planId);
        Session::flash('success', 'Plan deleted successfully.');
        Response::redirect('/admin/plans');
    }

    public function invoices(Request $request): void
    {
        $status = $request->query('status');
        $search = $request->query('q');

        $invoices = Invoice::getAll([
            'status' => $status,
            'search' => $search,
        ]);

        View::render('admin.invoices.index', [
            'title' => 'Invoices & Billing Audits',
            'invoices' => $invoices,
            'statusFilter' => $status,
            'searchQuery' => $search,
        ], 'admin');
    }
}
