<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Config;
use App\Core\Database;
use PDO;

class Attendance
{
    public static function getByClassAndDate(int $classId, string $date): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, s.student_admin_id, s.first_name, s.last_name, s.primary_phone
            FROM attendance a
            JOIN students s ON s.id = a.student_id
            WHERE a.class_id = :class_id AND a.attendance_date = :date
            ORDER BY s.first_name ASC
        ");
        $stmt->execute(['class_id' => $classId, 'date' => $date]);
        return $stmt->fetchAll();
    }

    public static function record(int $studentId, int $classId, string $date, string $status, ?string $remarks = null): bool
    {
        return self::recordBatch($classId, $date, [
            $studentId => ['status' => $status, 'remarks' => $remarks]
        ]);
    }

    public static function recordBatch(int $classId, string $date, array $records): bool
    {
        $pdo = Database::getConnection();
        $dbType = Config::get('DB_CONNECTION', 'sqlite');

        $pdo->beginTransaction();
        try {
            foreach ($records as $studentId => $item) {
                $status = $item['status'] ?? 'P';
                $remarks = !empty($item['remarks']) ? trim($item['remarks']) : null;

                if ($dbType === 'sqlite') {
                    $stmt = $pdo->prepare("
                        INSERT INTO attendance (student_id, class_id, attendance_date, status, remarks)
                        VALUES (:student_id, :class_id, :attendance_date, :status, :remarks)
                        ON CONFLICT(student_id, attendance_date) 
                        DO UPDATE SET status = :status_up, remarks = :remarks_up
                    ");
                    $stmt->execute([
                        'student_id' => (int) $studentId,
                        'class_id' => $classId,
                        'attendance_date' => $date,
                        'status' => $status,
                        'remarks' => $remarks,
                        'status_up' => $status,
                        'remarks_up' => $remarks,
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO attendance (student_id, class_id, attendance_date, status, remarks)
                        VALUES (:student_id, :class_id, :attendance_date, :status, :remarks)
                        ON DUPLICATE KEY UPDATE status = VALUES(status), remarks = VALUES(remarks)
                    ");
                    $stmt->execute([
                        'student_id' => (int) $studentId,
                        'class_id' => $classId,
                        'attendance_date' => $date,
                        'status' => $status,
                        'remarks' => $remarks,
                    ]);
                }
            }
            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function getStudentHistory(int $studentId, int $limit = 30): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, c.name as class_name, c.section as class_section
            FROM attendance a
            JOIN classes c ON c.id = a.class_id
            WHERE a.student_id = :student_id
            ORDER BY a.attendance_date DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getRecentSummary(int $classId, int $days = 7): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT attendance_date,
                   COUNT(*) as total_marked,
                   SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as present_count,
                   SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as absent_count,
                   SUM(CASE WHEN status = 'L' THEN 1 ELSE 0 END) as late_count
            FROM attendance
            WHERE class_id = :class_id
            GROUP BY attendance_date
            ORDER BY attendance_date DESC
            LIMIT :days
        ");
        $stmt->bindValue(':class_id', $classId, PDO::PARAM_INT);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getTodayStats(int $teacherId): array
    {
        $pdo = Database::getConnection();
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_marked,
                SUM(CASE WHEN a.status = 'P' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN a.status = 'A' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN a.status = 'L' THEN 1 ELSE 0 END) as late
            FROM attendance a
            JOIN classes c ON c.id = a.class_id
            WHERE c.teacher_id = :teacher_id AND a.attendance_date = :today
        ");
        $stmt->execute(['teacher_id' => $teacherId, 'today' => $today]);
        $res = $stmt->fetch() ?: [];

        $total = (int) ($res['total_marked'] ?? 0);
        $present = (int) ($res['present'] ?? 0);
        $rate = ($total > 0) ? round(($present / $total) * 100, 1) : 0.0;

        return [
            'total' => $total,
            'present' => $present,
            'absent' => (int) ($res['absent'] ?? 0),
            'late' => (int) ($res['late'] ?? 0),
            'rate' => $rate,
        ];
    }
}
