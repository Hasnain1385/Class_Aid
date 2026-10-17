<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Exam;
use App\Models\Mark;

class ResultController
{
    public function index(Request $request): void
    {
        $teacherId = Session::userId();
        $classes = SchoolClass::getAllByTeacher($teacherId);
        $exams = Exam::getByTeacher($teacherId);

        // Fetch subjects for class dropdown in modal
        $classSubjects = [];
        foreach ($classes as $c) {
            $classSubjects[$c['id']] = Subject::getByClass((int) $c['id']);
        }

        View::render('results.index', [
            'title' => 'Examinations & Results',
            'exams' => $exams,
            'classes' => $classes,
            'classSubjects' => $classSubjects,
        ]);
    }

    public function createExam(Request $request): void
    {
        $subjectId = (int) $request->input('subject_id');
        $title = trim((string) $request->input('title'));
        $term = trim((string) $request->input('term', 'Term 1'));
        $maxMarks = (float) $request->input('max_marks', 100.0);
        $passMarks = (float) $request->input('pass_marks', 40.0);
        $examDate = $request->input('exam_date') ?: date('Y-m-d');

        if (empty($subjectId) || empty($title) || $maxMarks <= 0) {
            Session::flash('error', 'Please fill in valid exam details.');
            Response::redirect('/results');
        }

        $examId = Exam::create([
            'subject_id' => $subjectId,
            'title' => $title,
            'term' => $term,
            'max_marks' => $maxMarks,
            'pass_marks' => $passMarks,
            'exam_date' => $examDate,
        ]);

        Session::flash('success', "Exam '{$title}' created. You can now enter student marks.");
        Response::redirect("/results/enter/{$examId}");
    }

    public function enterMarks(Request $request, string $examId): void
    {
        $eId = (int) $examId;
        $exam = Exam::findById($eId);
        if (!$exam) {
            Session::flash('error', 'Exam not found.');
            Response::redirect('/results');
        }

        $students = Student::getByClass((int) $exam['class_id']);
        $existingMarks = Mark::getByExam($eId);

        $marksMap = [];
        foreach ($existingMarks as $m) {
            $marksMap[$m['student_id']] = $m;
        }

        foreach ($students as &$stu) {
            $stuId = (int) $stu['id'];
            $stu['marks_data'] = $marksMap[$stuId] ?? null;
        }
        unset($stu);

        $analytics = Mark::getExamAnalytics($eId);

        View::render('results.enter', [
            'title' => 'Enter Marks: ' . $exam['title'] . ' (' . $exam['subject_name'] . ')',
            'exam' => $exam,
            'students' => $students,
            'analytics' => $analytics,
        ]);
    }

    public function saveMarks(Request $request, string $examId): void
    {
        $eId = (int) $examId;
        $exam = Exam::findById($eId);
        if (!$exam) {
            Session::flash('error', 'Exam not found.');
            Response::redirect('/results');
        }

        $marks = $request->input('marks', []);
        Mark::recordBatch($eId, (float) $exam['max_marks'], (float) $exam['pass_marks'], $marks);

        Session::flash('success', 'Student marks and letter grades updated successfully.');
        Response::redirect("/results/enter/{$eId}");
    }

    public function studentReport(Request $request, string $studentId): void
    {
        $sId = (int) $studentId;
        $student = Student::findById($sId);
        if (!$student) {
            Session::flash('error', 'Student not found.');
            Response::redirect('/students');
        }

        $marks = Mark::getByStudent($sId);
        $attendanceStats = Student::getAttendanceStats($sId);
        $user = Session::user();

        View::render('results.student_report', [
            'title' => 'Report Card: ' . $student['first_name'] . ' ' . $student['last_name'],
            'student' => $student,
            'marks' => $marks,
            'attendanceStats' => $attendanceStats,
            'teacher' => $user,
        ], 'print');
    }
}
