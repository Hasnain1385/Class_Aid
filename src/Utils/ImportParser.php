<?php

declare(strict_types=1);

namespace App\Utils;

use App\Models\Student;
use App\Utils\GradeCalculator;

class ImportParser
{
    /**
     * Splits raw text or CSV into rows and columns with smart delimiter detection.
     */
    public static function parseRowsAndCols(string $rawText): array
    {
        $rawText = trim($rawText);
        if (empty($rawText)) {
            return [];
        }

        // Normalize newlines
        $rawText = str_replace(["\r\n", "\r"], "\n", $rawText);
        $lines = explode("\n", $rawText);
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            // Detect delimiter: pipe, tab, semicolon, comma, or multi-space
            $cols = [];
            if (str_contains($line, '|')) {
                $cols = array_map('trim', explode('|', $line));
            } elseif (str_contains($line, "\t")) {
                $cols = array_map('trim', explode("\t", $line));
            } elseif (str_contains($line, ';')) {
                $cols = array_map('trim', str_getcsv($line, ';', '"', '\\'));
            } elseif (str_contains($line, ',')) {
                $cols = array_map('trim', str_getcsv($line, ',', '"', '\\'));
            } else {
                // Multi-space separated (common in OCR tables or formatted text)
                $cols = array_values(array_filter(preg_split('/\s{2,}|\t/', $line), fn($c) => trim($c) !== ''));
                if (count($cols) <= 1) {
                    // Try splitting by single space if columns couldn't be found
                    $cols = array_values(array_filter(explode(' ', $line), fn($c) => trim($c) !== ''));
                }
            }

            if (!empty($cols)) {
                $rows[] = $cols;
            }
        }

