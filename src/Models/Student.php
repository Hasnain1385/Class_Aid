<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Student
{
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT s.*, 
                   c.name as class_name, 
                   c.section as class_section,
                   c.is_home_class,
                   c.teacher_id
            FROM students s
            JOIN classes c ON c.id = s.class_id
            WHERE s.id = :id 
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByAdminId(string $adminId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT s.*, 
                   c.name as class_name, 
                   c.section as class_section,
                   c.is_home_class,
                   c.teacher_id
            FROM students s
            JOIN classes c ON c.id = s.class_id
            WHERE LOWER(s.student_admin_id) = LOWER(:admin_id)
            LIMIT 1
        ");
        $stmt->execute(['admin_id' => trim($adminId)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getByClass(int $classId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT s.*, c.name as class_name, c.section as class_section
            FROM students s
            JOIN classes c ON c.id = s.class_id
            WHERE s.class_id = :class_id
            ORDER BY s.first_name ASC, s.last_name ASC
        ");
        $stmt->execute(['class_id' => $classId]);
        return $stmt->fetchAll();
    }

    public static function getAll(int $teacherId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT s.*, c.name as class_name, c.section as class_section, c.is_home_class
            FROM students s
            JOIN classes c ON c.id = s.class_id
            WHERE c.teacher_id = :teacher_id
            ORDER BY c.is_home_class DESC, c.name ASC, s.first_name ASC
        ");
        $stmt->execute(['teacher_id' => $teacherId]);
        return $stmt->fetchAll();
    }

    public static function search(string $term, ?int $classId = null): array
    {
        $pdo = Database::getConnection();
        $term = '%' . trim($term) . '%';

        $sql = "
            SELECT s.*, c.name as class_name, c.section as class_section
            FROM students s
            JOIN classes c ON c.id = s.class_id
            WHERE (
                s.student_admin_id LIKE :term1 
                OR s.first_name LIKE :term2 
                OR s.last_name LIKE :term3 
                OR (s.first_name || ' ' || s.last_name) LIKE :term4
                OR s.primary_phone LIKE :term5
            )
        ";

        $params = [
            'term1' => $term,
            'term2' => $term,
            'term3' => $term,
            'term4' => $term,
            'term5' => $term,
        ];

        if ($classId !== null) {
            $sql .= " AND s.class_id = :class_id";
            $params['class_id'] = $classId;
        }

        $sql .= " ORDER BY s.first_name ASC LIMIT 20";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO students (
                student_admin_id, class_id, first_name, last_name, gender, dob,
                primary_guardian_name, primary_guardian_relation, primary_phone,
                secondary_phone, guardian_email, address, status
            ) VALUES (
                :student_admin_id, :class_id, :first_name, :last_name, :gender, :dob,
                :primary_guardian_name, :primary_guardian_relation, :primary_phone,
                :secondary_phone, :guardian_email, :address, :status
            )
        ");

        $stmt->execute([
            'student_admin_id' => trim($data['student_admin_id']),
            'class_id' => (int) $data['class_id'],
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'gender' => !empty($data['gender']) ? $data['gender'] : null,
            'dob' => !empty($data['dob']) ? $data['dob'] : null,
            'primary_guardian_name' => trim($data['primary_guardian_name']),
            'primary_guardian_relation' => trim($data['primary_guardian_relation'] ?? 'Parent'),
            'primary_phone' => trim($data['primary_phone']),
            'secondary_phone' => !empty($data['secondary_phone']) ? trim($data['secondary_phone']) : null,
            'guardian_email' => !empty($data['guardian_email']) ? trim($data['guardian_email']) : null,
            'address' => !empty($data['address']) ? trim($data['address']) : null,
            'status' => $data['status'] ?? 'active',
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE students SET
                student_admin_id = :student_admin_id,
                class_id = :class_id,
                first_name = :first_name,
                last_name = :last_name,
                gender = :gender,
                dob = :dob,
                primary_guardian_name = :primary_guardian_name,
                primary_guardian_relation = :primary_guardian_relation,
                primary_phone = :primary_phone,
                secondary_phone = :secondary_phone,
                guardian_email = :guardian_email,
                address = :address,
                status = :status
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'student_admin_id' => trim($data['student_admin_id']),
            'class_id' => (int) $data['class_id'],
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'gender' => !empty($data['gender']) ? $data['gender'] : null,
            'dob' => !empty($data['dob']) ? $data['dob'] : null,
            'primary_guardian_name' => trim($data['primary_guardian_name']),
            'primary_guardian_relation' => trim($data['primary_guardian_relation'] ?? 'Parent'),
            'primary_phone' => trim($data['primary_phone']),
            'secondary_phone' => !empty($data['secondary_phone']) ? trim($data['secondary_phone']) : null,
            'guardian_email' => !empty($data['guardian_email']) ? trim($data['guardian_email']) : null,
            'address' => !empty($data['address']) ? trim($data['address']) : null,
            'status' => $data['status'] ?? 'active',
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function getConsecutiveAbsences(int $studentId): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT status FROM attendance 
            WHERE student_id = :student_id 
            ORDER BY attendance_date DESC 
            LIMIT 10
        ");
        $stmt->execute(['student_id' => $studentId]);
        $rows = $stmt->fetchAll();

        $consecutive = 0;
        foreach ($rows as $row) {
            if ($row['status'] === 'A') {
                $consecutive++;
            } else {
                break;
            }
        }
        return $consecutive;
    }

    public static function getAttendanceStats(int $studentId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = 'L' THEN 1 ELSE 0 END) as late_days,
                SUM(CASE WHEN status = 'E' THEN 1 ELSE 0 END) as excused_days
            FROM attendance 
            WHERE student_id = :student_id
        ");
        $stmt->execute(['student_id' => $studentId]);
        $stats = $stmt->fetch() ?: [];

        $total = (int) ($stats['total_days'] ?? 0);
        $present = (int) ($stats['present_days'] ?? 0);
        $late = (int) ($stats['late_days'] ?? 0);
        $percentage = ($total > 0) ? round((($present + ($late * 0.5)) / $total) * 100, 1) : 100.0;

        return [
            'total' => $total,
            'total_days' => $total,
            'present' => $present,
            'present_days' => $present,
            'absent' => (int) ($stats['absent_days'] ?? 0),
            'absent_days' => (int) ($stats['absent_days'] ?? 0),
            'late' => $late,
            'late_days' => $late,
            'excused' => (int) ($stats['excused_days'] ?? 0),
            'excused_days' => (int) ($stats['excused_days'] ?? 0),
            'percentage' => $percentage,
            'consecutive_absences' => self::getConsecutiveAbsences($studentId),
        ];
    }
}
