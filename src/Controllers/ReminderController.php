<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Reminder;

class ReminderController
{
    public function index(Request $request): void
    {
        $teacherId = Session::userId();
        $reminders = Reminder::getByUser($teacherId);

        View::render('reminders.index', [
            'title' => 'Task & Event Reminders',
            'reminders' => $reminders,
            'categories' => Reminder::CATEGORIES,
        ]);
    }

    public function store(Request $request): void
    {
        $teacherId = Session::userId();
        $title = trim((string) $request->input('title'));
        $description = trim((string) $request->input('description'));
        $dueDate = (string) $request->input('due_date', date('Y-m-d'));
        $dueTime = (string) $request->input('due_time');
        $priority = (string) $request->input('priority', 'medium');
        $category = (string) $request->input('category', 'general');

        if (empty($title)) {
            Session::flash('error', 'Please enter a reminder title.');
            Response::redirect('/reminders');
        }

        Reminder::create([
            'user_id' => $teacherId,
            'title' => $title,
            'description' => $description,
            'due_date' => $dueDate,
            'due_time' => $dueTime,
            'priority' => $priority,
            'category' => $category,
        ]);

        Session::flash('success', 'Reminder added successfully.');
        Response::redirect('/reminders');
    }

    public function toggle(Request $request, string $id): void
    {
        $teacherId = Session::userId();
        Reminder::toggleStatus((int) $id, $teacherId);
        Session::flash('success', 'Reminder status updated.');
        Response::redirect('/reminders');
    }

    public function delete(Request $request, string $id): void
    {
        $teacherId = Session::userId();
        Reminder::delete((int) $id, $teacherId);
        Session::flash('success', 'Reminder removed.');
        Response::redirect('/reminders');
    }
}