        return $rows;
    }

    /**
     * Parses student roster data from CSV/OCR rows.
     */
    public static function parseStudents(string $rawText, int $classId = 0): array
    {
        $rows = self::parseRowsAndCols($rawText);
        if (empty($rows)) {
            return [];
        }

        $parsedStudents = [];
        $headerMap = null;

        // Check if first row is a header
        $firstRow = $rows[0];
        $detectedHeader = self::detectStudentHeaderMap($firstRow);
        $startIndex = 0;

        if ($detectedHeader !== null) {
            $headerMap = $detectedHeader;
            $startIndex = 1;
        }

        $counter = 1;
        for ($i = $startIndex; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row)) {
                continue;
            }

            $adminId = '';
            $firstName = '';
            $lastName = '';
            $gender = 'Other';
            $dob = '';
            $guardianName = 'Parent/Guardian';
            $guardianRelation = 'Parent';
            $phone = '';
            $email = '';
            $address = '';

            if ($headerMap !== null) {
                // Mapped by header columns
                $adminId = trim((string)($row[$headerMap['admin_id']] ?? ''));
                
                if (isset($headerMap['name'])) {
                    $fullName = trim((string)($row[$headerMap['name']] ?? ''));
                    [$firstName, $lastName] = self::splitName($fullName);
                } else {
                    $firstName = trim((string)($row[$headerMap['first_name']] ?? ''));
                    $lastName = trim((string)($row[$headerMap['last_name']] ?? ''));
                }

                if (isset($headerMap['gender'])) {
                    $gender = self::normalizeGender((string)($row[$headerMap['gender']] ?? ''));
                }
                if (isset($headerMap['phone'])) {
                    $phone = trim((string)($row[$headerMap['phone']] ?? ''));
                }
                if (isset($headerMap['guardian'])) {
                    $g = trim((string)($row[$headerMap['guardian']] ?? ''));
                    if (!empty($g)) {
                        $guardianName = $g;
                    }
                }
                if (isset($headerMap['relation'])) {
                    $r = trim((string)($row[$headerMap['relation']] ?? ''));
                    if (!empty($r)) {
                        $guardianRelation = $r;
                    }
                }
                if (isset($headerMap['dob'])) {
                    $dob = trim((string)($row[$headerMap['dob']] ?? ''));
                }
                if (isset($headerMap['address'])) {
                    $address = trim((string)($row[$headerMap['address']] ?? ''));
                }
            } else {
                // Heuristic mapping without headers
                $extracted = self::extractStudentFieldsHeuristic($row);
                $adminId = $extracted['admin_id'];
                $firstName = $extracted['first_name'];
                $lastName = $extracted['last_name'];
                $gender = $extracted['gender'];
                $phone = $extracted['phone'];
                $guardianName = $extracted['guardian_name'] ?: 'Parent/Guardian';
            }

            // Auto-generate admin ID if missing
            if (empty($adminId)) {
                $adminId = 'STU-' . date('Y') . '-' . str_pad((string)$counter, 3, '0', STR_PAD_LEFT);
            }
            $counter++;

            // Ensure fallback first/last name
            if (empty($firstName) && empty($lastName)) {
                $firstName = 'Student';
                $lastName = '#' . $counter;
            } elseif (empty($lastName)) {
                $lastName = 'Student';
            } elseif (empty($firstName)) {
                $firstName = $lastName;
                $lastName = 'Student';
            }

            // Default placeholder phone if blank
            if (empty($phone)) {
                $phone = '000-000-0000';
            }

            // Check if admin ID already exists in DB
            $existing = Student::findByAdminId($adminId);
            $status = 'ready';
            $message = 'Valid';

            if ($existing) {
                $status = 'duplicate';
                $message = "Admin ID already assigned to {$existing['first_name']} {$existing['last_name']} in {$existing['class_name']}";
            }

            $parsedStudents[] = [
                'row_index' => $i,
                'student_admin_id' => $adminId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => $gender,
                'dob' => $dob,
                'primary_guardian_name' => $guardianName,
                'primary_guardian_relation' => $guardianRelation,
                'primary_phone' => $phone,
                'secondary_phone' => '',
                'guardian_email' => $email,
                'address' => $address,
                'class_id' => $classId,
                'status' => $status,
                'message' => $message,
            ];
        }

        return $parsedStudents;
    }

    /**
     * Parses marks list from raw CSV/OCR text and matches against class roster.
     */
    public static function parseMarks(string $rawText, int $examId, array $enrolledStudents, float $maxMarks, float $passMarks): array
    {
        $rows = self::parseRowsAndCols($rawText);
        if (empty($rows)) {
            return [];
        }

        // Build lookup tables for enrolled students
        $byId = [];
        $byName = [];

        foreach ($enrolledStudents as $stu) {
            $adminKey = strtolower(trim((string)$stu['student_admin_id']));
            $byId[$adminKey] = $stu;

            $fullName = strtolower(trim($stu['first_name'] . ' ' . $stu['last_name']));
            $byName[$fullName] = $stu;

            $reverseName = strtolower(trim($stu['last_name'] . ' ' . $stu['first_name']));
            $byName[$reverseName] = $stu;

            // Also map by individual names if unique
            $fName = strtolower(trim((string)$stu['first_name']));
            if (!isset($byName[$fName])) {
                $byName[$fName] = $stu;
            }
        }

        $detectedHeader = self::detectMarksHeaderMap($rows[0]);
        $startIndex = ($detectedHeader !== null) ? 1 : 0;
        $parsedMarks = [];

        for ($i = $startIndex; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row)) {
                continue;
            }

            $identifier = '';
            $scoreRaw = null;
            $remarks = '';

            if ($detectedHeader !== null) {
                $identifier = trim((string)($row[$detectedHeader['identifier']] ?? ''));
                $scoreRaw = $row[$detectedHeader['score']] ?? null;
                if (isset($detectedHeader['remarks'])) {
                    $remarks = trim((string)($row[$detectedHeader['remarks']] ?? ''));
                }
            } else {
                // Heuristic line parsing
                $lineStr = implode(' ', $row);
                $extracted = self::extractMarkRowHeuristic($row, $lineStr, $byId, $byName);
                $identifier = $extracted['identifier'];
                $scoreRaw = $extracted['score'];
                $remarks = $extracted['remarks'];
            }

            if ($scoreRaw === null || $scoreRaw === '') {
                continue;
            }

            $score = (float)$scoreRaw;
            $matchedStudent = self::findStudentMatch($identifier, $byId, $byName);

            $status = 'matched';
            $message = 'Matched successfully';

            if (!$matchedStudent) {
                $status = 'unmatched';
                $message = "Student '{$identifier}' not found in class roster";
            } elseif ($score < 0 || $score > $maxMarks) {
                $status = 'invalid_score';
                $message = "Score {$score} is out of bounds (0 - {$maxMarks})";
            }

            $calc = GradeCalculator::calculate($score, $maxMarks, $passMarks);

            $parsedMarks[] = [
                'row_index' => $i,
                'raw_identifier' => $identifier,
                'student_id' => $matchedStudent ? (int)$matchedStudent['id'] : null,
                'student_admin_id' => $matchedStudent ? $matchedStudent['student_admin_id'] : $identifier,
                'student_name' => $matchedStudent ? ($matchedStudent['first_name'] . ' ' . $matchedStudent['last_name']) : 'Unmatched Student',
                'score' => $score,
                'percentage' => $calc['percentage'],
                'grade' => $calc['grade'],
                'remarks' => $remarks,
                'status' => $status,
                'message' => $message,
            ];
        }

        return $parsedMarks;
    }

    private static function detectStudentHeaderMap(array $cols): ?array
    {
        $map = [];
        $foundKey = false;

        foreach ($cols as $idx => $col) {
            $header = strtolower(trim((string)$col));
            $header = preg_replace('/[^a-z0-9]/', '', $header);

            if (in_array($header, ['studentadminid', 'adminid', 'studentid', 'admissionno', 'regno', 'rollno', 'id', 'roll'])) {
                $map['admin_id'] = $idx;
                $foundKey = true;
            } elseif (in_array($header, ['name', 'fullname', 'studentname'])) {
                $map['name'] = $idx;
                $foundKey = true;
            } elseif (in_array($header, ['firstname', 'fname', 'first'])) {
                $map['first_name'] = $idx;
                $foundKey = true;
            } elseif (in_array($header, ['lastname', 'lname', 'last', 'surname'])) {
                $map['last_name'] = $idx;
                $foundKey = true;
            } elseif (in_array($header, ['gender', 'sex'])) {
                $map['gender'] = $idx;
            } elseif (in_array($header, ['phone', 'mobile', 'primaryphone', 'contact', 'cell', 'parentphone'])) {
                $map['phone'] = $idx;
            } elseif (in_array($header, ['guardian', 'guardianname', 'parent', 'primaryguardianname', 'father', 'mother'])) {
                $map['guardian'] = $idx;
            } elseif (in_array($header, ['relation', 'guardianrelation', 'relationship'])) {
                $map['relation'] = $idx;
            } elseif (in_array($header, ['dob', 'dateofbirth', 'birthdate'])) {
                $map['dob'] = $idx;
            } elseif (in_array($header, ['address', 'location'])) {
                $map['address'] = $idx;
            }
        }

        return $foundKey ? $map : null;
    }

    private static function detectMarksHeaderMap(array $cols): ?array
    {
        $map = [];
        $hasIdentifier = false;
        $hasScore = false;

        foreach ($cols as $idx => $col) {
            $header = strtolower(trim((string)$col));
            $header = preg_replace('/[^a-z0-9]/', '', $header);

            if (in_array($header, ['studentadminid', 'adminid', 'studentid', 'id', 'roll', 'rollno', 'name', 'student', 'studentname'])) {
                $map['identifier'] = $idx;
                $hasIdentifier = true;
            } elseif (in_array($header, ['score', 'mark', 'marks', 'marksobtained', 'points', 'result', 'grade_num'])) {
                $map['score'] = $idx;
                $hasScore = true;
            } elseif (in_array($header, ['remarks', 'comment', 'notes', 'feedback'])) {
                $map['remarks'] = $idx;
            }
        }

        return ($hasIdentifier && $hasScore) ? $map : null;
    }

    private static function splitName(string $fullName): array
    {
        $parts = array_values(array_filter(explode(' ', trim($fullName))));
        if (count($parts) === 0) {
            return ['Student', 'Name'];
        }
        if (count($parts) === 1) {
            return [$parts[0], 'Student'];
        }
        $firstName = array_shift($parts);
        $lastName = implode(' ', $parts);
        return [$firstName, $lastName];
    }

    private static function normalizeGender(string $raw): string
    {
        $raw = strtolower(trim($raw));
        if (in_array($raw, ['m', 'male', 'boy'])) {
            return 'Male';
        }
        if (in_array($raw, ['f', 'female', 'girl'])) {
            return 'Female';
        }
        return 'Other';
    }

    private static function extractStudentFieldsHeuristic(array $cols): array
    {
        $adminId = '';
        $phone = '';
        $gender = 'Other';
        $names = [];
        $guardian = '';

        foreach ($cols as $val) {
            $val = trim((string)$val);
            if (empty($val)) {
                continue;
            }

            // Check if phone number
            if (empty($phone) && preg_match('/^(\+?\d{1,4}[-.\s]?)?(\(?\d{3}\)?[-.\s]?)?[\d\s.-]{6,12}$/', $val)) {
                $phone = $val;
                continue;
            }

            // Check if gender
            if ($gender === 'Other' && in_array(strtolower($val), ['m', 'f', 'male', 'female', 'other'])) {
                $gender = self::normalizeGender($val);
                continue;
            }

            // Check if ID pattern (e.g. ADM-101, STU01, 1001, #12)
            if (empty($adminId) && (preg_match('/^[A-Z0-9#_-]{2,15}$/i', $val) && (preg_match('/\d/', $val) || str_contains($val, '-')))) {
                $adminId = $val;
                continue;
            }

            // Text names
            if (preg_match('/^[A-Za-z\s.\'-]+$/', $val)) {
                $names[] = $val;
            }
        }

        $firstName = '';
        $lastName = '';

        if (!empty($names)) {
            $studentNameRaw = array_shift($names);
            [$firstName, $lastName] = self::splitName($studentNameRaw);
            if (!empty($names)) {
                $guardian = implode(' ', $names);
            }
        }

        return [
            'admin_id' => $adminId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => $gender,
            'phone' => $phone,
            'guardian_name' => $guardian,
        ];
    }

    private static function extractMarkRowHeuristic(array $cols, string $lineStr, array $byId, array $byName): array
    {
        $identifier = '';
        $score = null;
        $remarks = '';

        // First pass: look for enrolled student admin IDs in the row
        foreach ($cols as $c) {
            $cleaned = strtolower(trim((string)$c));
            if (isset($byId[$cleaned])) {
                $identifier = $byId[$cleaned]['student_admin_id'];
                break;
            }
        }

        // Second pass: look for numeric score
        foreach ($cols as $idx => $c) {
            $c = trim((string)$c);
            // If it's a number (e.g. 85, 92.5, 70/100)
            if (preg_match('/^(\d+(\.\d+)?)\s*(\/\s*\d+)?$/', $c, $matches)) {
                $score = (float)$matches[1];
                unset($cols[$idx]);
                break;
            }
        }

        // If score not found by cell, regex search the whole line
        if ($score === null && preg_match('/(?:^|\s)(\d{1,3}(?:\.\d{1,2})?)(?:%|\/\d+)?(?:\s|$)/', $lineStr, $m)) {
            $score = (float)$m[1];
        }

        // If identifier still blank, look for name match or use first non-numeric column
        if (empty($identifier)) {
            foreach ($cols as $idx => $c) {
                $c = trim((string)$c);
                if (empty($c) || is_numeric($c)) {
                    continue;
                }
                $matched = self::findStudentMatch($c, $byId, $byName);
                if ($matched) {
                    $identifier = $matched['student_admin_id'];
                    unset($cols[$idx]);
                    break;
                }
            }
        }

        // Fallback identifier to first column
        if (empty($identifier) && !empty($cols)) {
            $identifier = array_shift($cols);
        }

        // Any leftover text becomes remarks
        if (!empty($cols)) {
            $rem = trim(implode(' ', $cols));
            if (!empty($rem) && !is_numeric($rem)) {
                $remarks = $rem;
            }
        }

        // Filter out title/header banners that don't match any enrolled student
        if ($score !== null) {
            $matched = self::findStudentMatch($identifier, $byId, $byName);
            if (!$matched && preg_match('/\b(results|examination|exam|grade\s*\d+|class\s*\d+|midterm|final|term\s*\d+|quiz|assessment|test\s*marks)\b/i', $lineStr)) {
                $score = null;
            }
        }

        return [
            'identifier' => $identifier,
            'score' => $score,
            'remarks' => $remarks,
        ];
    }

    private static function findStudentMatch(string $identifier, array $byId, array $byName): ?array
    {
        $clean = strtolower(trim($identifier));
        if (isset($byId[$clean])) {
            return $byId[$clean];
        }
        if (isset($byName[$clean])) {
            return $byName[$clean];
        }

        // Substring / fuzzy match
        foreach ($byName as $name => $stu) {
            if (str_contains($clean, $name) || str_contains($name, $clean)) {
                return $stu;
            }
        }

        return null;
    }
}
