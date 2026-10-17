<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Config;
use App\Core\Database;
use App\Utils\GradeCalculator;
use PDO;

class Mark
{
    public static function recordBatch(int $examId, float $maxMarks, float $passMarks, array $studentMarks): bool
    {
        $pdo = Database::getConnection();
        $dbType = Config::get('DB_CONNECTION', 'sqlite');

        $pdo->beginTransaction();
        try {
            foreach ($studentMarks as $studentId => $item) {
                $rawScore = $item['score'] ?? $item['marks'] ?? null;
                if ($rawScore === null || $rawScore === '') {
                    continue;
                }

                $marks = (float) $rawScore;
                $remarks = !empty($item['remarks']) ? trim($item['remarks']) : null;
                $calc = GradeCalculator::calculate($marks, $maxMarks, $passMarks);

                if ($dbType === 'sqlite') {
                    $stmt = $pdo->prepare("
                        INSERT INTO marks (exam_id, student_id, marks_obtained, percentage, grade, remarks)
                        VALUES (:exam_id, :student_id, :marks_obtained, :percentage, :grade, :remarks)
                        ON CONFLICT(exam_id, student_id) 
                        DO UPDATE SET marks_obtained = :marks_up, percentage = :percentage_up, grade = :grade_up, remarks = :remarks_up
                    ");
                    $stmt->execute([
                        'exam_id' => $examId,
                        'student_id' => (int) $studentId,
                        'marks_obtained' => $calc['marks_obtained'],
                        'percentage' => $calc['percentage'],
                        'grade' => $calc['grade'],
                        'remarks' => $remarks,
                        'marks_up' => $calc['marks_obtained'],
                        'percentage_up' => $calc['percentage'],
                        'grade_up' => $calc['grade'],
                        'remarks_up' => $remarks,
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO marks (exam_id, student_id, marks_obtained, percentage, grade, remarks)
                        VALUES (:exam_id, :student_id, :marks_obtained, :percentage, :grade, :remarks)
                        ON DUPLICATE KEY UPDATE 
                            marks_obtained = VALUES(marks_obtained), 
                            percentage = VALUES(percentage), 
                            grade = VALUES(grade), 
                            remarks = VALUES(remarks)
                    ");
                    $stmt->execute([
                        'exam_id' => $examId,
                        'student_id' => (int) $studentId,
                        'marks_obtained' => $calc['marks_obtained'],
                        'percentage' => $calc['percentage'],
                        'grade' => $calc['grade'],
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

    public static function getByExam(int $examId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT m.*, m.grade as letter_grade, s.student_admin_id, s.first_name, s.last_name
            FROM marks m
            JOIN students s ON s.id = m.student_id
            WHERE m.exam_id = :exam_id
            ORDER BY m.marks_obtained DESC, s.first_name ASC
        ");
        $stmt->execute(['exam_id' => $examId]);
        return $stmt->fetchAll();
    }

    public static function getByStudent(int $studentId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT m.*, m.grade as letter_grade,
                   (m.marks_obtained >= e.pass_marks) as is_passed,
                   e.title as exam_title, e.max_marks, e.pass_marks, e.exam_date, e.term,
                   sub.name as subject_name, sub.code as subject_code
            FROM marks m
            JOIN exams e ON e.id = m.exam_id
            JOIN subjects sub ON sub.id = e.subject_id
            WHERE m.student_id = :student_id
            ORDER BY e.exam_date DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    public static function getExamAnalytics(int $examId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_students,
                MAX(marks_obtained) as highest_mark,
                MIN(marks_obtained) as lowest_mark,
                AVG(marks_obtained) as average_mark,
                AVG(percentage) as average_percentage
            FROM marks 
            WHERE exam_id = :exam_id
        ");
        $stmt->execute(['exam_id' => $examId]);
        $row = $stmt->fetch() ?: [];

        return [
            'total_students' => (int) ($row['total_students'] ?? 0),
            'highest_mark' => $row['highest_mark'] !== null ? (float) $row['highest_mark'] : 0.0,
            'lowest_mark' => $row['lowest_mark'] !== null ? (float) $row['lowest_mark'] : 0.0,
            'average_mark' => $row['average_mark'] !== null ? round((float) $row['average_mark'], 2) : 0.0,
            'average_percentage' => $row['average_percentage'] !== null ? round((float) $row['average_percentage'], 2) : 0.0,
        ];
    }
}
