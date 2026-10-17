<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Exam
{
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO exams (subject_id, title, term, max_marks, pass_marks, exam_date)
            VALUES (:subject_id, :title, :term, :max_marks, :pass_marks, :exam_date)
        ");
        $stmt->execute([
            'subject_id' => (int) $data['subject_id'],
            'title' => trim($data['title']),
            'term' => trim($data['term'] ?? 'Term 1'),
            'max_marks' => (float) ($data['max_marks'] ?? 100.0),
            'pass_marks' => (float) ($data['pass_marks'] ?? 40.0),
            'exam_date' => !empty($data['exam_date']) ? $data['exam_date'] : date('Y-m-d'),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT e.*, 
                   sub.name as subject_name, sub.code as subject_code,
                   c.id as class_id, c.name as class_name, c.section as class_section,
                   c.teacher_id
            FROM exams e
            JOIN subjects sub ON sub.id = e.subject_id
            JOIN classes c ON c.id = sub.class_id
            WHERE e.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getByTeacher(int $teacherId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT e.*, 
                   sub.name as subject_name,
                   c.id as class_id, c.name as class_name, c.section as class_section,
                   COUNT(m.id) as graded_count,
                   SUM(CASE WHEN m.marks_obtained >= e.pass_marks THEN 1 ELSE 0 END) as pass_count,
                   AVG(m.marks_obtained) as avg_score
            FROM exams e
            JOIN subjects sub ON sub.id = e.subject_id
            JOIN classes c ON c.id = sub.class_id
            LEFT JOIN marks m ON m.exam_id = e.id
            WHERE c.teacher_id = :teacher_id
            GROUP BY e.id
            ORDER BY e.exam_date DESC, e.id DESC
        ");
        $stmt->execute(['teacher_id' => $teacherId]);
        return $stmt->fetchAll();
    }

    public static function getByClass(int $classId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT e.*, sub.name as subject_name, sub.code as subject_code
            FROM exams e
            JOIN subjects sub ON sub.id = e.subject_id
            WHERE sub.class_id = :class_id
            ORDER BY e.exam_date DESC
        ");
        $stmt->execute(['class_id' => $classId]);
        return $stmt->fetchAll();
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM exams WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
