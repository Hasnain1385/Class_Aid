<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Utils\QrCodeGenerator;
use PDO;

class PaymentMethod
{
    public const DEFAULT_METHODS = [
        [
            'code' => 'easypaisa',
            'title' => 'EasyPaisa Mobile Account & QR',
            'account_title' => 'Class Aid Administrator',
            'account_number' => '0300-1234567',
            'qr_payload' => '00020101021126580014pk.easypaisa.app0111030012345675204599953035865802PK5918Class Aid Admin',
            'instructions' => "1. Open EasyPaisa App on your mobile device.\n2. Tap 'Scan QR' or 'Send Money' to Mobile Number: 0300-1234567\n3. Verify Account Title: Class Aid Administrator\n4. Confirm payment and save the 11-digit TRX ID (Transaction ID).",
            'currency' => 'PKR',
            'conversion_rate' => 280.0,
            'is_active' => 1,
            'sort_order' => 1,
        ],
        [
            'code' => 'jazzcash',
            'title' => 'JazzCash Mobile Account & QR',
            'account_title' => 'Class Aid Administrator',
            'account_number' => '0300-7654321',
            'qr_payload' => '00020101021126580014pk.jazzcash.app0111030076543215204599953035865802PK5918Class Aid Admin',
            'instructions' => "1. Open JazzCash App on your mobile device.\n2. Tap 'Send Money' -> 'To Mobile Account' -> 0300-7654321\n3. Verify Account Title: Class Aid Administrator\n4. Confirm transfer and save the TID (Transaction ID).",
            'currency' => 'PKR',
            'conversion_rate' => 280.0,
            'is_active' => 1,
            'sort_order' => 2,
        ],
        [
            'code' => 'bank',
            'title' => 'Direct Bank Wire / Online Transfer',
            'account_title' => 'Class Aid Digital Systems',
            'account_number' => 'PK36MEZN0001020304050607',
            'qr_payload' => 'Bank: Meezan Bank Ltd | Title: Class Aid Digital Systems | IBAN: PK36MEZN0001020304050607',
            'instructions' => "Bank: Meezan Bank Ltd\nAccount Title: Class Aid Digital Systems\nIBAN: PK36MEZN0001020304050607\nBranch: Main Educational Boulevard Branch\nAfter transferring funds via your banking app, ATM, or counter, submit the transaction ID or receipt screenshot.",
            'currency' => 'PKR',
            'conversion_rate' => 280.0,
            'is_active' => 1,
            'sort_order' => 3,
        ],
    ];

    private static bool $seeding = false;

    public static function ensureTableAndSeed(): void
    {
        if (self::$seeding) {
            return;
        }
        self::$seeding = true;

        try {
            $pdo = Database::getConnection();
            $isMysql = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql');

            if ($isMysql) {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS payment_methods (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        code VARCHAR(50) NOT NULL UNIQUE,
                        title VARCHAR(100) NOT NULL,
                        account_title VARCHAR(150) NULL,
                        account_number VARCHAR(100) NULL,
                        qr_payload TEXT NULL,
                        qr_image VARCHAR(255) NULL,
                        instructions TEXT NULL,
                        currency VARCHAR(10) DEFAULT 'PKR',
                        conversion_rate DECIMAL(10,2) DEFAULT 280.00,
                        is_active TINYINT(1) DEFAULT 1,
                        sort_order INT DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
            } else {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS payment_methods (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        code TEXT NOT NULL UNIQUE,
                        title TEXT NOT NULL,
                        account_title TEXT,
                        account_number TEXT,
                        qr_payload TEXT,
                        qr_image TEXT,
                        instructions TEXT,
                        currency TEXT DEFAULT 'PKR',
                        conversion_rate REAL DEFAULT 280.0,
                        is_active INTEGER DEFAULT 1,
                        sort_order INTEGER DEFAULT 0,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    );
                ");
            }

            $count = (int) $pdo->query("SELECT count(*) FROM payment_methods")->fetchColumn();
            if ($count === 0) {
                foreach (self::DEFAULT_METHODS as $m) {
                    self::create($m);
                }
            }
        } finally {
            self::$seeding = false;
        }
    }

    public static function getAllActive(): array
    {
        self::ensureTableAndSeed();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        $stmt->execute();
        $methods = $stmt->fetchAll();

        foreach ($methods as &$m) {
            $m['qr_url'] = QrCodeGenerator::getQrCodeUrl($m['qr_payload'] ?? '', $m['qr_image'] ?? null);
        }

        return $methods;
    }

    public static function getAll(): array
    {
        self::ensureTableAndSeed();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM payment_methods ORDER BY sort_order ASC, id ASC");
        $stmt->execute();
        $methods = $stmt->fetchAll();

        foreach ($methods as &$m) {
            $m['qr_url'] = QrCodeGenerator::getQrCodeUrl($m['qr_payload'] ?? '', $m['qr_image'] ?? null);
        }

        return $methods;
    }

    public static function findByCode(string $code): ?array
    {
        self::ensureTableAndSeed();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $m = $stmt->fetch();
        if ($m) {
            $m['qr_url'] = QrCodeGenerator::getQrCodeUrl($m['qr_payload'] ?? '', $m['qr_image'] ?? null);
            return $m;
        }
        return null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $m = $stmt->fetch();
        if ($m) {
            $m['qr_url'] = QrCodeGenerator::getQrCodeUrl($m['qr_payload'] ?? '', $m['qr_image'] ?? null);
            return $m;
        }
        return null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO payment_methods (code, title, account_title, account_number, qr_payload, qr_image, instructions, currency, conversion_rate, is_active, sort_order)
            VALUES (:code, :title, :account_title, :account_number, :qr_payload, :qr_image, :instructions, :currency, :conversion_rate, :is_active, :sort_order)
        ");

        $stmt->execute([
            'code' => strtolower(trim($data['code'])),
            'title' => trim($data['title']),
            'account_title' => trim($data['account_title'] ?? ''),
            'account_number' => trim($data['account_number'] ?? ''),
            'qr_payload' => trim($data['qr_payload'] ?? ''),
            'qr_image' => $data['qr_image'] ?? null,
            'instructions' => trim($data['instructions'] ?? ''),
            'currency' => trim($data['currency'] ?? 'PKR'),
            'conversion_rate' => (float) ($data['conversion_rate'] ?? 280.0),
            'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $fields = [];
        $params = ['id' => $id];

        $allowed = ['title', 'account_title', 'account_number', 'qr_payload', 'qr_image', 'instructions', 'currency', 'conversion_rate', 'is_active', 'sort_order'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = :{$f}";
                $params[$f] = $data[$f];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $sql = "UPDATE payment_methods SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function toggle(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE payment_methods SET is_active = (CASE WHEN is_active = 1 THEN 0 ELSE 1 END), updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
