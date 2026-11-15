<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class ParentCallLog
{
    public const CATEGORIES = [
        'Absenteeism' => [
            'label' => 'Absenteeism / Truancy',
            'badge' => 'badge-rose',
            'dot' => 'bg-rose-500',
            'color' => '#f43f5e',
        ],
        'Discipline' => [
            'label' => 'Discipline / Behavior',
            'badge' => 'badge-amber',
            'dot' => 'bg-amber-500',
            'color' => '#f59e0b',
        ],
        'Homework' => [
            'label' => 'Homework / Assignments',
            'badge' => 'badge-sky',
            'dot' => 'bg-sky-500',
            'color' => '#0284c7',
        ],
        'Academics' => [
            'label' => 'Exam / Test Marks',
            'badge' => 'badge-indigo',
            'dot' => 'bg-indigo-500',
            'color' => '#6366f1',
        ],
        'Health' => [
            'label' => 'Health / Medical',
            'badge' => 'badge-emerald',
            'dot' => 'bg-emerald-500',
            'color' => '#10b981',
        ],
        'Appreciation' => [
            'label' => 'Appreciation / Praise',
            'badge' => 'badge-violet',
            'dot' => 'bg-violet-500',
            'color' => '#a855f7',
        ],
        'General' => [
            'label' => 'General / Administration',
            'badge' => 'border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
            'dot' => 'bg-slate-500',
            'color' => '#64748b',
        ],
    ];

    public static function getBadgeInfo(string $category): array
    {
        return self::CATEGORIES[$category] ?? [
            'label' => $category,
            'badge' => 'badge-indigo',
            'dot' => 'bg-indigo-500',
            'color' => '#6366f1',
        ];
    }

    public static function getCategoryBadgeClass(string $category): string
    {
        return self::getBadgeInfo($category)['badge'];
    }

    public static function renderPill(string $category): string
    {
        $info = self::getBadgeInfo($category);
        $dotClass = $info['dot'] ?? 'bg-slate-400';
        $badgeClass = $info['badge'] ?? 'badge-indigo';
        $label = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
        return '<span class="status-pill ' . $badgeClass . '"><span class="status-dot ' . $dotClass . '"></span>' . $label . '</span>';
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO parent_call_logs (
                student_id, teacher_id, contact_person_name, relationship, phone_dialed,
                call_timestamp, call_type, call_category, discussion_summary, parent_response,
                follow_up_required, follow_up_date, status
            ) VALUES (
                :student_id, :teacher_id, :contact_person_name, :relationship, :phone_dialed,
                :call_timestamp, :call_type, :call_category, :discussion_summary, :parent_response,
                :follow_up_required, :follow_up_date, :status
            )
        ");

        $stmt->execute([
            'student_id' => (int) $data['student_id'],
            'teacher_id' => (int) $data['teacher_id'],
            'contact_person_name' => trim($data['contact_person_name']),
            'relationship' => trim($data['relationship']),
            'phone_dialed' => trim($data['phone_dialed']),
            'call_timestamp' => !empty($data['call_timestamp']) ? $data['call_timestamp'] : date('Y-m-d H:i:s'),
            'call_type' => $data['call_type'] ?? 'Phone Call',
            'call_category' => $data['call_category'] ?? 'General',
            'discussion_summary' => trim($data['discussion_summary']),
            'parent_response' => trim($data['parent_response']),
            'follow_up_required' => !empty($data['follow_up_required']) ? 1 : 0,
            'follow_up_date' => !empty($data['follow_up_date']) ? $data['follow_up_date'] : null,
            'status' => $data['status'] ?? 'Completed',
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT l.*, 
                   s.student_admin_id, s.first_name, s.last_name, s.primary_phone,
                   c.name as class_name, c.section as class_section
            FROM parent_call_logs l
            JOIN students s ON s.id = l.student_id
            JOIN classes c ON c.id = s.class_id
            WHERE l.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getByStudent(int $studentId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT l.*, u.name as teacher_name
            FROM parent_call_logs l
            JOIN users u ON u.id = l.teacher_id
            WHERE l.student_id = :student_id
            ORDER BY l.call_timestamp DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    public static function getAll(int $teacherId, array $filters = []): array
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT l.*, 
                   s.student_admin_id, s.first_name, s.last_name, s.primary_phone,
                   c.id as class_id, c.name as class_name, c.section as class_section
            FROM parent_call_logs l
            JOIN students s ON s.id = l.student_id
            JOIN classes c ON c.id = s.class_id
            WHERE l.teacher_id = :teacher_id
        ";
        $params = ['teacher_id' => $teacherId];

        if (!empty($filters['class_id'])) {
            $sql .= " AND c.id = :class_id";
            $params['class_id'] = (int) $filters['class_id'];
        }

        if (!empty($filters['student_id'])) {
            $sql .= " AND s.id = :student_id";
            $params['student_id'] = (int) $filters['student_id'];
        }

        if (!empty($filters['category'])) {
            $sql .= " AND l.call_category = :category";
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (s.student_admin_id LIKE :search OR s.first_name LIKE :search2 OR s.last_name LIKE :search3 OR l.contact_person_name LIKE :search4)";
            $term = '%' . trim($filters['search']) . '%';
            $params['search'] = $term;
            $params['search2'] = $term;
            $params['search3'] = $term;
            $params['search4'] = $term;
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND l.call_timestamp >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND l.call_timestamp <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $sql .= " ORDER BY l.call_timestamp DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getPendingFollowups(int $teacherId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT l.*, 
                   s.student_admin_id, s.first_name, s.last_name, s.primary_phone,
                   c.name as class_name, c.section as class_section
            FROM parent_call_logs l
            JOIN students s ON s.id = l.student_id
            JOIN classes c ON c.id = s.class_id
            WHERE l.teacher_id = :teacher_id 
              AND l.follow_up_required = 1 
              AND l.status != 'Resolved'
            ORDER BY l.follow_up_date ASC, l.call_timestamp DESC
        ");
        $stmt->execute(['teacher_id' => $teacherId]);
        return $stmt->fetchAll();
    }

    public static function getRecent(int $teacherId, int $limit = 5): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT l.*, 
                   s.student_admin_id, s.first_name, s.last_name,
                   c.name as class_name, c.section as class_section
            FROM parent_call_logs l
            JOIN students s ON s.id = l.student_id
            JOIN classes c ON c.id = s.class_id
            WHERE l.teacher_id = :teacher_id
            ORDER BY l.call_timestamp DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':teacher_id', $teacherId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE parent_call_logs SET status = :status WHERE id = :id");
        return $stmt->execute(['id' => $id, 'status' => $status]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM parent_call_logs WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
