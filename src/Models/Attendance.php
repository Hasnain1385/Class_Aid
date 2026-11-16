<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Config;
use App\Core\Database;
use PDO;

class Attendance
{
    /**
     * Check if the session is valid for the given date.
     * Monday through Thursday and weekend have 2 roll calls: 'morning' and 'midday'.
     * Friday has only 1 roll call: 'morning'.
     */
    public static function isValidSessionForDate(string $date, string $sessionType): bool
    {
        $dayOfWeek = (int) date('N', strtotime($date));
        if ($dayOfWeek === 5 && $sessionType === 'midday') {
            return false;
        }
        return in_array($sessionType, ['morning', 'midday'], true);
    }

    /**
     * Normalize session type: enforces 'morning' on Fridays, otherwise ensures 'morning' or 'midday'.
     */
    public static function normalizeSessionType(string $date, ?string $sessionType): string
    {
        $dayOfWeek = (int) date('N', strtotime($date));
        if ($dayOfWeek === 5) {
            return 'morning';
        }
        return in_array($sessionType, ['morning', 'midday'], true) ? $sessionType : 'morning';
    }

    public static function getByClassAndDate(int $classId, string $date, string $sessionType = 'morning'): array
    {
        $sessionType = self::normalizeSessionType($date, $sessionType);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, s.student_admin_id, s.first_name, s.last_name, s.primary_phone
            FROM attendance a
            JOIN students s ON s.id = a.student_id
            WHERE a.class_id = :class_id 
              AND a.attendance_date = :date
              AND (a.session_type = :session_type OR a.session_type IS NULL)
            ORDER BY s.first_name ASC
        ");
        $stmt->execute([
            'class_id' => $classId,
            'date' => $date,
            'session_type' => $sessionType,
        ]);
        return $stmt->fetchAll();
    }

