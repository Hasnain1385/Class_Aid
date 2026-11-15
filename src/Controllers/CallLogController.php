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

class CallLogController
{
    public function index(Request $request): void
    {
        $teacherId = Session::userId();
        $classes = SchoolClass::getAllByTeacher($teacherId);

        $filters = [
            'class_id' => $request->query('class_id'),
            'student_id' => $request->query('student_id'),
            'category' => $request->query('category'),
            'search' => $request->query('search'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $callLogs = ParentCallLog::getAll($teacherId, $filters);

        View::render('calls.index', [
            'title' => 'Parent Communication Journal',
            'calls' => $callLogs,
            'classes' => $classes,
            'filters' => $filters,
            'categories' => ParentCallLog::CATEGORIES,
        ]);
    }

    public function create(Request $request): void
    {
        $teacherId = Session::userId();
        $classes = SchoolClass::getAllByTeacher($teacherId);

        $preselectedStudentId = (int) $request->query('student_id', 0);
        $student = $preselectedStudentId ? Student::findById($preselectedStudentId) : null;
        $category = $request->query('category', 'Absenteeism');

        View::render('calls.create', [
            'title' => 'Log Parent Interaction',
            'classes' => $classes,
            'student' => $student,
            'preselectedCategory' => $category,
            'categories' => ParentCallLog::CATEGORIES,
        ]);
    }

    public function store(Request $request): void
    {
        $teacherId = Session::userId();
        $studentId = (int) $request->input('student_id');
        $contactPerson = trim((string) $request->input('contact_person_name'));
        $relationship = trim((string) $request->input('relationship'));
        $phoneDialed = trim((string) $request->input('phone_dialed'));
        $category = trim((string) $request->input('call_category', 'General'));
        $discussion = trim((string) $request->input('discussion_summary'));
        $response = trim((string) $request->input('parent_response'));
        $callTimestamp = $request->input('call_timestamp') ?: date('Y-m-d H:i:s');
        $callType = $request->input('call_type', 'Phone Call');
        $followUp = (bool) $request->input('follow_up_required', false);
        $followUpDate = $request->input('follow_up_date') ?: null;

        if (empty($studentId) || empty($contactPerson) || empty($phoneDialed) || empty($discussion) || empty($response)) {
            Session::flash('error', 'Please fill in all required interaction details.');
            Response::redirect($studentId ? "/calls/create?student_id={$studentId}" : '/calls/create');
        }

        ParentCallLog::create([
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'contact_person_name' => $contactPerson,
            'relationship' => $relationship,
            'phone_dialed' => $phoneDialed,
            'call_timestamp' => $callTimestamp,
            'call_type' => $callType,
            'call_category' => $category,
            'discussion_summary' => $discussion,
            'parent_response' => $response,
            'follow_up_required' => $followUp ? 1 : 0,
            'follow_up_date' => $followUpDate,
            'status' => $followUp ? 'Pending Follow-up' : 'Completed',
        ]);

        Session::flash('success', 'Parent communication log recorded successfully.');
        Response::redirect("/students/{$studentId}");
    }

    public function toggleStatus(Request $request, string $id): void
    {
        $logId = (int) $id;
        $log = ParentCallLog::findById($logId);
        if ($log && ((int)($log['teacher_id'] ?? 0) === (int)Session::userId() || Session::isSuperAdmin())) {
            $newStatus = ($log['status'] === 'Resolved') ? 'Pending Follow-up' : 'Resolved';
            ParentCallLog::updateStatus($logId, $newStatus);
            Session::flash('success', "Follow-up marked as {$newStatus}.");
        }
        $redirectUrl = (string) $request->input('redirect', $request->query('redirect', '/calls'));
        Response::redirect($redirectUrl);
    }

    public function exportCsv(Request $request): void
    {
        $teacherId = Session::userId();
        $filters = [
            'class_id' => $request->query('class_id'),
            'student_id' => $request->query('student_id'),
            'category' => $request->query('category'),
            'search' => $request->query('search'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $logs = ParentCallLog::getAll($teacherId, $filters);

        $headers = [
            'Record ID',
            'Date & Time',
            'Student Admin ID',
            'Student Name',
            'Class & Section',
            'Contact Person',
            'Relationship',
            'Phone Dialed',
            'Interaction Type',
            'Category',
            'Discussion Summary',
            'Parent Response / Commitment',
            'Follow-up Required',
            'Follow-up Date',
            'Status',
        ];

        $rows = [];
        foreach ($logs as $l) {
            $rows[] = [
                $l['id'],
                $l['call_timestamp'],
                $l['student_admin_id'],
                $l['first_name'] . ' ' . $l['last_name'],
                $l['class_name'] . ' - ' . $l['class_section'],
                $l['contact_person_name'],
                $l['relationship'],
                $l['phone_dialed'],
                $l['call_type'],
                $l['call_category'],
                $l['discussion_summary'],
                $l['parent_response'],
                $l['follow_up_required'] ? 'Yes' : 'No',
                $l['follow_up_date'] ?? 'N/A',
                $l['status'],
            ];
        }

        $filename = 'parent_call_records_' . date('Y-m-d_His') . '.csv';
        Response::csv($filename, $headers, $rows);
    }

    public function printStudent(Request $request, string $studentId): void
    {
        $sId = (int) $studentId;
        $student = Student::findById($sId);
        if (!$student) {
            Session::flash('error', 'Student not found.');
            Response::redirect('/calls');
        }

        $calls = ParentCallLog::getByStudent($sId);
        $user = Session::user();

        View::render('calls.print_student', [
            'title' => 'Parent Communication Audit - ' . $student['first_name'] . ' ' . $student['last_name'],
            'student' => $student,
            'calls' => $calls,
            'teacher' => $user,
        ], 'print');
    }

    public function printClass(Request $request, string $classId): void
    {
        $cId = (int) $classId;
        $class = SchoolClass::findById($cId);
        if (!$class) {
            Session::flash('error', 'Class not found.');
            Response::redirect('/calls');
        }

        $teacherId = Session::userId();
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $calls = ParentCallLog::getAll($teacherId, [
            'class_id' => $cId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        $user = Session::user();

        View::render('calls.print_class', [
            'title' => 'Class Parent Communication Audit - ' . $class['name'] . ' ' . $class['section'],
            'class' => $class,
            'calls' => $calls,
            'teacher' => $user,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ], 'print');
    }

    public function printAudit(Request $request): void
    {
        $teacherId = Session::userId();
        $classId = (int) $request->query('class_id', 0);
        $category = $request->query('category');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $filters = [];
        if ($classId > 0) $filters['class_id'] = $classId;
        if (!empty($category)) $filters['category'] = $category;
        if (!empty($dateFrom)) $filters['date_from'] = $dateFrom;
        if (!empty($dateTo)) $filters['date_to'] = $dateTo;

        $calls = ParentCallLog::getAll($teacherId, $filters);
        $user = Session::user();
        $class = $classId > 0 ? SchoolClass::findById($classId) : null;

        View::render('calls.print_audit', [
            'title' => 'Parent Communication Audit Register',
            'calls' => $calls,
            'teacher' => $user,
            'class' => $class,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'category' => $category,
        ], 'print');
    }
}
