<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Plan
{
    public const DEFAULT_PLANS = [
        'trial' => [
            'code' => 'trial',
            'name' => '14-Day Free Trial',
            'cycle' => 'trial',
            'days' => 14,
            'price' => 0.00,
            'discount' => 'Complimentary',
            'popular' => 0,
            'features' => [
                '14-Day Full Unrestricted Access',
                'Unlimited Students & Parent Journals',
                'Roll Call & WhatsApp Integration',
                'Automatic Switch to Paid Tier upon Verification',
            ],
            'badge' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
            'sort_order' => 1,
        ],
        'monthly' => [
            'code' => 'monthly',
            'name' => 'Monthly Starter Plan',
            'cycle' => 'monthly',
            'days' => 30,
            'price' => 14.99,
            'discount' => null,
            'popular' => 0,
            'features' => [
                'Single Educator License',
                'Unlimited Students & Attendance',
                'Parent Call Journal & Reminders',
                'Standard Email Support',
            ],
            'badge' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
            'sort_order' => 2,
        ],
        'annual' => [
            'code' => 'annual',
            'name' => 'Annual Professional Plan',
            'cycle' => 'annual',
            'days' => 365,
            'price' => 89.99,
            'discount' => 'Save 25% (Best Value)',
            'popular' => 1,
            'features' => [
                'Complete Academic Year License',
                'All Upcoming Feature Releases',
                'Unlimited Students, Classes & Exams',
                'Instant One-Click Backup Downloads',
                'School Head Sign-off Audit Mode',
            ],
            'badge' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20',
            'sort_order' => 3,
        ],
        'lifetime' => [
            'code' => 'lifetime',
            'name' => 'Lifetime VIP Plan',
            'cycle' => 'lifetime',
            'days' => null,
            'price' => 249.99,
            'discount' => 'Pay Once, Keep Forever',
            'popular' => 0,
            'features' => [
                'Perpetual Lifetime Access — Zero Renewal Fees',
                'All Current & Future Pro Modules',
                'VIP Direct Developer Priority Support',
                'Institutional White-Label Ready',
            ],
            'badge' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
            'sort_order' => 4,
        ],
    ];

    private static bool $seeding = false;

    public static function ensureTableAndSeed(): void
    {
        if (self::$seeding) {
            return;
        }
        self::$seeding = true;

        $pdo = Database::getConnection();
        $isMysql = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql');

        if ($isMysql) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS plans (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    code VARCHAR(50) NOT NULL UNIQUE,
                    name VARCHAR(100) NOT NULL,
                    cycle VARCHAR(50) NOT NULL DEFAULT 'monthly',
                    days INT NULL,
                    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    currency VARCHAR(10) DEFAULT 'USD',
                    discount VARCHAR(100) NULL,
                    popular TINYINT(1) DEFAULT 0,
                    features TEXT NULL,
                    badge VARCHAR(100) NULL,
                    is_active TINYINT(1) DEFAULT 1,
                    sort_order INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_plan_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } else {
            $pdo->exec("
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
            ");
        }

        $count = (int) $pdo->query("SELECT count(*) FROM plans")->fetchColumn();
        if ($count === 0) {
            foreach (self::DEFAULT_PLANS as $p) {
                self::create($p);
            }
        } else {
            // Ensure only the 4 offered tiers exist (trial, monthly, annual, lifetime)
            $pdo->exec("DELETE FROM plans WHERE code NOT IN ('trial', 'monthly', 'annual', 'lifetime')");
            $pdo->exec("UPDATE plans SET sort_order = 1 WHERE code = 'trial'");
            $pdo->exec("UPDATE plans SET sort_order = 2 WHERE code = 'monthly'");
            $pdo->exec("UPDATE plans SET sort_order = 3 WHERE code = 'annual'");
            $pdo->exec("UPDATE plans SET sort_order = 4 WHERE code = 'lifetime'");
        }
        self::$seeding = false;
    }

    public static function getAll(): array
    {
        self::ensureTableAndSeed();
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM plans ORDER BY sort_order ASC, id ASC");
        $plans = $stmt->fetchAll();

        foreach ($plans as &$p) {
            $p['features'] = !empty($p['features']) ? json_decode($p['features'], true) : [];
            if (!is_array($p['features'])) {
                $p['features'] = [];
            }
        }

        return $plans;
    }

    public static function getAllActive(): array
    {
        self::ensureTableAndSeed();
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        $plans = $stmt->fetchAll();

        $result = [];
        foreach ($plans as $p) {
            $p['features'] = !empty($p['features']) ? json_decode($p['features'], true) : [];
            if (!is_array($p['features'])) {
                $p['features'] = [];
            }
            $result[$p['code']] = $p;
        }

        return $result;
    }

    public static function findById(int $id): ?array
    {
        self::ensureTableAndSeed();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM plans WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $p = $stmt->fetch();
        if (!$p) {
            return null;
        }

        $p['features'] = !empty($p['features']) ? json_decode($p['features'], true) : [];
        if (!is_array($p['features'])) {
            $p['features'] = [];
        }
        return $p;
    }

    public static function findByCode(string $code): ?array
    {
        self::ensureTableAndSeed();
        $code = strtolower(trim($code));
        if ($code === 'anually' || $code === 'annually') {
            $code = 'annual';
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM plans WHERE code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $p = $stmt->fetch();
        if (!$p) {
            return null;
        }

        $p['features'] = !empty($p['features']) ? json_decode($p['features'], true) : [];
        if (!is_array($p['features'])) {
            $p['features'] = [];
        }
        return $p;
    }

    public static function create(array $data): int
    {
        self::ensureTableAndSeed();
        $pdo = Database::getConnection();

        $features = $data['features'] ?? [];
        if (is_string($features)) {
            // Split by newline or comma
            $lines = preg_split('/[\r\n]+/', trim($features));
            $features = array_values(array_filter(array_map('trim', $lines)));
        }
        $featuresJson = json_encode($features);

        $days = isset($data['days']) && $data['days'] !== '' && $data['days'] !== null ? (int) $data['days'] : null;
        if ($data['cycle'] === 'lifetime') {
            $days = null;
        } elseif ($days === null) {
            $cycleDays = [
                'trial' => 14,
                'monthly' => 30,
                'annual' => 365,
                'annually' => 365,
                'anually' => 365,
            ];
            $days = $cycleDays[$data['cycle']] ?? 30;
        }

        $stmt = $pdo->prepare("
            INSERT INTO plans (
                code, name, cycle, days, price, currency, discount, popular, features, badge, is_active, sort_order
            ) VALUES (
                :code, :name, :cycle, :days, :price, :currency, :discount, :popular, :features, :badge, :is_active, :sort_order
            )
        ");

        $stmt->execute([
            'code' => strtolower(trim((string) $data['code'])),
            'name' => trim((string) $data['name']),
            'cycle' => strtolower(trim((string) ($data['cycle'] ?? 'monthly'))),
            'days' => $days,
            'price' => (float) ($data['price'] ?? 0.0),
            'currency' => $data['currency'] ?? 'USD',
            'discount' => !empty($data['discount']) ? trim((string) $data['discount']) : null,
            'popular' => !empty($data['popular']) ? 1 : 0,
            'features' => $featuresJson,
            'badge' => $data['badge'] ?? null,
            'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        self::ensureTableAndSeed();
        $pdo = Database::getConnection();
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['name'])) {
            $fields[] = 'name = :name';
            $params['name'] = trim((string) $data['name']);
        }

        if (isset($data['code'])) {
            $fields[] = 'code = :code';
            $params['code'] = strtolower(trim((string) $data['code']));
        }

        if (isset($data['cycle'])) {
            $fields[] = 'cycle = :cycle';
            $params['cycle'] = strtolower(trim((string) $data['cycle']));
        }

        if (array_key_exists('days', $data)) {
            $fields[] = 'days = :days';
            $params['days'] = ($data['days'] !== '' && $data['days'] !== null) ? (int) $data['days'] : null;
        }

        if (isset($data['price'])) {
            $fields[] = 'price = :price';
            $params['price'] = (float) $data['price'];
        }

        if (array_key_exists('discount', $data)) {
            $fields[] = 'discount = :discount';
            $params['discount'] = !empty($data['discount']) ? trim((string) $data['discount']) : null;
        }

        if (isset($data['popular'])) {
            $fields[] = 'popular = :popular';
            $params['popular'] = !empty($data['popular']) ? 1 : 0;
        }

        if (isset($data['features'])) {
            $features = $data['features'];
            if (is_string($features)) {
                $lines = preg_split('/[\r\n]+/', trim($features));
                $features = array_values(array_filter(array_map('trim', $lines)));
            }
            $fields[] = 'features = :features';
            $params['features'] = json_encode($features);
        }

        if (array_key_exists('badge', $data)) {
            $fields[] = 'badge = :badge';
            $params['badge'] = $data['badge'];
        }

        if (isset($data['is_active'])) {
            $fields[] = 'is_active = :is_active';
            $params['is_active'] = (int) $data['is_active'];
        }

        if (isset($data['sort_order'])) {
            $fields[] = 'sort_order = :sort_order';
            $params['sort_order'] = (int) $data['sort_order'];
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = 'updated_at = :updated_at';
        $params['updated_at'] = date('Y-m-d H:i:s');

        $sql = "UPDATE plans SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function toggle(int $id): bool
    {
        self::ensureTableAndSeed();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE plans SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function delete(int $id): bool
    {
        self::ensureTableAndSeed();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM plans WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
