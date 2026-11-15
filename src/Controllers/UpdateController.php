<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Core\Session;
use App\Core\Config;
use App\Services\UpdateService;
use Throwable;

class UpdateController
{
    /**
     * Display the System Updates Cockpit
     */
    public function index(Request $request): void
    {
        $currentInfo = UpdateService::getCurrentVersionInfo();
        $force = ($request->query('check') === '1');
        $updateStatus = UpdateService::checkForUpdates($force);
        $backups = UpdateService::getBackups();

        // Read recent update logs
        $baseDir = UpdateService::getBaseDir();
        $logFile = $baseDir . '/storage/logs/updates.log';
        $logLines = [];
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $logLines = array_slice(array_reverse($lines), 0, 30);
        }

        View::render('admin.updates.index', [
            'title' => 'System Updates & In-Place Engine',
            'current' => $currentInfo,
            'update' => $updateStatus,
            'backups' => $backups,
            'logLines' => $logLines,
            'repo' => UpdateService::getRepo(),
            'branch' => UpdateService::getBranch(),
            'tokenConfigured' => trim((string) Config::get('GITHUB_TOKEN', '')) !== '',
        ], 'admin');
    }

    /**
     * Trigger fresh update check via AJAX or POST
     */
    public function check(Request $request): void
    {
        try {
            $updateStatus = UpdateService::checkForUpdates(true);

            if ($request->isJson() || $request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode($updateStatus);
                return;
            }

            if (!empty($updateStatus['has_update'])) {
                Session::flash('success', "Update available: {$updateStatus['latest_version']} is ready to install!");
            } else {
                Session::flash('success', "Class Aid is up to date! (Current version: {$updateStatus['current_version']})");
            }
        } catch (Throwable $e) {
            if ($request->isJson() || $request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                return;
            }
            Session::flash('error', "Update check failed: " . $e->getMessage());
        }

        header('Location: /admin/updates');
        exit;
    }

    /**
     * Perform automated in-place update
     */
    public function apply(Request $request): void
    {
        try {
            $result = UpdateService::applyUpdate();

            if ($request->isJson() || $request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode($result);
                return;
            }

            if (!empty($result['success'])) {
                Session::flash('success', "Application updated successfully to {$result['new_version']}!");
            } else {
                $err = $result['error'] ?? 'Unknown error';
                $rbNote = !empty($result['rolled_back']) ? ' (Files automatically restored to previous version)' : '';
                Session::flash('error', "Update failed: {$err}{$rbNote}");
            }
        } catch (Throwable $e) {
            if ($request->isJson() || $request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                return;
            }
            Session::flash('error', "Update execution error: " . $e->getMessage());
        }

        header('Location: /admin/updates');
        exit;
    }

    /**
     * Handle manual update ZIP file upload
     */
    public function upload(Request $request): void
    {
        if (empty($_FILES['update_zip']['tmp_name']) || $_FILES['update_zip']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Please select a valid update ZIP archive file.');
            header('Location: /admin/updates');
            exit;
        }

        $file = $_FILES['update_zip'];
        $baseDir = UpdateService::getBaseDir();
        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));

        if ($ext !== 'zip') {
            Session::flash('error', 'Only .zip archive packages are supported for manual updates.');
            header('Location: /admin/updates');
            exit;
        }

        $destDir = $baseDir . '/storage/updates';
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0755, true);
        }

        $destPath = $destDir . '/manual_update_' . date('Ymd_His') . '.zip';
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Session::flash('error', 'Failed to store uploaded update file. Check directory permissions.');
            header('Location: /admin/updates');
            exit;
        }

        // Apply from uploaded zip
        $result = UpdateService::applyUpdate($destPath);

        if (!empty($result['success'])) {
            Session::flash('success', "Manual package update applied successfully!");
        } else {
            $err = $result['error'] ?? 'Unknown error';
            $rbNote = !empty($result['rolled_back']) ? ' (Safely rolled back to previous version)' : '';
            Session::flash('error', "Manual update failed: {$err}{$rbNote}");
        }

        header('Location: /admin/updates');
        exit;
    }

    /**
     * Create an on-demand full backup
     */
    public function createBackup(Request $request): void
    {
        $note = (string) $request->input('note', 'Manual Super Admin backup');
        try {
            $record = UpdateService::createBackup($note);
            Session::flash('success', "Safety snapshot created: {$record['filename']} (" . number_format($record['size'] / 1024, 1) . " KB)");
        } catch (Throwable $e) {
            Session::flash('error', "Backup failed: " . $e->getMessage());
        }

        header('Location: /admin/updates');
        exit;
    }

    /**
     * Rollback/restore from a selected backup
     */
    public function restoreBackup(Request $request): void
    {
        $filename = (string) $request->input('filename', '');
        if ($filename === '') {
            Session::flash('error', 'No backup file specified.');
            header('Location: /admin/updates');
            exit;
        }

        try {
            $res = UpdateService::rollback($filename);
            Session::flash('success', "Successfully restored system from backup: {$filename}!");
        } catch (Throwable $e) {
            Session::flash('error', "Restore failed: " . $e->getMessage());
        }

        header('Location: /admin/updates');
        exit;
    }

    /**
     * Download backup zip
     */
    public function downloadBackup(Request $request, string $filename): void
    {
        $clean = basename($filename);
        $baseDir = UpdateService::getBaseDir();
        $fullPath = $baseDir . '/storage/backups/' . $clean;

        if (!file_exists($fullPath) || !str_ends_with($clean, '.zip')) {
            Session::flash('error', 'Backup file not found.');
            header('Location: /admin/updates');
            exit;
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $clean . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    /**
     * Save GitHub Repository Settings
     */
    public function saveSettings(Request $request): void
    {
        $repo = trim((string) $request->input('github_repo', 'class-aid/class-aid'));
        $branch = trim((string) $request->input('github_branch', 'main'));
        $token = trim((string) $request->input('github_token', ''));

        if ($repo === '') {
            $repo = 'class-aid/class-aid';
        }
        if ($branch === '') {
            $branch = 'main';
        }

        $envData = [
            'GITHUB_REPO' => $repo,
            'GITHUB_BRANCH' => $branch,
        ];

        // Only update token if provided (or if user cleared it)
        if ($request->input('update_token') === '1') {
            $envData['GITHUB_TOKEN'] = $token;
        }

        Config::writeEnv($envData);
        @unlink(UpdateService::getBaseDir() . '/storage/framework/update_cache.json');

        Session::flash('success', 'GitHub repository updater settings updated successfully!');
        header('Location: /admin/updates');
        exit;
    }
}
