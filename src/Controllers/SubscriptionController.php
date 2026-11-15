<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Subscription;
use App\Models\PaymentMethod;
use App\Models\Invoice;
use App\Models\User;
use App\Utils\Mailer;

class SubscriptionController
{
    public function index(Request $request): void
    {
        $teacherId = Session::userId();
        $activeSub = Subscription::getActiveForUser($teacherId);
        $history = Subscription::getByUser($teacherId);
        $methods = PaymentMethod::getAllActive();
        $invoices = Invoice::getByUser($teacherId);

        View::render('subscription.index', [
            'title' => 'Subscription & Billing',
            'activeSub' => $activeSub,
            'history' => $history,
            'invoices' => $invoices,
            'methods' => $methods,
            'plans' => Subscription::getPlans(),
        ]);
    }

    public function checkout(Request $request): void
    {
        $planCode = (string) $request->input('plan_code');

        if (!Subscription::getPlan($planCode)) {
            Session::flash('error', 'Selected plan is not valid.');
            Response::redirect('/subscription');
            return;
        }

        Response::redirect('/subscription/paywall?plan=' . $planCode);
    }

    public function paywall(Request $request): void
    {
        $teacherId = (int) Session::userId();
        $sub = Subscription::getActiveForUser($teacherId);

        // If user already has an active unlocked subscription, allow into dashboard
        if (Subscription::isUserUnlocked($teacherId)) {
            Response::redirect('/dashboard');
            return;
        }

        $selectedPlanCode = $request->query('plan') ?: ($sub['plan_code'] ?? 'annual');
        if (!Subscription::getPlan($selectedPlanCode)) {
            $selectedPlanCode = 'annual';
        }

        $methods = PaymentMethod::getAllActive();
        $selectedPlan = Subscription::getPlan($selectedPlanCode);

        View::render('subscription.paywall', [
            'title' => 'Workspace Locked - Payment Verification Required',
            'sub' => $sub,
            'plans' => Subscription::getPlans(),
            'selectedPlan' => $selectedPlan,
            'selectedPlanCode' => $selectedPlanCode,
            'methods' => $methods,
            'maxWidth' => 'max-w-3xl',
        ], 'auth');
    }

    public function verifyPayment(Request $request): void
    {
        $teacherId = (int) Session::userId();
        $user = User::findById($teacherId);
        $planCode = (string) $request->input('plan_code', 'annual');
        $paymentMethod = (string) $request->input('payment_method', 'easypaisa');

        if (!Subscription::getPlan($planCode)) {
            $planCode = 'annual';
        }

        $plan = Subscription::getPlan($planCode);

        // Case 1: 14-Day Free Trial
        if ($planCode === 'trial' || $paymentMethod === 'trial') {
            $subId = Subscription::create([
                'user_id' => $teacherId,
                'plan_code' => 'trial',
                'price' => 0.00,
                'status' => 'active',
                'payment_method' => 'trial',
                'notes' => '14-Day Free Trial Activated',
            ]);

            $invId = Invoice::create([
                'user_id' => $teacherId,
                'subscription_id' => $subId,
                'plan_code' => 'trial',
                'plan_name' => '14-Day Free Trial',
                'billing_cycle' => 'trial',
                'amount' => 0.00,
                'payment_method' => 'trial',
                'status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
                'notes' => 'Complimentary 14-Day Full Access Trial',
            ]);

            if ($user) {
                $inv = Invoice::findById($invId);
                if ($inv) {
                    Mailer::sendInvoiceEmail($inv, $user);
                }
            }

            Session::flash('success', 'Your 14-Day Free Trial has been activated! Your educator workspace is now unlocked.');
            Response::redirect('/dashboard');
            return;
        }

        // Handle uploaded payment receipt proof
        $paymentProof = null;
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'pdf'], true)) {
                $uploadDir = Config::get('BASE_DIR', dirname(__DIR__, 2)) . '/public/uploads/receipts';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $filename = 'receipt_' . $teacherId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
                    $paymentProof = '/uploads/receipts/' . $filename;
                }
            }
        }

        // EasyPaisa / JazzCash / Bank Transfer QR Verification
        $trxId = trim((string) $request->input('transaction_id', ''));
        $senderPhone = trim((string) $request->input('sender_phone', ''));

        if (empty($trxId)) {
            Session::flash('error', 'Please provide the Transaction ID (TRX ID) from your EasyPaisa / Bank confirmation receipt.');
            Response::redirect('/subscription/paywall?plan=' . $planCode);
            return;
        }

        $paymentRef = strtoupper($trxId);
        $methodLabel = strtoupper($paymentMethod);

        // Immediate Trial Mode until Super Admin verifies payment
        $subId = Subscription::create([
            'user_id' => $teacherId,
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

        $invId = Invoice::create([
            'user_id' => $teacherId,
            'subscription_id' => $subId,
            'plan_code' => $planCode,
            'plan_name' => $plan['name'],
            'billing_cycle' => $plan['cycle'],
            'amount' => $plan['price'],
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentRef,
            'payment_proof' => $paymentProof,
            'status' => 'pending_verification',
            'notes' => "Submitted for Super Admin payment audit.",
        ]);

        if ($user) {
            $inv = Invoice::findById($invId);
            if ($inv) {
                Mailer::sendInvoiceEmail($inv, $user);
            }
        }

        Session::flash('success', "Payment details received! Your workspace is unlocked in 14-Day Trial Mode while Super Admin verifies your {$methodLabel} transaction ({$paymentRef}). You have full access right now!");
        Response::redirect('/dashboard');
    }

    public function showInvoice(Request $request, string $id): void
    {
        $invId = (int) $id;
        $inv = Invoice::findById($invId);

        if (!$inv) {
            Session::flash('error', 'Invoice record not found.');
            Response::redirect('/subscription');
            return;
        }

        // Permission check
        $userId = (int) Session::userId();
        if ($inv['user_id'] !== $userId && !Session::isSuperAdmin()) {
            Session::flash('error', 'Access denied.');
            Response::redirect('/subscription');
            return;
        }

        View::render('subscription.invoice', [
            'invoice' => $inv,
            'title' => "Invoice #{$inv['invoice_number']}",
        ], 'none');
    }
}
