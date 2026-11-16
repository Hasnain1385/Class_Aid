<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\SchoolClass;
use App\Models\Subject;

class ClassController
{
    public function index(Request $request): void
    {
        $teacherId = Session::userId();
        $classes = SchoolClass::getAllByTeacher($teacherId);

        foreach ($classes as &$c) {
            $c['subjects'] = Subject::getByClass((int) $c['id']);
        }
        unset($c);

        View::render('classes.index', [
            'title' => 'Class & Subject Management',
            'classes' => $classes,
        ]);
    }

    public function store(Request $request): void
    {
        $teacherId = Session::userId();
        $name = trim((string) $request->input('name'));
        $section = trim((string) $request->input('section'));
        $year = trim((string) $request->input('academic_year', date('Y') . '-' . (date('Y') + 1)));
        $isHome = (bool) $request->input('is_home_class', false);

        if (empty($name) || empty($section)) {
            Session::flash('error', 'Class name and section are required.');
            Response::redirect('/classes');
        }

        $classId = SchoolClass::create([
            'teacher_id' => $teacherId,
            'name' => $name,
            'section' => $section,
            'academic_year' => $year,
            'is_home_class' => $isHome ? 1 : 0,
        ]);

        $initialSubject = trim((string) $request->input('initial_subject'));
        if (!empty($initialSubject)) {
            Subject::create([
                'class_id' => $classId,
                'name' => $initialSubject,
                'code' => $request->input('initial_code'),
            ]);
        }

        Session::flash('success', "Class {$name}-{$section} created successfully.");
        Response::redirect('/classes');
    }

    public function storeSubject(Request $request): void
    {
        $classId = (int) $request->input('class_id');
        $name = trim((string) $request->input('name'));
        $code = trim((string) $request->input('code'));

        if (empty($classId) || empty($name)) {
            Session::flash('error', 'Subject name and class are required.');
            Response::redirect('/classes');
        }

        Subject::create([
            'class_id' => $classId,
            'name' => $name,
            'code' => !empty($code) ? $code : null,
        ]);

        Session::flash('success', "Subject '{$name}' added to class.");
        Response::redirect('/classes');
    }

