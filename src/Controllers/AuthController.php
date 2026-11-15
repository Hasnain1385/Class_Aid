<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Reminder;

class AuthController
{
    public function showLogin(Request $request): void
    {
        View::render('auth.login', ['title' => 'Sign In'], 'auth');
    }

    public function login(Request $request): void
    {
        $email = strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password');

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Please enter both email and password.');
            Response::redirect('/login');
        }

        $user = User::findByEmail($email);
        if (!$user || !User::verifyPassword($password, $user['password_hash'])) {
            Session::flash('error', 'Invalid email address or password.');
            Response::redirect('/login');
        }

        if (($user['status'] ?? 'active') === 'suspended') {
            Session::flash('error', 'Your teacher account has been suspended by the administrator. Please contact support.');
            Response::redirect('/login');
        }

        Session::setAuthUser($user);

        if (($user['role'] ?? '') === 'super_admin') {
            Session::flash('success', "Welcome back, Administrator {$user['name']}!");
            Response::redirect('/admin');
        }

        Session::flash('success', "Welcome back, {$user['name']}!");
        Response::redirect('/dashboard');
    }

    public function showRegister(Request $request): void
    {
        $selectedPlan = $request->query('plan', 'trial');
        $methods = \App\Models\PaymentMethod::getAllActive();
        View::render('auth.register', [
            'title' => 'Create Your Teacher Workspace',
            'subtitle' => 'Select your SaaS tier and register your educator cockpit in seconds',
            'maxWidth' => 'max-w-5xl',
            'plans' => Subscription::getPlans(),
            'selectedPlan' => $selectedPlan,
            'methods' => $methods,
        ], 'auth');
    }

    public function register(Request $request): void
    {
        $name = trim((string) $request->input('name'));
        $email = strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password');
        $schoolName = trim((string) $request->input('school_name', ''));
        $phone = trim((string) $request->input('phone', ''));
        $planCode = (string) $request->input('plan_code', 'trial');

        if (empty($name) || empty($email) || empty($password)) {
            Session::flash('error', 'Please fill in all required registration fields.');
            Response::redirect('/register');
        }

        if (strlen($password) < 6) {
            Session::flash('error', 'Password must be at least 6 characters.');
            Response::redirect('/register');
        }

        $existing = User::findByEmail($email);
        if ($existing) {
            Session::flash('error', 'An account with this email address already exists. Please sign in.');
            Response::redirect('/login');
        }

        if (!Subscription::getPlan($planCode)) {
            $planCode = 'trial';
        }

        $userId = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'teacher',
            'school_name' => !empty($schoolName) ? $schoolName : 'Independent Classroom',
            'status' => 'active',
            'phone' => $phone,
        ]);

        $trxId = trim((string) $request->input('transaction_id', ''));
        $paymentMethod = trim((string) $request->input('payment_method', 'easypaisa'));
        $senderPhone = trim((string) $request->input('sender_phone', ''));

        // Handle payment receipt upload if provided
        $paymentProof = null;
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'pdf'], true)) {
                $uploadDir = Config::get('BASE_DIR', dirname(__DIR__, 2)) . '/public/uploads/receipts';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $filename = 'receipt_' . $userId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
                    $paymentProof = '/uploads/receipts/' . $filename;
                }
            }
        }

        // Create initial onboarding reminder
        Reminder::create([
            'user_id' => $userId,
            'title' => 'Set up your first classroom & subjects',
            'description' => 'Add your Grade and Section under Classes & Subjects to start tracking attendance and parent logs.',
            'due_date' => date('Y-m-d', strtotime('+2 days')),
            'priority' => 'high',
            'category' => 'general',
        ]);

        $newUser = User::findById($userId);
        Session::setAuthUser($newUser);

        // Case 1: 14-Day Free Trial
        if ($planCode === 'trial') {
            $subId = Subscription::create([
                'user_id' => $userId,
                'plan_code' => 'trial',
                'plan_name' => '14-Day Free Trial',
                'billing_cycle' => 'trial',
                'price' => 0.00,
                'status' => 'trial',
                'starts_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+14 days')),
                'payment_method' => 'trial',
                'notes' => '14-Day Free Trial activated at registration',
            ]);

            $invId = \App\Models\Invoice::create([
                'user_id' => $userId,
                'subscription_id' => $subId,
                'plan_code' => 'trial',
                'plan_name' => '14-Day Free Trial',
                'billing_cycle' => 'trial',
                'amount' => 0.00,
                'payment_method' => 'trial',
                'status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
                'notes' => 'Complimentary 14-Day Trial',
            ]);

            $inv = \App\Models\Invoice::findById($invId);
            if ($inv) {
                \App\Utils\Mailer::sendInvoiceEmail($inv, $newUser);
            }

            Session::flash('success', "Welcome to Class Aid! Your 14-Day Free Trial is active and your educator workspace is unlocked.");
            Response::redirect('/dashboard');
            return;
        }

        // Case 2: Selected Paid Plan (Annual, Semi-Annual, Quarterly, Monthly, Lifetime)
        // APP GOES INTO TRIAL MODE IMMEDIATELY TILL SUPER ADMIN PAYMENT VERIFICATION
        $plan = Subscription::getPlan($planCode);
        $paymentRef = !empty($trxId) ? strtoupper($trxId) : ('TRX-' . strtoupper(substr($planCode, 0, 3)) . '-' . date('Ymd') . '-' . rand(100, 999));
        $methodLabel = strtoupper($paymentMethod);

        $subId = Subscription::create([
            'user_id' => $userId,
            'plan_code' => 'trial',
            'plan_name' => "14-Day Trial (Pending {$plan['name']})",
            'billing_cycle' => 'trial',
            'price' => 0.00,
            'status' => 'trial',
            'starts_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+14 days')),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentRef,
            'payment_proof' => $paymentProof,
            'pending_plan' => $planCode,
            'pending_price' => $plan['price'],
            'notes' => "Active in Trial Mode pending {$methodLabel} verification for {$plan['name']}. TRX: {$paymentRef}" . (!empty($senderPhone) ? " (Sender: {$senderPhone})" : ''),
        ]);

        $invId = \App\Models\Invoice::create([
            'user_id' => $userId,
            'subscription_id' => $subId,
            'plan_code' => $planCode,
            'plan_name' => $plan['name'],
            'billing_cycle' => $plan['cycle'],
            'amount' => $plan['price'],
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentRef,
            'payment_proof' => $paymentProof,
            'status' => 'pending_verification',
            'notes' => "Submitted during registration. Pending admin payment audit.",
        ]);

        $inv = \App\Models\Invoice::findById($invId);
        if ($inv) {
            \App\Utils\Mailer::sendInvoiceEmail($inv, $newUser);
        }

        Session::flash('success', "Welcome to Class Aid! Your educator workspace is active in 14-Day Trial Mode with full access while your payment for {$plan['name']} (TRX: {$paymentRef}) is being verified.");
        Response::redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        Session::logout();
        Session::flash('success', 'You have been logged out securely.');
        Response::redirect('/login');
    }

    public function showResetPassword(Request $request): void
    {
        $token = (string) $request->query('token', '');
        $user = User::findByResetToken($token);

        if (!$user) {
            Session::flash('error', 'This password reset link is invalid or has expired. Please request a new one from your administrator.');
            Response::redirect('/login');
            return;
        }

        View::render('auth.reset_password', [
            'title' => 'Set New Password',
            'subtitle' => "Resetting password for {$user['name']} ({$user['email']})",
            'token' => $token,
            'user' => $user,
            'maxWidth' => 'max-w-md',
        ], 'auth');
    }

    public function resetPassword(Request $request): void
    {
        $token = (string) $request->input('token', '');
        $password = (string) $request->input('password', '');
        $confirm = (string) $request->input('password_confirmation', '');

        if (strlen($password) < 6) {
            Session::flash('error', 'Password must be at least 6 characters.');
            Response::redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        if ($password !== $confirm) {
            Session::flash('error', 'Passwords do not match. Please try again.');
            Response::redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        $ok = User::resetPasswordWithToken($token, $password);
        if ($ok) {
            Session::flash('success', 'Your password has been successfully reset! You can now sign in.');
            Response::redirect('/login');
        } else {
            Session::flash('error', 'Password reset failed. The link may have expired.');
            Response::redirect('/login');
        }
    }
}
