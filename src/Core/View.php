<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class View
{
    public static function render(string $template, array $data = [], string $layout = 'main'): void
    {
        $baseDir = Config::get('BASE_DIR', dirname(__DIR__, 2));
        $viewFile = $baseDir . '/views/' . str_replace('.', '/', $template) . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("View file not found: {$viewFile}");
        }

        // Extract variables to local scope
        extract($data);

        // Helper closures available inside templates
        $e = fn(?string $val): string => htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
        $csrf = fn(): string => Csrf::field();
        $csrfToken = fn(): string => Csrf::getToken();
        $authUser = Session::user();
        if (!isset($user)) {
            $user = $authUser;
        }
        $flashSuccess = Session::flash('success');
        $flashError = Session::flash('error');
        $flashWarning = Session::flash('warning');

        // Global SaaS data for layouts
        $activeAnnouncements = [];
        $userSubscription = null;
        $pendingRemindersCount = 0;
        $pendingRemindersList = [];

        if (Session::check() && $authUser) {
            $activeAnnouncements = \App\Models\Announcement::getAllPublished($authUser['role'] ?? 'teacher');
            if (($authUser['role'] ?? '') === 'teacher') {
                $userSubscription = \App\Models\Subscription::getActiveForUser((int) $authUser['id']);
                $pendingRemindersCount = \App\Models\Reminder::countPending((int) $authUser['id']);
                $pendingRemindersList = \App\Models\Reminder::getPending((int) $authUser['id'], 5);
            }
        }

        // Capture view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === 'none' || empty($layout)) {
            echo $content;
            return;
        }

        $layoutFile = $baseDir . '/views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new RuntimeException("Layout file not found: {$layoutFile}");
        }

        require $layoutFile;
    }
}
