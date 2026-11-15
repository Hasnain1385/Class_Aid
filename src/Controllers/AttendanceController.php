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
        $selectedDate = $request->query('date', date('Y-m-d'));

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

        Attendance::recordBatch($cId, $date, $records);

        Session::flash('success', "Attendance for {$date} saved successfully.");
        Response::redirect("/attendance/sheet/{$cId}?date={$date}");
    }
}
