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
}
