<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Subscription
{
    public const PLANS = [
        'trial' => [
            'code' => 'trial',
            'name' => '14-Day Free Trial',
            'cycle' => 'trial',
            'days' => 14,
            'price' => 0.00,
            'discount' => 'No Card Required',
            'features' => [
                'Full 14-Day Workspace Access',
                '1-Tap Roll Call & Attendance',
                'Parent WhatsApp Journal',
                'Student Rosters & OCR Imports',
                'Zero Initial Payment',
            ],
            'badge' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
        ],
        'monthly' => [
            'code' => 'monthly',
            'name' => 'Monthly Starter Plan',
            'cycle' => 'monthly',
            'days' => 30,
            'price' => 9.99,
            'features' => [
                'Unlimited Students & Classes',
                '1-Tap Roll Call & Absenteeism Flags',
                'Parent WhatsApp Journal & PDF Audits',
                'Grade Entry & Report Cards',
                'Email & Web Reminders',
            ],
            'badge' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
        ],
        'quarterly' => [
            'code' => 'quarterly',
            'name' => 'Quarterly Educator Plan',
            'cycle' => 'quarterly',
            'days' => 90,
            'price' => 26.99,
            'discount' => 'Save 10%',
            'features' => [
                'Everything in Monthly Plan',
                'Term Exam Weighting & Analytics',
                'Institutional Audit Sign-off Sheets',
                'Priority Feature Upgrades',
            ],
            'badge' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
        ],
        'semi_annual' => [
            'code' => 'semi_annual',
            'name' => 'Semi-Annual Pro Plan',
            'cycle' => 'semi_annual',
            'days' => 180,
            'price' => 49.99,
            'discount' => 'Save 16%',
            'features' => [
                'Everything in Quarterly Plan',
                'Semester Exam & Attendance Trends',
                'Multi-Class Bulk CSV Data Exports',
                'Dedicated Support Desk',
            ],
            'badge' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
        ],
        'annual' => [
            'code' => 'annual',
            'name' => 'Annual Professional Plan',
            'cycle' => 'annual',
            'days' => 365,
            'price' => 89.99,
            'discount' => 'Save 25% (Best Value)',
            'popular' => true,
            'features' => [
                'Complete Academic Year License',
                'All Upcoming Feature Releases',
                'Unlimited Students, Classes & Exams',
                'Instant One-Click Backup Downloads',
                'School Head Sign-off Audit Mode',
            ],
            'badge' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20',
        ],
        'lifetime' => [
            'code' => 'lifetime',
            'name' => 'Lifetime VIP Plan',
            'cycle' => 'lifetime',
            'days' => null, // Never expires
            'price' => 249.99,
            'discount' => 'Pay Once, Keep Forever',
            'features' => [
                'Perpetual Lifetime Access — Zero Renewal Fees',
                'All Current & Future Pro Modules',
                'VIP Direct Developer Priority Support',
                'Institutional White-Label Ready',
            ],
            'badge' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
        ],
    ];

    public static function getPlan(string $code): ?array
    {
        $plan = Plan::findByCode($code);
        if ($plan) {
            return $plan;
        }
        return self::PLANS[$code] ?? null;
    }

    public static function getPlans(): array
    {
        $plans = Plan::getAllActive();
        if (!empty($plans)) {
            return $plans;
        }
        return self::PLANS;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $sub = $stmt->fetch();
        return $sub ?: null;
    }

    public static function getCurrentForUser(int $userId): ?array
    {
        return self::getActiveForUser($userId);
    }

    public static function getActiveForUser(int $userId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM subscriptions 
            WHERE user_id = :user_id 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        $sub = $stmt->fetch();
        if (!$sub) {
            return null;
        }

        // Decorate with status computation
        $sub['is_lifetime'] = ($sub['plan_code'] === 'lifetime' || $sub['status'] === 'lifetime');
        if ($sub['is_lifetime']) {
            $sub['status'] = 'lifetime';
            $sub['days_left'] = 9999;
            $sub['is_expiring'] = false;
            $sub['is_expired'] = false;
        } else {
            $expiryTimestamp = !empty($sub['expires_at']) ? strtotime($sub['expires_at']) : 0;
            $now = time();
            $diffSeconds = $expiryTimestamp - $now;
            $daysLeft = (int) ceil($diffSeconds / 86400);

            $sub['days_left'] = $daysLeft;
            $sub['is_expired'] = ($daysLeft <= 0);
            $sub['is_expiring'] = ($daysLeft > 0 && $daysLeft <= 7);

            if ($sub['is_expired'] && $sub['status'] === 'active') {
                $sub['status'] = 'expired';
            }
        }

        return $sub;
    }

    public static function isUserUnlocked(int $userId): bool
    {
        $sub = self::getActiveForUser($userId);
        if (!$sub) {
            return false;
        }

        if (in_array($sub['status'], ['pending_payment', 'unpaid', 'cancelled', 'expired'], true)) {
            return false;
        }

        if (!empty($sub['is_expired'])) {
            return false;
        }

        return in_array($sub['status'], ['active', 'lifetime', 'trial'], true);
    }

    public static function getByUser(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = :user_id ORDER BY id DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function getAll(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT sub.*, u.name as teacher_name, u.email as teacher_email, u.school_name, u.status as user_status
            FROM subscriptions sub
            JOIN users u ON u.id = sub.user_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['plan'])) {
            $sql .= " AND sub.plan_code = :plan";
            $params['plan'] = $filters['plan'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'pending_verification') {
                $sql .= " AND (sub.status = 'pending_verification' OR sub.pending_plan IS NOT NULL)";
            } else {
                $sql .= " AND sub.status = :status";
                $params['status'] = $filters['status'];
            }
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (u.name LIKE :search OR u.email LIKE :search2 OR u.school_name LIKE :search3)";
            $term = '%' . trim($filters['search']) . '%';
            $params['search'] = $term;
            $params['search2'] = $term;
            $params['search3'] = $term;
        }

        $sql .= " ORDER BY sub.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $plan = self::getPlan($data['plan_code'] ?? 'monthly');
        $planName = $data['plan_name'] ?? ($plan['name'] ?? 'Class Aid Subscription');
        $cycle = $data['billing_cycle'] ?? ($plan['cycle'] ?? 'monthly');
        $price = (float) ($data['price'] ?? ($plan['price'] ?? 0.0));

        $startsAt = !empty($data['starts_at']) ? $data['starts_at'] : date('Y-m-d H:i:s');
        $expiresAt = null;

        if ($data['plan_code'] !== 'lifetime') {
            $days = $plan['days'] ?? 30;
            $expiresAt = date('Y-m-d H:i:s', strtotime($startsAt . " +{$days} days"));
        }

        $status = ($data['plan_code'] === 'lifetime') ? 'lifetime' : 'active';
        $paymentRef = $data['payment_reference'] ?? ('SUB-' . strtoupper(substr($data['plan_code'], 0, 3)) . '-' . date('Ymd') . '-' . rand(100, 999));

        $stmt = $pdo->prepare("
            INSERT INTO subscriptions (
                user_id, plan_code, plan_name, billing_cycle, price, currency, status,
                starts_at, expires_at, auto_renew, payment_method, payment_reference, payment_proof, pending_plan, pending_price, notes
            ) VALUES (
                :user_id, :plan_code, :plan_name, :billing_cycle, :price, :currency, :status,
                :starts_at, :expires_at, :auto_renew, :payment_method, :payment_reference, :payment_proof, :pending_plan, :pending_price, :notes
            )
        ");

        $stmt->execute([
            'user_id' => (int) $data['user_id'],
            'plan_code' => $data['plan_code'],
            'plan_name' => $planName,
            'billing_cycle' => $cycle,
            'price' => $price,
            'currency' => $data['currency'] ?? 'USD',
            'status' => $data['status'] ?? $status,
            'starts_at' => $startsAt,
            'expires_at' => $data['expires_at'] ?? $expiresAt,
            'auto_renew' => isset($data['auto_renew']) ? (int) $data['auto_renew'] : 1,
            'payment_method' => $data['payment_method'] ?? 'card',
            'payment_reference' => $paymentRef,
            'payment_proof' => $data['payment_proof'] ?? null,
            'pending_plan' => $data['pending_plan'] ?? null,
            'pending_price' => isset($data['pending_price']) ? (float) $data['pending_price'] : null,
            'notes' => $data['notes'] ?? null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['plan_code'])) {
            $fields[] = 'plan_code = :plan_code';
            $params['plan_code'] = $data['plan_code'];
            $plan = self::getPlan($data['plan_code']);
            if ($plan) {
                $fields[] = 'plan_name = :plan_name';
                $params['plan_name'] = $plan['name'];
                $fields[] = 'billing_cycle = :billing_cycle';
                $params['billing_cycle'] = $plan['cycle'];
            }
        }

        if (isset($data['status'])) {
            $fields[] = 'status = :status';
            $params['status'] = $data['status'];
        }

        if (array_key_exists('expires_at', $data)) {
            $fields[] = 'expires_at = :expires_at';
            $params['expires_at'] = $data['expires_at'];
        }

        if (isset($data['starts_at'])) {
            $fields[] = 'starts_at = :starts_at';
            $params['starts_at'] = $data['starts_at'];
        }

        if (isset($data['price'])) {
            $fields[] = 'price = :price';
            $params['price'] = (float) $data['price'];
        }

        if (array_key_exists('pending_plan', $data)) {
            $fields[] = 'pending_plan = :pending_plan';
            $params['pending_plan'] = $data['pending_plan'];
        }

        if (array_key_exists('pending_price', $data)) {
            $fields[] = 'pending_price = :pending_price';
            $params['pending_price'] = $data['pending_price'];
        }

        if (isset($data['payment_proof'])) {
            $fields[] = 'payment_proof = :payment_proof';
            $params['payment_proof'] = $data['payment_proof'];
        }

        if (isset($data['payment_reference'])) {
            $fields[] = 'payment_reference = :payment_reference';
            $params['payment_reference'] = $data['payment_reference'];
        }

        if (isset($data['payment_method'])) {
            $fields[] = 'payment_method = :payment_method';
            $params['payment_method'] = $data['payment_method'];
        }

        if (isset($data['notes'])) {
            $fields[] = 'notes = :notes';
            $params['notes'] = $data['notes'];
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = 'updated_at = :updated_at';
        $params['updated_at'] = date('Y-m-d H:i:s');

        $sql = "UPDATE subscriptions SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function extend(int $id, int $days): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $sub = $stmt->fetch();
        if (!$sub || $sub['plan_code'] === 'lifetime') {
            return false;
        }

        $base = !empty($sub['expires_at']) && strtotime($sub['expires_at']) > time()
            ? strtotime($sub['expires_at'])
            : time();

        $newExpiry = date('Y-m-d H:i:s', strtotime("+{$days} days", $base));
        $stmt = $pdo->prepare("UPDATE subscriptions SET expires_at = :exp, status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['exp' => $newExpiry, 'id' => $id]);
    }

    public static function getExpiringSoon(int $days = 7): array
    {
        $pdo = Database::getConnection();
        $now = date('Y-m-d H:i:s');
        $future = date('Y-m-d H:i:s', strtotime("+{$days} days"));

        $stmt = $pdo->prepare("
            SELECT sub.*, u.name as teacher_name, u.email as teacher_email, u.school_name
            FROM subscriptions sub
            JOIN users u ON u.id = sub.user_id
            WHERE sub.status = 'active'
              AND sub.expires_at IS NOT NULL
              AND sub.expires_at >= :now
              AND sub.expires_at <= :future
            ORDER BY sub.expires_at ASC
        ");
        $stmt->execute(['now' => $now, 'future' => $future]);
        return $stmt->fetchAll();
    }

    public static function getSaaSStats(): array
    {
        $pdo = Database::getConnection();

        $totalTeachers = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
        $activeSubs = (int) $pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status IN ('active', 'lifetime')")->fetchColumn();
        $lifetimeSubs = (int) $pdo->query("SELECT COUNT(*) FROM subscriptions WHERE plan_code = 'lifetime' OR status = 'lifetime'")->fetchColumn();
        $now = date('Y-m-d H:i:s');
        $expiredStmt = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE status = 'expired' OR (expires_at IS NOT NULL AND expires_at < :now AND status != 'lifetime')");
        $expiredStmt->execute(['now' => $now]);
        $expiredSubs = (int) $expiredStmt->fetchColumn();
        
        $in7Days = date('Y-m-d H:i:s', strtotime('+7 days'));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE status = 'active' AND expires_at BETWEEN :now AND :future");
        $stmt->execute(['now' => $now, 'future' => $in7Days]);
        $expiringIn7Days = (int) $stmt->fetchColumn();

        // Calculate approximate MRR (Monthly Recurring Revenue)
        $subs = $pdo->query("SELECT plan_code, price FROM subscriptions WHERE status IN ('active', 'lifetime')")->fetchAll();
        $mrr = 0.0;
        $totalRevenue = 0.0;
        $planBreakdown = ['monthly' => 0, 'quarterly' => 0, 'semi_annual' => 0, 'annual' => 0, 'lifetime' => 0];

        foreach ($subs as $s) {
            $code = $s['plan_code'];
            $price = (float) $s['price'];
            $totalRevenue += $price;

            if (isset($planBreakdown[$code])) {
                $planBreakdown[$code]++;
            }

            // Normalize to monthly equivalent
            $mrr += match ($code) {
                'monthly' => $price,
                'quarterly' => $price / 3,
                'semi_annual' => $price / 6,
                'annual' => $price / 12,
                'lifetime' => $price / 24, // amortized over 2 years
                default => 0.0,
            };
        }

        return [
            'total_teachers' => $totalTeachers,
            'active_subscriptions' => $activeSubs,
            'lifetime_subscriptions' => $lifetimeSubs,
            'expired_subscriptions' => $expiredSubs,
            'expiring_in_7_days' => $expiringIn7Days,
            'estimated_mrr' => round($mrr, 2),
            'estimated_arr' => round($mrr * 12, 2),
            'total_revenue' => round($totalRevenue, 2),
            'plan_breakdown' => $planBreakdown,
        ];
    }

    public static function approvePayment(int $subId, string $notes = ''): bool
    {
        $sub = self::findById($subId);
        if (!$sub) {
            return false;
        }

        $targetPlanCode = !empty($sub['pending_plan']) ? $sub['pending_plan'] : $sub['plan_code'];
        if ($targetPlanCode === 'trial') {
            $targetPlanCode = 'annual';
        }
        $plan = self::getPlan($targetPlanCode) ?? self::getPlan('annual');

        $isLifetime = ($targetPlanCode === 'lifetime');
        $newStatus = $isLifetime ? 'lifetime' : 'active';
        $startsAt = date('Y-m-d H:i:s');
        $expiresAt = null;

        if (!$isLifetime) {
            $days = $plan['days'] ?? 365;
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        }

        $planPrice = (float) (!empty($sub['pending_price']) ? $sub['pending_price'] : ($plan['price'] ?? 0.0));
        $noteText = !empty($notes) ? $notes : ("Verified by Super Admin on " . date('Y-m-d H:i') . ". Switched from Trial to {$plan['name']}.");

        $ok = self::update($subId, [
            'plan_code' => $targetPlanCode,
            'price' => $planPrice,
            'status' => $newStatus,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'pending_plan' => null,
            'pending_price' => null,
            'notes' => $noteText,
        ]);

        if (!$ok) {
            return false;
        }

        // Find or create matching invoice
        $inv = Invoice::findBySubscriptionId($subId);
        if (!$inv && !empty($sub['payment_reference'])) {
            $invoices = Invoice::getAll(['search' => $sub['payment_reference']]);
            $inv = !empty($invoices) ? $invoices[0] : null;
        }

        if ($inv) {
            Invoice::update((int) $inv['id'], [
                'plan_code' => $targetPlanCode,
                'plan_name' => $plan['name'],
                'billing_cycle' => $plan['cycle'],
                'amount' => $planPrice,
                'status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
                'notes' => 'Payment verified and approved by Super Admin',
            ]);
            $inv = Invoice::findById((int) $inv['id']);
        } else {
            $invId = Invoice::create([
                'user_id' => $sub['user_id'],
                'subscription_id' => $sub['id'],
                'plan_code' => $targetPlanCode,
                'plan_name' => $plan['name'],
                'billing_cycle' => $plan['cycle'],
                'amount' => $planPrice,
                'payment_method' => $sub['payment_method'],
                'payment_reference' => $sub['payment_reference'],
                'payment_proof' => $sub['payment_proof'] ?? null,
                'status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
                'notes' => 'Generated upon Super Admin verification',
            ]);
            $inv = Invoice::findById($invId);
        }

        $teacher = User::findById((int) $sub['user_id']);
        if ($teacher && $inv) {
            \App\Utils\Mailer::sendInvoiceEmail($inv, $teacher);
        }

        return true;
    }

    public static function rejectPayment(int $subId, string $reason = ''): bool
    {
        $sub = self::findById($subId);
        if (!$sub) {
            return false;
        }

        $notes = !empty($reason) ? $reason : 'Payment rejected by Super Admin. Invalid transaction reference / slip.';

        return self::update($subId, [
            'status' => 'cancelled',
            'pending_plan' => null,
            'notes' => $notes,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM subscriptions WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
