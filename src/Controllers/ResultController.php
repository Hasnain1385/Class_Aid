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
use App\Utils\OcrService;
use App\Utils\ImportParser;

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
        if (!$exam || ((int)($exam['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Exam not found or access denied.');
            Response::redirect('/results');
            return;
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
        if (!$exam || ((int)($exam['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Exam not found or access denied.');
            Response::redirect('/results');
            return;
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
        if (!$student || ((int)($student['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Student not found or access denied.');
            Response::redirect('/students');
            return;
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

    public function printExam(Request $request, string $examId): void
    {
        $eId = (int) $examId;
        $exam = Exam::findById($eId);
        if (!$exam || ((int)($exam['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Exam not found or access denied.');
            Response::redirect('/results');
            return;
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
        $teacher = Session::user();

        View::render('results.print_exam', [
            'title' => 'Exam Result Sheet - ' . $exam['title'] . ' (' . $exam['subject_name'] . ')',
            'exam' => $exam,
            'students' => $students,
            'analytics' => $analytics,
            'teacher' => $teacher,
        ], 'print');
    }

    public function bulkImport(Request $request): void
    {
        $teacherId = Session::userId();
        $exams = Exam::getByTeacher($teacherId);
        $selectedExamId = (int) $request->query('exam_id', 0);
        if (!$selectedExamId && !empty($exams)) {
            $selectedExamId = (int) $exams[0]['id'];
        }

        $selectedExam = null;
        $enrolledStudents = [];
        if ($selectedExamId) {
            $selectedExam = Exam::findById($selectedExamId);
            if ($selectedExam) {
                $enrolledStudents = Student::getByClass((int) $selectedExam['class_id']);
            }
        }

        View::render('results.import', [
            'title' => 'Bulk Import Test Marks',
            'exams' => $exams,
            'selectedExam' => $selectedExam,
            'selectedExamId' => $selectedExamId,
            'enrolledStudents' => $enrolledStudents,
            'ocrAvailable' => OcrService::isAvailable(),
        ]);
    }

    public function bulkImportScan(Request $request): void
    {
        $examId = (int) $request->input('exam_id');
        $exam = Exam::findById($examId);
        if (!$exam || ((int)($exam['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Response::json(['success' => false, 'message' => 'Exam not found or access denied.'], 403);
            return;
        }

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
                Response::json(['success' => false, 'message' => 'No readable text or data found.'], 422);
                return;
            }

            $enrolled = Student::getByClass((int) $exam['class_id']);
            $marks = ImportParser::parseMarks($rawText, $examId, $enrolled, (float)$exam['max_marks'], (float)$exam['pass_marks']);

            Response::json([
                'success' => true,
                'count' => count($marks),
                'exam' => $exam,
                'marks' => $marks,
                'enrolled_students' => $enrolled,
                'raw_text_preview' => mb_substr($rawText, 0, 500),
            ]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Scan error: ' . $e->getMessage()], 500);
        }
    }

    public function bulkImportCommit(Request $request): void
    {
        $examId = (int) $request->input('exam_id');
        $exam = Exam::findById($examId);
        if (!$exam || ((int)($exam['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Exam not found or access denied.');
            Response::redirect('/results');
            return;
        }

        $marksInput = $request->input('marks', []);
        if (empty($marksInput) || !is_array($marksInput)) {
            Session::flash('error', 'No marks data provided.');
            Response::redirect('/results/import?exam_id=' . $examId);
            return;
        }

        $batch = [];
        foreach ($marksInput as $item) {
            $stuId = (int)($item['student_id'] ?? 0);
            if (!$stuId || !isset($item['score']) || $item['score'] === '') {
                continue;
            }
            $batch[$stuId] = [
                'score' => (float)$item['score'],
                'remarks' => trim((string)($item['remarks'] ?? '')),
            ];
        }

        if (!empty($batch)) {
            Mark::recordBatch($examId, (float)$exam['max_marks'], (float)$exam['pass_marks'], $batch);
            Session::flash('success', "Successfully imported and updated marks for " . count($batch) . " student(s).");
        } else {
            Session::flash('warning', "No valid matched student marks found to import.");
        }

        Response::redirect("/results/enter/{$examId}");
    }

    public function downloadTemplate(Request $request, string $examId): void
    {
        $eId = (int) $examId;
        $exam = Exam::findById($eId);
        if (!$exam) {
            Response::redirect('/results');
            return;
        }

        $students = Student::getByClass((int) $exam['class_id']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="exam_marks_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $exam['title']) . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['student_admin_id', 'student_name', 'score', 'remarks']);
        foreach ($students as $stu) {
            fputcsv($output, [
                $stu['student_admin_id'],
                $stu['first_name'] . ' ' . $stu['last_name'],
                '',
                ''
            ]);
        }
        fclose($output);
        exit;
    }
}
