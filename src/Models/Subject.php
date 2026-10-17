<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Subject
{
    public static function getByClass(int $classId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM subjects WHERE class_id = :class_id ORDER BY name ASC");
        $stmt->execute(['class_id' => $classId]);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO subjects (class_id, name, code)
            VALUES (:class_id, :name, :code)
        ");
        $stmt->execute([
            'class_id' => (int) $data['class_id'],
            'name' => trim($data['name']),
            'code' => !empty($data['code']) ? strtoupper(trim($data['code'])) : null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
