<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\ParentCallLog;
use App\Models\Attendance;
use App\Models\Mark;
use App\Utils\OcrService;
use App\Utils\ImportParser;

class StudentController
{
    public function index(Request $request): void
    {
        $teacherId = Session::userId();
        $classId = $request->query('class_id') ? (int) $request->query('class_id') : null;
        $search = trim((string) $request->query('q', ''));

        $classes = SchoolClass::getAllByTeacher($teacherId);

        if (!empty($search)) {
            $students = Student::search($search, $classId);
        } elseif ($classId) {
            $students = Student::getByClass($classId);
        } else {
            $students = Student::getAll($teacherId);
        }

        // Decorate with attendance stats
        foreach ($students as &$stu) {
            $stu['stats'] = Student::getAttendanceStats((int) $stu['id']);
        }
        unset($stu);

        View::render('students.index', [
            'title' => 'Student Directory',
            'students' => $students,
            'classes' => $classes,
            'selectedClassId' => $classId,
            'searchQuery' => $search,
        ]);
    }

    public function show(Request $request, string $id): void
    {
        $studentId = (int) $id;
        $student = Student::findById($studentId);

        if (!$student || ((int)($student['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Student not found or access denied.');
            Response::redirect('/students');
            return;
        }

        $callLogs = ParentCallLog::getByStudent($studentId);
        $attendanceStats = Student::getAttendanceStats($studentId);
        $attendanceHistory = Attendance::getStudentHistory($studentId, 20);
        $marks = Mark::getByStudent($studentId);

        View::render('students.show', [
            'title' => $student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['student_admin_id'] . ')',
            'student' => $student,
            'callLogs' => $callLogs,
            'attendanceStats' => $attendanceStats,
            'attendanceHistory' => $attendanceHistory,
            'marks' => $marks,
            'categories' => ParentCallLog::CATEGORIES,
        ]);
    }

    public function create(Request $request): void
    {
        $teacherId = Session::userId();
        $classes = SchoolClass::getAllByTeacher($teacherId);
        $preselectedClassId = (int) $request->query('class_id', 0);

        View::render('students.create', [
            'title' => 'Register New Student',
            'classes' => $classes,
            'preselectedClassId' => $preselectedClassId,
        ]);
    }

    public function store(Request $request): void
    {
        $adminId = trim((string) $request->input('student_admin_id'));
        $firstName = trim((string) $request->input('first_name'));
        $lastName = trim((string) $request->input('last_name'));
        $classId = (int) $request->input('class_id');
        $primaryPhone = trim((string) $request->input('primary_phone'));
        $guardianName = trim((string) $request->input('primary_guardian_name'));

        if (empty($adminId) || empty($firstName) || empty($lastName) || empty($classId) || empty($primaryPhone) || empty($guardianName)) {
            Session::flash('error', 'Please fill in all required fields.');
            Response::redirect('/students/create');
        }

        // Check unique student_admin_id
        $existing = Student::findByAdminId($adminId);
        if ($existing) {
            Session::flash('error', "Student Admin ID '{$adminId}' is already registered to another student ({$existing['first_name']} {$existing['last_name']}).");
            Response::redirect('/students/create');
        }

        $studentId = Student::create([
            'student_admin_id' => $adminId,
            'class_id' => $classId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => $request->input('gender'),
            'dob' => $request->input('dob'),
            'primary_guardian_name' => $guardianName,
            'primary_guardian_relation' => $request->input('primary_guardian_relation', 'Parent'),
            'primary_phone' => $primaryPhone,
            'secondary_phone' => $request->input('secondary_phone'),
            'guardian_email' => $request->input('guardian_email'),
            'address' => $request->input('address'),
            'status' => 'active',
        ]);

        Session::flash('success', "Student {$firstName} {$lastName} [{$adminId}] registered successfully.");
        Response::redirect("/students/{$studentId}");
    }

    public function edit(Request $request, string $id): void
    {
        $studentId = (int) $id;
        $student = Student::findById($studentId);
        if (!$student || ((int)($student['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Student not found or access denied.');
            Response::redirect('/students');
            return;
        }

        $teacherId = Session::userId();
        $classes = SchoolClass::getAllByTeacher($teacherId);

        View::render('students.edit', [
            'title' => 'Edit Student: ' . $student['first_name'],
            'student' => $student,
            'classes' => $classes,
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $studentId = (int) $id;
        $student = Student::findById($studentId);
        if (!$student || ((int)($student['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Student not found or access denied.');
            Response::redirect('/students');
            return;
        }

        $adminId = trim((string) $request->input('student_admin_id'));
        $firstName = trim((string) $request->input('first_name'));
        $lastName = trim((string) $request->input('last_name'));
        $classId = (int) $request->input('class_id');
        $primaryPhone = trim((string) $request->input('primary_phone'));
        $guardianName = trim((string) $request->input('primary_guardian_name'));

        // Check unique student_admin_id if changed
        $existing = Student::findByAdminId($adminId);
        if ($existing && (int)$existing['id'] !== $studentId) {
            Session::flash('error', "Student Admin ID '{$adminId}' is already registered.");
            Response::redirect("/students/{$studentId}/edit");
        }

        Student::update($studentId, [
            'student_admin_id' => $adminId,
            'class_id' => $classId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => $request->input('gender'),
            'dob' => $request->input('dob'),
            'primary_guardian_name' => $guardianName,
            'primary_guardian_relation' => $request->input('primary_guardian_relation', 'Parent'),
            'primary_phone' => $primaryPhone,
            'secondary_phone' => $request->input('secondary_phone'),
            'guardian_email' => $request->input('guardian_email'),
            'address' => $request->input('address'),
            'status' => $request->input('status', 'active'),
        ]);

        Session::flash('success', "Student records updated successfully.");
        Response::redirect("/students/{$studentId}");
    }

    public function searchApi(Request $request): void
    {
        $q = trim((string) $request->query('q', ''));
        if (strlen($q) < 1) {
            Response::json([]);
        }

        $results = Student::search($q);
        $formatted = array_map(function($s) {
            return [
                'id' => $s['id'],
                'admin_id' => $s['student_admin_id'],
                'name' => $s['first_name'] . ' ' . $s['last_name'],
                'class' => $s['class_name'] . ' - ' . $s['class_section'],
                'phone' => $s['primary_phone'],
                'guardian_name' => $s['primary_guardian_name'] ?? 'Parent',
                'guardian_relation' => $s['primary_guardian_relation'] ?? 'Parent',
                'url' => '/students/' . $s['id'],
            ];
        }, $results);

        Response::json($formatted);
    }

    public function bulkImport(Request $request): void
    {
        $teacherId = Session::userId();
        $classes = SchoolClass::getAllByTeacher($teacherId);
        $preselectedClassId = (int) $request->query('class_id', 0);
        if (!$preselectedClassId && !empty($classes)) {
            $preselectedClassId = (int) $classes[0]['id'];
        }

        View::render('students.import', [
            'title' => 'Bulk Import Students',
            'classes' => $classes,
            'selectedClassId' => $preselectedClassId,
            'ocrAvailable' => OcrService::isAvailable(),
        ]);
    }

    public function bulkImportScan(Request $request): void
    {
        $classId = (int) $request->input('class_id', 0);
        $rawText = (string) $request->input('raw_text', '');
        $base64Image = (string) $request->input('image_base64', '');

        try {
            if ($request->hasFile('import_file')) {
                $file = $request->file('import_file');
                $tmpPath = $file['tmp_name'];
                $clientName = strtolower($file['name']);
                
                if (preg_match('/\.(png|jpe?g|webp|gif|bmp|tiff|pdf)$/i', $clientName)) {
                    $rawText = OcrService::extractText($tmpPath);
                } else {
                    $rawText = file_get_contents($tmpPath);
                }
            } elseif (!empty($base64Image)) {
                $rawText = OcrService::extractFromBase64($base64Image);
            }

            if (empty(trim($rawText))) {
                Response::json([
                    'success' => false,
                    'message' => 'No readable text or data found. Please check your image or CSV content.',
                ], 422);
                return;
            }

            $students = ImportParser::parseStudents($rawText, $classId);

            Response::json([
                'success' => true,
                'count' => count($students),
                'raw_text_preview' => mb_substr($rawText, 0, 500),
                'students' => $students,
            ]);
        } catch (\Throwable $e) {
            Response::json([
                'success' => false,
                'message' => 'Import error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function bulkImportCommit(Request $request): void
    {
        $classId = (int) $request->input('class_id');
        $students = $request->input('students', []);

        if (empty($classId)) {
            Session::flash('error', 'Please select a valid class for student import.');
            Response::redirect('/students/import');
            return;
        }

        $targetClass = SchoolClass::findById($classId);
        if (!$targetClass || ((int)($targetClass['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Unauthorized classroom selection.');
            Response::redirect('/students/import');
            return;
        }

        if (empty($students) || !is_array($students)) {
            Session::flash('error', 'No students to import.');
            Response::redirect('/students/import?class_id=' . $classId);
            return;
        }

        $imported = 0;
        $skipped = 0;

        foreach ($students as $stu) {
            $adminId = trim((string)($stu['student_admin_id'] ?? ''));
            $firstName = trim((string)($stu['first_name'] ?? ''));
            $lastName = trim((string)($stu['last_name'] ?? ''));

            if (empty($adminId) || empty($firstName)) {
                $skipped++;
                continue;
            }

            // Check duplicate
            $existing = Student::findByAdminId($adminId);
            if ($existing) {
                $skipped++;
                continue;
            }

            Student::create([
                'student_admin_id' => $adminId,
                'class_id' => $classId,
                'first_name' => $firstName,
                'last_name' => !empty($lastName) ? $lastName : 'Student',
                'gender' => $stu['gender'] ?? 'Other',
                'dob' => !empty($stu['dob']) ? $stu['dob'] : null,
                'primary_guardian_name' => !empty($stu['primary_guardian_name']) ? trim($stu['primary_guardian_name']) : 'Parent/Guardian',
                'primary_guardian_relation' => $stu['primary_guardian_relation'] ?? 'Parent',
                'primary_phone' => !empty($stu['primary_phone']) ? trim($stu['primary_phone']) : '000-000-0000',
                'secondary_phone' => $stu['secondary_phone'] ?? null,
                'guardian_email' => $stu['guardian_email'] ?? null,
                'address' => $stu['address'] ?? null,
                'status' => 'active',
            ]);
            $imported++;
        }

        $msg = "Bulk import completed! {$imported} student(s) successfully registered.";
        if ($skipped > 0) {
            $msg .= " ({$skipped} duplicate or invalid entries skipped).";
        }

        Session::flash('success', $msg);
        Response::redirect("/students?class_id={$classId}");
    }

    public function downloadTemplate(Request $request): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="students_import_template.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['student_admin_id', 'first_name', 'last_name', 'gender', 'primary_phone', 'primary_guardian_name', 'dob', 'address']);
        fputcsv($output, ['ADM-2026-001', 'Arthur', 'Dent', 'Male', '555-0101', 'Ford Dent', '2010-04-12', '42 Galaxy Way']);
        fputcsv($output, ['ADM-2026-002', 'Tricia', 'McMillan', 'Female', '555-0102', 'Zaphod McMillan', '2010-08-25', '10 Islington St']);
        fputcsv($output, ['ADM-2026-003', 'Marvin', 'Android', 'Male', '555-0103', 'Sirius Cyber', '2010-01-15', 'Floor 9 Lab']);
        fclose($output);
        exit;
    }
}
