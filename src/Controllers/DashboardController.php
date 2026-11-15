<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\ParentCallLog;
use App\Models\Attendance;
use App\Models\Reminder;
use App\Core\Response;

class DashboardController
{
    public function index(Request $request): void
    {
        if (Session::isSuperAdmin()) {
            Response::redirect('/admin');
        }

        $teacherId = Session::userId();
        $classes = SchoolClass::getAllByTeacher($teacherId);
        $homeClass = SchoolClass::getHomeClass($teacherId);

        $totalStudents = 0;
        foreach ($classes as $c) {
            $totalStudents += (int) $c['student_count'];
        }

        $todayAttendance = Attendance::getTodayStats($teacherId);
        $pendingFollowups = ParentCallLog::getPendingFollowups($teacherId);
        $recentCalls = ParentCallLog::getRecent($teacherId, 5);
        $reminders = Reminder::getPending($teacherId, 4);

        // Find students with chronic absenteeism (>= 3 consecutive absences)
        $flaggedStudents = [];
        if ($homeClass) {
            $homeStudents = Student::getByClass((int) $homeClass['id']);
            foreach ($homeStudents as $stu) {
                $consecutive = Student::getConsecutiveAbsences((int) $stu['id']);
                if ($consecutive >= 3) {
                    $stu['consecutive_absences'] = $consecutive;
                    $flaggedStudents[] = $stu;
                }
            }
        }

        View::render('dashboard.index', [
            'title' => 'Dashboard',
            'classes' => $classes,
            'homeClass' => $homeClass,
            'totalStudents' => $totalStudents,
            'todayAttendance' => $todayAttendance,
            'pendingFollowups' => $pendingFollowups,
            'recentCalls' => $recentCalls,
            'flaggedStudents' => $flaggedStudents,
            'reminders' => $reminders,
        ]);
    }

    public function overdueNotificationsApi(Request $request): void
    {
        $userId = Session::userId();
        if ($userId <= 0) {
            Response::json(['success' => false, 'items' => []], 401);
            return;
        }

        $items = [];
        $today = date('Y-m-d');
        $nowTime = date('H:i');

        // 1. Overdue parent call follow-ups
        $followups = ParentCallLog::getPendingFollowups($userId);
        foreach ($followups as $f) {
            if (!empty($f['follow_up_date']) && $f['follow_up_date'] <= $today) {
                $isToday = ($f['follow_up_date'] === $today);
                $items[] = [
                    'id' => 'call_' . $f['id'],
                    'type' => 'call_followup',
                    'title' => ($isToday ? 'Scheduled Follow-up Today: ' : 'Overdue Parent Follow-up: ') . $f['first_name'] . ' ' . $f['last_name'],
                    'message' => 'Follow-up commitment with ' . $f['contact_person_name'] . ' (' . $f['call_category'] . ') due ' . date('M d', strtotime($f['follow_up_date'])) . '.',
                    'url' => '/calls?search=' . urlencode($f['first_name']),
                    'due' => $f['follow_up_date'],
                    'priority' => 'high',
                ];
            }
        }

        // 2. Overdue tasks / reminders
        $allReminders = Reminder::getByUser($userId);
        foreach ($allReminders as $r) {
            if ($r['status'] === 'pending') {
                $rDate = $r['due_date'];
                $rTime = $r['due_time'];
                $isOverdue = ($rDate < $today) || ($rDate === $today && !empty($rTime) && $rTime <= $nowTime);
                if ($isOverdue) {
                    $items[] = [
                        'id' => 'reminder_' . $r['id'],
                        'type' => 'task_reminder',
                        'title' => 'Overdue Task: ' . $r['title'],
                        'message' => 'Task was due ' . date('M d', strtotime($rDate)) . (!empty($rTime) ? ' at ' . $rTime : '') . '. Tap to review.',
                        'url' => '/reminders',
                        'due' => $rDate . (!empty($rTime) ? ' ' . $rTime : ''),
                        'priority' => $r['priority'] ?? 'medium',
                    ];
                }
            }
        }

        Response::json([
            'success' => true,
            'count' => count($items),
            'items' => $items,
        ]);
    }
}
