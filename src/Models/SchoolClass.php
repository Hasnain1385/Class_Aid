<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class SchoolClass
{
    public static function getAllByTeacher(int $teacherId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.*, 
                   COUNT(DISTINCT s.id) as student_count,
                   COUNT(DISTINCT sub.id) as subject_count
            FROM classes c
            LEFT JOIN students s ON s.class_id = c.id
            LEFT JOIN subjects sub ON sub.class_id = c.id
            WHERE c.teacher_id = :teacher_id
            GROUP BY c.id
            ORDER BY c.is_home_class DESC, c.name ASC, c.section ASC
        ");
        $stmt->execute(['teacher_id' => $teacherId]);
        return $stmt->fetchAll();
    }

    public static function getHomeClass(int $teacherId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM classes 
            WHERE teacher_id = :teacher_id AND is_home_class = 1 
            LIMIT 1
        ");
        $stmt->execute(['teacher_id' => $teacherId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.*, u.name as teacher_name 
            FROM classes c
            JOIN users u ON u.id = c.teacher_id
            WHERE c.id = :id 
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO classes (teacher_id, name, section, academic_year, is_home_class)
            VALUES (:teacher_id, :name, :section, :academic_year, :is_home_class)
        ");
        $stmt->execute([
            'teacher_id' => (int) $data['teacher_id'],
            'name' => trim($data['name']),
            'section' => trim($data['section']),
            'academic_year' => trim($data['academic_year'] ?? date('Y') . '-' . (date('Y') + 1)),
            'is_home_class' => !empty($data['is_home_class']) ? 1 : 0,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE classes 
            SET name = :name, section = :section, academic_year = :academic_year, is_home_class = :is_home_class
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'name' => trim($data['name']),
            'section' => trim($data['section']),
            'academic_year' => trim($data['academic_year']),
            'is_home_class' => !empty($data['is_home_class']) ? 1 : 0,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            // Get student IDs belonging to this class
            $stmt = $pdo->prepare("SELECT id FROM students WHERE class_id = :class_id");
            $stmt->execute(['class_id' => $id]);
            $studentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($studentIds)) {
                $inClause = implode(',', array_map('intval', $studentIds));
                $pdo->exec("DELETE FROM attendance WHERE student_id IN ({$inClause})");
                $pdo->exec("DELETE FROM parent_call_logs WHERE student_id IN ({$inClause})");
                $pdo->exec("DELETE FROM marks WHERE student_id IN ({$inClause})");
                $pdo->exec("DELETE FROM students WHERE id IN ({$inClause})");
            }

            $pdo->prepare("DELETE FROM attendance WHERE class_id = :id")->execute(['id' => $id]);
            $pdo->prepare("DELETE FROM subjects WHERE class_id = :id")->execute(['id' => $id]);
            $del = $pdo->prepare("DELETE FROM classes WHERE id = :id")->execute(['id' => $id]);

            $pdo->commit();
            return $del;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
