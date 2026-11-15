<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Attendance;

class AttendanceController
{
    public function index(Request $request): void
    {
        $teacherId = Session::userId();
        $classes = SchoolClass::getAllByTeacher($teacherId);
        $selectedDate = (string) $request->query('date', date('Y-m-d'));

        foreach ($classes as &$c) {
            $existing = Attendance::getByClassAndDate((int) $c['id'], $selectedDate);
            $c['attendance_recorded'] = !empty($existing);
            $c['recorded_count'] = count($existing);
        }
        unset($c);

        View::render('attendance.index', [
            'title' => 'Attendance Management',
            'classes' => $classes,
            'selectedDate' => $selectedDate,
        ]);
    }

    public function sheet(Request $request, string $classId): void
    {
        $cId = (int) $classId;
        $class = SchoolClass::findById($cId);
        if (!$class || ((int)($class['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Class not found or access denied.');
            Response::redirect('/attendance');
            return;
        }

        $date = $request->query('date', date('Y-m-d'));
        $students = Student::getByClass($cId);

        // Fetch existing attendance records for this date
        $existing = Attendance::getByClassAndDate($cId, $date);
        $attendanceMap = [];
        foreach ($existing as $row) {
            $attendanceMap[$row['student_id']] = [
                'status' => $row['status'],
                'remarks' => $row['remarks'],
            ];
        }

        // Decorate students with consecutive absence counts & existing status
        foreach ($students as &$stu) {
            $stuId = (int) $stu['id'];
            $stu['consecutive_absences'] = Student::getConsecutiveAbsences($stuId);
            $stu['current_status'] = $attendanceMap[$stuId]['status'] ?? 'P'; // Default to Present
            $stu['current_remarks'] = $attendanceMap[$stuId]['remarks'] ?? '';
        }
        unset($stu);

        View::render('attendance.sheet', [
            'title' => 'Roll Call: ' . $class['name'] . ' ' . $class['section'],
            'class' => $class,
            'date' => $date,
            'students' => $students,
            'isSaved' => !empty($existing),
        ]);
    }

    public function save(Request $request, string $classId): void
    {
        $cId = (int) $classId;
        $class = SchoolClass::findById($cId);
        if (!$class || ((int)($class['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Class not found or access denied.');
            Response::redirect('/attendance');
            return;
        }

        $date = $request->input('date', date('Y-m-d'));
        $records = $request->input('attendance', []);

        if (empty($records)) {
            Session::flash('error', 'No student records received.');
            Response::redirect("/attendance/sheet/{$cId}?date={$date}");
        }

        $existing = Attendance::getByClassAndDate($cId, $date);
        $wasAlreadySaved = !empty($existing);

        Attendance::recordBatch($cId, $date, $records);

        $msg = $wasAlreadySaved
            ? "Roll call for {$date} updated in-place. Existing records were successfully edited without duplicates."
            : "Attendance for {$date} saved successfully.";

        Session::flash('success', $msg);
        Response::redirect("/attendance/sheet/{$cId}?date={$date}");
    }

    public function printSheet(Request $request, string $classId): void
    {
        $cId = (int) $classId;
        $class = SchoolClass::findById($cId);
        if (!$class || ((int)($class['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Class not found or access denied.');
            Response::redirect('/attendance');
            return;
        }

        $date = $request->query('date', date('Y-m-d'));
        $students = Student::getByClass($cId);
        $existing = Attendance::getByClassAndDate($cId, $date);

        $attendanceMap = [];
        $summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0];
        foreach ($existing as $row) {
            $attendanceMap[$row['student_id']] = $row;
            $st = strtoupper((string)$row['status']);
            if ($st === 'P') $summary['present']++;
            elseif ($st === 'A') $summary['absent']++;
            elseif ($st === 'L') $summary['late']++;
            elseif ($st === 'E') $summary['excused']++;
        }

        foreach ($students as &$stu) {
            $stuId = (int) $stu['id'];
            $stu['current_status'] = $attendanceMap[$stuId]['status'] ?? 'P';
            $stu['current_remarks'] = $attendanceMap[$stuId]['remarks'] ?? '';
        }
        unset($stu);

        $teacher = Session::user();

        View::render('attendance.print_sheet', [
            'title' => 'Attendance Sheet - ' . $class['name'] . ' ' . $class['section'] . ' (' . $date . ')',
            'class' => $class,
            'date' => $date,
            'students' => $students,
            'summary' => $summary,
            'teacher' => $teacher,
        ], 'print');
    }

    public function reports(Request $request): void
    {
        $teacherId = Session::userId();
        $classes = SchoolClass::getAllByTeacher($teacherId);

        $defaultClassId = !empty($classes) ? (int) $classes[0]['id'] : 0;
        $classId = (int) $request->query('class_id', (string) $defaultClassId);

        $class = null;
        if ($classId > 0) {
            $class = SchoolClass::findById($classId);
            if (!$class || ((int)($class['teacher_id'] ?? 0) !== $teacherId && !Session::isSuperAdmin())) {
                Session::flash('error', 'Class not found or access denied.');
                Response::redirect('/attendance');
                return;
            }
        }

        $allStudents = $classId > 0 ? Student::getByClass($classId) : [];

        $dateFrom = (string) $request->query('date_from', date('Y-m-01'));
        $dateTo = (string) $request->query('date_to', date('Y-m-d'));
        $scope = (string) $request->query('scope', 'all'); // 'all', 'single', 'selected'
        $singleStudentId = (int) $request->query('student_id', 0);

        $rawSelected = $request->query('students', '');
        $selectedIds = [];
        if (is_array($rawSelected)) {
            $selectedIds = array_map('intval', $rawSelected);
        } elseif (!empty($rawSelected)) {
            $selectedIds = array_map('intval', explode(',', (string) $rawSelected));
        }

        $filterStudentIds = [];
        if ($scope === 'single' && $singleStudentId > 0) {
            $filterStudentIds = [$singleStudentId];
        } elseif ($scope === 'selected' && !empty($selectedIds)) {
            $filterStudentIds = $selectedIds;
        }

        $report = null;
        if ($classId > 0) {
            $report = Attendance::getCustomReport($classId, $dateFrom, $dateTo, $filterStudentIds);
        }

        View::render('attendance.reports', [
            'title' => 'Class Attendance Records & Intelligence Dashboard',
            'classes' => $classes,
            'selectedClass' => $class,
            'allStudents' => $allStudents,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'scope' => $scope,
            'singleStudentId' => $singleStudentId,
            'selectedIds' => $selectedIds,
            'report' => $report,
        ]);
    }

    public function printReport(Request $request): void
    {
        $teacherId = Session::userId();
        $classId = (int) $request->query('class_id', 0);
        if ($classId <= 0) {
            $classes = SchoolClass::getAllByTeacher($teacherId);
            $classId = !empty($classes) ? (int) $classes[0]['id'] : 0;
        }
        $class = $classId > 0 ? SchoolClass::findById($classId) : null;

        if (!$class || ((int)($class['teacher_id'] ?? 0) !== $teacherId && !Session::isSuperAdmin())) {
            Session::flash('error', 'Class not found or access denied.');
            Response::redirect('/attendance');
            return;
        }

        $dateFrom = (string) $request->query('date_from', date('Y-m-01'));
        $dateTo = (string) $request->query('date_to', date('Y-m-d'));
        $scope = (string) $request->query('scope', 'all');
        $singleStudentId = (int) $request->query('student_id', 0);

        $rawSelected = $request->query('students', '');
        $selectedIds = [];
        if (is_array($rawSelected)) {
            $selectedIds = array_map('intval', $rawSelected);
        } elseif (!empty($rawSelected)) {
            $selectedIds = array_map('intval', explode(',', (string) $rawSelected));
        }

        $filterStudentIds = [];
        if ($scope === 'single' && $singleStudentId > 0) {
            $filterStudentIds = [$singleStudentId];
        } elseif ($scope === 'selected' && !empty($selectedIds)) {
            $filterStudentIds = $selectedIds;
        }

        $report = Attendance::getCustomReport($classId, $dateFrom, $dateTo, $filterStudentIds);
        $teacher = Session::user();

        View::render('attendance.print_report', [
            'title' => 'Attendance Audit Report - ' . $class['name'] . ' ' . $class['section'],
            'class' => $class,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'scope' => $scope,
            'report' => $report,
            'teacher' => $teacher,
        ], 'print');
    }
}
