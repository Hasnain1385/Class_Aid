<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Reminder
{
    public const CATEGORIES = [
        'call' => [
            'label' => 'Parent Communication',
            'badge' => 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20',
            'icon' => 'phone-call',
        ],
        'attendance' => [
            'label' => 'Roll Call & Truancy',
            'badge' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
            'icon' => 'calendar-check',
        ],
        'exam' => [
            'label' => 'Marks & Exams',
            'badge' => 'bg-violet-500/10 text-violet-500 border-violet-500/20',
            'icon' => 'award',
        ],
        'subscription' => [
            'label' => 'SaaS Subscription',
            'badge' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
            'icon' => 'credit-card',
        ],
        'general' => [
            'label' => 'General School Task',
            'badge' => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
            'icon' => 'check-square',
        ],
    ];

    public static function getByUser(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM reminders 
            WHERE user_id = :user_id 
            ORDER BY status ASC, due_date ASC, priority DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function getPending(int $userId, int $limit = 5): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM reminders 
            WHERE user_id = :user_id AND status = 'pending'
            ORDER BY due_date ASC, priority DESC 
            LIMIT :lim
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countPending(int $userId): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reminders WHERE user_id = :user_id AND status = 'pending'");
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO reminders (user_id, title, description, due_date, due_time, priority, status, category)
            VALUES (:user_id, :title, :description, :due_date, :due_time, :priority, :status, :category)
        ");
        $stmt->execute([
            'user_id' => (int) $data['user_id'],
            'title' => trim($data['title']),
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'due_date' => $data['due_date'] ?: date('Y-m-d'),
            'due_time' => !empty($data['due_time']) ? trim($data['due_time']) : null,
            'priority' => $data['priority'] ?? 'medium',
            'status' => $data['status'] ?? 'pending',
            'category' => $data['category'] ?? 'general',
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function toggleStatus(int $id, int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT status FROM reminders WHERE id = :id AND user_id = :user_id LIMIT 1");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }

        $newStatus = ($row['status'] === 'pending') ? 'completed' : 'pending';
        $up = $pdo->prepare("UPDATE reminders SET status = :status WHERE id = :id");
        return $up->execute(['status' => $newStatus, 'id' => $id]);
    }

    public static function delete(int $id, int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM reminders WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}
