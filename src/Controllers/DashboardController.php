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

class DashboardController
{
    public function index(Request $request): void
    {
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
        ]);
    }
}
