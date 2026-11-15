<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Announcement
{
    public const TYPES = [
        'info' => [
            'label' => 'Information',
            'badge' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
            'banner' => 'bg-blue-500/10 border-blue-500/30 text-blue-700 dark:text-blue-300',
            'icon' => 'info',
        ],
        'success' => [
            'label' => 'Feature Update',
            'badge' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
            'banner' => 'bg-emerald-500/10 border-emerald-500/30 text-emerald-700 dark:text-emerald-300',
            'icon' => 'sparkles',
        ],
        'warning' => [
            'label' => 'Maintenance Alert',
            'badge' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
            'banner' => 'bg-amber-500/10 border-amber-500/30 text-amber-700 dark:text-amber-300',
            'icon' => 'alert-triangle',
        ],
        'urgent' => [
            'label' => 'Urgent Notice',
            'badge' => 'bg-rose-500/10 text-rose-500 border-rose-500/20',
            'banner' => 'bg-rose-500/10 border-rose-500/30 text-rose-700 dark:text-rose-300',
            'icon' => 'alert-octagon',
        ],
    ];

    public static function getAllPublished(string $targetRole = 'all'): array
    {
        $pdo = Database::getConnection();
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("
            SELECT a.*, u.name as creator_name
            FROM announcements a
            LEFT JOIN users u ON u.id = a.created_by
            WHERE a.is_published = 1
              AND (a.starts_at IS NULL OR a.starts_at <= :now)
              AND (a.expires_at IS NULL OR a.expires_at >= :now2)
              AND (a.target_role = 'all' OR a.target_role = :role)
            ORDER BY a.id DESC
        ");
        $stmt->execute(['now' => $now, 'now2' => $now, 'role' => $targetRole]);
        return $stmt->fetchAll();
    }

    public static function getAllAdmin(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, u.name as creator_name
            FROM announcements a
            LEFT JOIN users u ON u.id = a.created_by
            ORDER BY a.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO announcements (title, content, type, target_role, is_published, starts_at, expires_at, created_by)
            VALUES (:title, :content, :type, :target_role, :is_published, :starts_at, :expires_at, :created_by)
        ");
        $stmt->execute([
            'title' => trim($data['title']),
            'content' => trim($data['content']),
            'type' => $data['type'] ?? 'info',
            'target_role' => $data['target_role'] ?? 'all',
            'is_published' => isset($data['is_published']) ? (int) $data['is_published'] : 1,
            'starts_at' => !empty($data['starts_at']) ? $data['starts_at'] : date('Y-m-d H:i:s'),
            'expires_at' => !empty($data['expires_at']) ? $data['expires_at'] : null,
            'created_by' => !empty($data['created_by']) ? (int) $data['created_by'] : null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['title'])) {
            $fields[] = 'title = :title';
            $params['title'] = trim($data['title']);
        }
        if (isset($data['content'])) {
            $fields[] = 'content = :content';
            $params['content'] = trim($data['content']);
        }
        if (isset($data['type'])) {
            $fields[] = 'type = :type';
            $params['type'] = $data['type'];
        }
        if (isset($data['target_role'])) {
            $fields[] = 'target_role = :target_role';
            $params['target_role'] = $data['target_role'];
        }
        if (isset($data['is_published'])) {
            $fields[] = 'is_published = :is_published';
            $params['is_published'] = (int) $data['is_published'];
        }
        if (isset($data['expires_at'])) {
            $fields[] = 'expires_at = :expires_at';
            $params['expires_at'] = !empty($data['expires_at']) ? $data['expires_at'] : null;
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE announcements SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