    public static function getDailySessionStatus(int $classId, string $date): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT session_type, COUNT(*) as count
            FROM attendance
            WHERE class_id = :class_id AND attendance_date = :date
            GROUP BY session_type
        ");
        $stmt->execute(['class_id' => $classId, 'date' => $date]);
        $rows = $stmt->fetchAll();
        $status = ['morning' => 0, 'midday' => 0];
        foreach ($rows as $r) {
            $sType = $r['session_type'] ?: 'morning';
            $status[$sType] = (int) $r['count'];
        }
        $isFriday = ((int) date('N', strtotime($date)) === 5);
        return [
            'is_friday' => $isFriday,
            'morning_recorded' => $status['morning'] > 0,
            'morning_count' => $status['morning'],
            'midday_recorded' => $isFriday ? false : ($status['midday'] > 0),
            'midday_count' => $status['midday'],
            'is_fully_recorded' => $isFriday ? ($status['morning'] > 0) : ($status['morning'] > 0 && $status['midday'] > 0),
        ];
    }

    public static function record(int $studentId, int $classId, string $date, string $status, ?string $remarks = null, string $sessionType = 'morning'): bool
    {
        return self::recordBatch($classId, $date, [
            $studentId => ['status' => $status, 'remarks' => $remarks]
        ], $sessionType);
    }

    public static function recordBatch(int $classId, string $date, array $records, string $sessionType = 'morning'): bool
    {
        $sessionType = self::normalizeSessionType($date, $sessionType);
        $pdo = Database::getConnection();
        $dbType = Config::get('DB_CONNECTION', 'sqlite');

        $pdo->beginTransaction();
        try {
            foreach ($records as $studentId => $item) {
                $status = $item['status'] ?? 'P';
                $remarks = !empty($item['remarks']) ? trim($item['remarks']) : null;

                if ($dbType === 'sqlite') {
                    $stmt = $pdo->prepare("
                        INSERT INTO attendance (student_id, class_id, attendance_date, session_type, status, remarks)
                        VALUES (:student_id, :class_id, :attendance_date, :session_type, :status, :remarks)
                        ON CONFLICT(student_id, attendance_date, session_type) 
                        DO UPDATE SET status = :status_up, remarks = :remarks_up
                    ");
                    $stmt->execute([
                        'student_id' => (int) $studentId,
                        'class_id' => $classId,
                        'attendance_date' => $date,
                        'session_type' => $sessionType,
                        'status' => $status,
                        'remarks' => $remarks,
                        'status_up' => $status,
                        'remarks_up' => $remarks,
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO attendance (student_id, class_id, attendance_date, session_type, status, remarks)
                        VALUES (:student_id, :class_id, :attendance_date, :session_type, :status, :remarks)
                        ON DUPLICATE KEY UPDATE status = VALUES(status), remarks = VALUES(remarks)
                    ");
                    $stmt->execute([
                        'student_id' => (int) $studentId,
                        'class_id' => $classId,
                        'attendance_date' => $date,
                        'session_type' => $sessionType,
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

    public static function getCustomReport(int $classId, ?string $dateFrom = null, ?string $dateTo = null, array $studentIds = []): array
    {
        $pdo = Database::getConnection();

        $dateFrom = !empty($dateFrom) ? $dateFrom : date('Y-m-01');
        $dateTo = !empty($dateTo) ? $dateTo : date('Y-m-d');

        // Fetch distinct dates attendance was recorded for this class in range
        $stmtDates = $pdo->prepare("
            SELECT DISTINCT attendance_date 
            FROM attendance 
            WHERE class_id = :class_id 
              AND attendance_date BETWEEN :d_from AND :d_to
            ORDER BY attendance_date ASC
        ");
        $stmtDates->execute([
            'class_id' => $classId,
            'd_from' => $dateFrom,
            'd_to' => $dateTo,
        ]);
        $dates = $stmtDates->fetchAll(PDO::FETCH_COLUMN) ?: [];

        // Build students query
        $studentSql = "SELECT s.* FROM students s WHERE s.class_id = :class_id";
        $studentParams = ['class_id' => $classId];

        if (!empty($studentIds)) {
            $sanitizedIds = array_filter(array_map('intval', $studentIds));
            if (!empty($sanitizedIds)) {
                $placeholders = implode(',', $sanitizedIds);
                $studentSql .= " AND s.id IN ({$placeholders})";
            }
        }
        $studentSql .= " ORDER BY s.first_name ASC, s.last_name ASC";

        $stmtStudents = $pdo->prepare($studentSql);
        $stmtStudents->execute($studentParams);
        $students = $stmtStudents->fetchAll();

        // Fetch all attendance rows for this class in date range
        $attSql = "
            SELECT student_id, attendance_date, session_type, status, remarks 
            FROM attendance 
            WHERE class_id = :class_id 
              AND attendance_date BETWEEN :d_from AND :d_to
        ";
        $attStmt = $pdo->prepare($attSql);
        $attStmt->execute([
            'class_id' => $classId,
            'd_from' => $dateFrom,
            'd_to' => $dateTo,
        ]);
        $rawRecords = $attStmt->fetchAll();

        // Map records by [student_id][attendance_date] => row
        $recordsMap = [];
        foreach ($rawRecords as $r) {
            $sId = (int) $r['student_id'];
            $d = $r['attendance_date'];
            $sType = $r['session_type'] ?: 'morning';
            $recordsMap[$sId][$d][$sType] = $r;
            if (!isset($recordsMap[$sId][$d]['status']) || $sType === 'morning') {
                $recordsMap[$sId][$d] = $r;
            }
        }

        $summary = [
            'total_students' => count($students),
            'total_sessions_taken' => count($dates),
            'total_p' => 0,
            'total_a' => 0,
            'total_l' => 0,
            'total_e' => 0,
            'chronic_count' => 0,
        ];

        $studentIds = array_column($students, 'id');
        $consecutiveMap = Student::getBatchConsecutiveAbsences($studentIds);

        foreach ($students as &$stu) {
            $sId = (int) $stu['id'];
            $stuDates = $recordsMap[$sId] ?? [];
            
            $p = 0; $a = 0; $l = 0; $e = 0;
            foreach ($stuDates as $rec) {
                $st = strtoupper((string) ($rec['status'] ?? ''));
                if ($st === 'P') $p++;
                elseif ($st === 'A') $a++;
                elseif ($st === 'L') $l++;
                elseif ($st === 'E') $e++;
            }

            $totalSessions = $p + $a + $l + $e;
            $rate = ($totalSessions > 0) ? round(($p / $totalSessions) * 100, 1) : 0.0;
            $consecutive = $consecutiveMap[$sId] ?? 0;

            $stu['present_count'] = $p;
            $stu['absent_count'] = $a;
            $stu['late_count'] = $l;
            $stu['excused_count'] = $e;
            $stu['total_sessions'] = $totalSessions;
            $stu['attendance_rate'] = $rate;
            $stu['consecutive_absences'] = $consecutive;
            $stu['date_map'] = $stuDates;
            $stu['is_chronic'] = ($rate < 85.0 && $totalSessions >= 3) || ($a >= 3);

            $summary['total_p'] += $p;
            $summary['total_a'] += $a;
            $summary['total_l'] += $l;
            $summary['total_e'] += $e;
            if ($stu['is_chronic']) {
                $summary['chronic_count']++;
            }
        }
        unset($stu);

        $totalMarks = $summary['total_p'] + $summary['total_a'] + $summary['total_l'] + $summary['total_e'];
        $summary['overall_rate'] = ($totalMarks > 0) ? round(($summary['total_p'] / $totalMarks) * 100, 1) : 0.0;

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'dates' => $dates,
            'students' => $students,
            'summary' => $summary,
        ];
    }
}
