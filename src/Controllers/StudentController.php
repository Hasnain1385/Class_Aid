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

        if (!$student) {
            Session::flash('error', 'Student not found.');
            Response::redirect('/students');
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
        if (!$student) {
            Session::flash('error', 'Student not found.');
            Response::redirect('/students');
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
        if (!$student) {
            Session::flash('error', 'Student not found.');
            Response::redirect('/students');
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
                'url' => '/students/' . $s['id'],
            ];
        }, $results);

        Response::json($formatted);
    }
}