    public function update(Request $request, string $classId): void
    {
        $cId = (int) $classId;
        $class = SchoolClass::findById($cId);
        if (!$class || ((int)($class['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Class not found or access denied.');
            Response::redirect('/classes');
            return;
        }

        $name = trim((string) $request->input('name'));
        $section = trim((string) $request->input('section'));
        $year = trim((string) $request->input('academic_year', date('Y') . '-' . (date('Y') + 1)));
        $isHome = (bool) $request->input('is_home_class', false);

        if (empty($name) || empty($section)) {
            Session::flash('error', 'Class name and section are required.');
            Response::redirect('/classes');
            return;
        }

        SchoolClass::update($cId, [
            'name' => $name,
            'section' => $section,
            'academic_year' => $year,
            'is_home_class' => $isHome ? 1 : 0,
        ]);

        Session::flash('success', "Class '{$name}-{$section}' updated successfully.");
        Response::redirect('/classes');
    }

    public function delete(Request $request, string $classId): void
    {
        $cId = (int) $classId;
        $class = SchoolClass::findById($cId);
        if (!$class || ((int)($class['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Class not found or access denied.');
            Response::redirect('/classes');
            return;
        }

        $name = $class['name'] . ' - ' . $class['section'];
        SchoolClass::delete($cId);

        Session::flash('success', "Class '{$name}' and its associated records were removed.");
        Response::redirect('/classes');
    }

    public function deleteSubject(Request $request, string $subjectId): void
    {
        $sId = (int) $subjectId;
        $subject = Subject::findById($sId);
        if (!$subject) {
            Session::flash('error', 'Subject not found.');
            Response::redirect('/classes');
            return;
        }

        $class = SchoolClass::findById((int) $subject['class_id']);
        if (!$class || ((int)($class['teacher_id'] ?? 0) !== (int)Session::userId() && !Session::isSuperAdmin())) {
            Session::flash('error', 'Access denied.');
            Response::redirect('/classes');
            return;
        }

        Subject::delete($sId);
        Session::flash('success', "Subject '{$subject['name']}' removed from class.");
        Response::redirect('/classes');
    }

    public function cleanMockData(Request $request): void
    {
        $teacherId = Session::userId();
        $target = $request->input('clean_target', 'demo_students');

        $pdo = \App\Core\Database::getConnection();

        if ($target === 'demo_students') {
            $classes = SchoolClass::getAllByTeacher($teacherId);
            $classIds = array_column($classes, 'id');
            if (!empty($classIds)) {
                $inClasses = implode(',', array_map('intval', $classIds));
                $stmt = $pdo->prepare("SELECT id FROM students WHERE class_id IN ({$inClasses}) AND (student_admin_id LIKE 'ADM-2026-%' OR first_name IN ('Ayaan', 'Fatima', 'Muhammad', 'Zainab', 'Bilal', 'Sara'))");
                $stmt->execute();
                $stuIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                if (!empty($stuIds)) {
                    $inStu = implode(',', array_map('intval', $stuIds));
                    $pdo->exec("DELETE FROM attendance WHERE student_id IN ({$inStu})");
                    $pdo->exec("DELETE FROM parent_call_logs WHERE student_id IN ({$inStu})");
                    $pdo->exec("DELETE FROM marks WHERE student_id IN ({$inStu})");
                    $pdo->exec("DELETE FROM students WHERE id IN ({$inStu})");
                }
            }
            Session::flash('success', 'Demo student records and simulated activity cleaned. Real classes and subject curricula are preserved.');
        } elseif ($target === 'all_classes') {
            $classes = SchoolClass::getAllByTeacher($teacherId);
            foreach ($classes as $c) {
                SchoolClass::delete((int) $c['id']);
            }
            Session::flash('success', 'All class rosters and records removed. Your workspace is reset to a clean empty slate.');
        }

        Response::redirect('/classes');
    }

    public function promotion(Request $request): void
    {
        $teacherId = Session::userId();
        $classes = SchoolClass::getAllByTeacher($teacherId);

        $sourceClassId = (int) $request->query('source_class_id', 0);
        $destClassId = (int) $request->query('dest_class_id', 0);

        if (!$sourceClassId && !empty($classes)) {
            $sourceClassId = (int) $classes[0]['id'];
        }

        $sourceClass = $sourceClassId > 0 ? SchoolClass::findById($sourceClassId) : null;
        $destClass = $destClassId > 0 ? SchoolClass::findById($destClassId) : null;

        $students = [];
        $batchStats = [];
        if ($sourceClass) {
            $students = \App\Models\Student::getByClass($sourceClassId);
            if (!empty($students)) {
                $studentIds = array_column($students, 'id');
                $batchStats = \App\Models\Student::getBatchAttendanceStats($studentIds);
            }
        }

        View::render('classes.promotion', [
            'title' => 'Student Academic Promotion & Rollover',
            'classes' => $classes,
            'sourceClass' => $sourceClass,
            'destClass' => $destClass,
            'sourceClassId' => $sourceClassId,
            'destClassId' => $destClassId,
            'students' => $students,
            'batchStats' => $batchStats,
        ]);
    }

    public function processPromotion(Request $request): void
    {
        $teacherId = Session::userId();
        $sourceClassId = (int) $request->input('source_class_id', 0);
        $destClassId = (int) $request->input('dest_class_id', 0);
        $studentIds = (array) $request->input('students', []);

        if (empty($sourceClassId) || empty($destClassId)) {
            Session::flash('error', 'Both source and destination classes must be selected.');
            Response::redirect('/classes/promotion');
            return;
        }

        if ($sourceClassId === $destClassId) {
            Session::flash('error', 'Destination class must be different from source class.');
            Response::redirect('/classes/promotion?source_class_id=' . $sourceClassId);
            return;
        }

        if (empty($studentIds)) {
            Session::flash('error', 'Please select at least one student to promote.');
            Response::redirect('/classes/promotion?source_class_id=' . $sourceClassId . '&dest_class_id=' . $destClassId);
            return;
        }

        $sourceClass = SchoolClass::findById($sourceClassId);
        $destClass = SchoolClass::findById($destClassId);

        if (!$sourceClass || !$destClass) {
            Session::flash('error', 'Selected classes could not be found.');
            Response::redirect('/classes/promotion');
            return;
        }

        if ((int)($sourceClass['teacher_id'] ?? 0) !== $teacherId && !Session::isSuperAdmin()) {
            Session::flash('error', 'Access denied to source class.');
            Response::redirect('/classes/promotion');
            return;
        }

        if ((int)($destClass['teacher_id'] ?? 0) !== $teacherId && !Session::isSuperAdmin()) {
            Session::flash('error', 'Access denied to destination class.');
            Response::redirect('/classes/promotion');
            return;
        }

        try {
            $promotedCount = \App\Models\Student::promoteStudents($studentIds, $sourceClassId, $destClassId, $teacherId);
            Session::flash('success', "Successfully promoted {$promotedCount} student(s) to {$destClass['name']} ({$destClass['section']}). Historical records, attendance rolls, and marks remain completely intact.");
            Response::redirect('/classes/promotion?source_class_id=' . $destClassId);
        } catch (\Exception $e) {
            Session::flash('error', 'Failed to promote students: ' . $e->getMessage());
            Response::redirect('/classes/promotion?source_class_id=' . $sourceClassId . '&dest_class_id=' . $destClassId);
        }
    }
}
