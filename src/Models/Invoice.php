<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Invoice
{
    public static function generateInvoiceNumber(): string
    {
        $pdo = Database::getConnection();
        $year = date('Y');
        $prefix = "INV-{$year}-";
        
        $stmt = $pdo->prepare("SELECT invoice_number FROM invoices WHERE invoice_number LIKE :prefix ORDER BY id DESC LIMIT 1");
        $stmt->execute(['prefix' => "{$prefix}%"]);
        $last = $stmt->fetchColumn();

        if ($last) {
            $num = (int) substr($last, strlen($prefix));
            $next = $num + 1;
        } else {
            $next = 1001;
        }

        return $prefix . sprintf('%04d', $next);
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $invNumber = $data['invoice_number'] ?? self::generateInvoiceNumber();

        $stmt = $pdo->prepare("
            INSERT INTO invoices (
                invoice_number, user_id, subscription_id, plan_code, plan_name, billing_cycle,
                amount, currency, payment_method, payment_reference, payment_proof, status,
                issue_date, due_date, paid_at, notes
            ) VALUES (
                :invoice_number, :user_id, :subscription_id, :plan_code, :plan_name, :billing_cycle,
                :amount, :currency, :payment_method, :payment_reference, :payment_proof, :status,
                :issue_date, :due_date, :paid_at, :notes
            )
        ");

        $stmt->execute([
            'invoice_number' => $invNumber,
            'user_id' => (int) $data['user_id'],
            'subscription_id' => !empty($data['subscription_id']) ? (int) $data['subscription_id'] : null,
            'plan_code' => $data['plan_code'] ?? 'annual',
            'plan_name' => $data['plan_name'] ?? 'Annual Professional Plan',
            'billing_cycle' => $data['billing_cycle'] ?? 'annual',
            'amount' => (float) ($data['amount'] ?? 0.0),
            'currency' => $data['currency'] ?? 'USD',
            'payment_method' => $data['payment_method'] ?? 'easypaisa',
            'payment_reference' => $data['payment_reference'] ?? null,
            'payment_proof' => $data['payment_proof'] ?? null,
            'status' => $data['status'] ?? 'unpaid',
            'issue_date' => $data['issue_date'] ?? date('Y-m-d'),
            'due_date' => $data['due_date'] ?? date('Y-m-d', strtotime('+3 days')),
            'paid_at' => $data['paid_at'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT inv.*, u.name as teacher_name, u.email as teacher_email, u.phone as teacher_phone, u.school_name
            FROM invoices inv
            JOIN users u ON u.id = inv.user_id
            WHERE inv.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByNumber(string $invNumber): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT inv.*, u.name as teacher_name, u.email as teacher_email, u.phone as teacher_phone, u.school_name
            FROM invoices inv
            JOIN users u ON u.id = inv.user_id
            WHERE inv.invoice_number = :invNumber
            LIMIT 1
        ");
        $stmt->execute(['invNumber' => $invNumber]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findBySubscriptionId(int $subId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT inv.*, u.name as teacher_name, u.email as teacher_email, u.phone as teacher_phone, u.school_name
            FROM invoices inv
            JOIN users u ON u.id = inv.user_id
            WHERE inv.subscription_id = :subId
            ORDER BY inv.id DESC
            LIMIT 1
        ");
        $stmt->execute(['subId' => $subId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getUnpaidForSubscription(int $subId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM invoices 
            WHERE subscription_id = :subId AND status = 'unpaid'
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->execute(['subId' => $subId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getUnpaidForUser(int $userId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM invoices 
            WHERE user_id = :user_id AND status = 'unpaid'
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getByUser(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE user_id = :user_id ORDER BY id DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function getAll(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT inv.*, u.name as teacher_name, u.email as teacher_email, u.school_name
            FROM invoices inv
            JOIN users u ON u.id = inv.user_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND inv.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (inv.invoice_number LIKE :search OR u.name LIKE :search2 OR u.email LIKE :search3)";
            $term = '%' . trim($filters['search']) . '%';
            $params['search'] = $term;
            $params['search2'] = $term;
            $params['search3'] = $term;
        }

        $sql .= " ORDER BY inv.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function markPaid(int $id, ?string $paymentReference = null): bool
    {
        $pdo = Database::getConnection();
        $inv = self::findById($id);
        if (!$inv) {
            return false;
        }

        $params = [
            'id' => $id,
            'paid_at' => date('Y-m-d H:i:s'),
        ];
        $refClause = "";
        if ($paymentReference) {
            $refClause = ", payment_reference = :ref";
            $params['ref'] = $paymentReference;
        }

        $stmt = $pdo->prepare("UPDATE invoices SET status = 'paid', paid_at = :paid_at {$refClause} WHERE id = :id");
        $ok = $stmt->execute($params);

        if ($ok) {
            Subscription::extendOnInvoicePayment($id);
        }

        return $ok;
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $fields = [];
        $params = ['id' => $id];

        foreach (['plan_code', 'plan_name', 'billing_cycle', 'amount', 'currency', 'status', 'payment_method', 'payment_reference', 'payment_proof', 'paid_at', 'notes'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = :{$col}";
                $params[$col] = $data[$col];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE invoices SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
}
