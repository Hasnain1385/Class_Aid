<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use ZipArchive;
use RuntimeException;
use Throwable;

class UpdateService
{
    /**
     * Optional mock HTTP client for deterministic automated testing
     * Signature: fn(string $url, array $options): array{status: int, body: string, headers: array}
     */
    public static ?\Closure $mockHttpClient = null;

    /**
     * Last response headers from httpGet (e.g. rate limit info)
     */
    public static array $lastResponseHeaders = [];

    /**
     * Get base directory of project
     */
    public static function getBaseDir(): string
    {
        return rtrim((string) Config::get('BASE_DIR', dirname(__DIR__, 2)), '/');
    }

    /**
     * Get current version information from version.json and git
     */
    public static function getCurrentVersionInfo(): array
    {
        $baseDir = self::getBaseDir();
        $verFile = $baseDir . '/version.json';

        $defaults = [
            'version' => (string) Config::get('APP_VERSION', '1.0.0'),
            'release_name' => 'Class Aid v' . Config::get('APP_VERSION', '1.0.0'),
            'release_date' => date('Y-m-d'),
            'commit' => 'HEAD',
            'channel' => 'stable',
            'github_repo' => (string) Config::get('GITHUB_REPO', 'Hasnain1385/Class_Aid'),
            'github_branch' => (string) Config::get('GITHUB_BRANCH', 'main'),
        ];

        if (file_exists($verFile)) {
            $json = json_decode((string) file_get_contents($verFile), true);
            if (is_array($json)) {
                $defaults = array_merge($defaults, $json);
            }
        }

        // If commit is 'HEAD' and git is present, attempt to resolve actual git commit
        if (($defaults['commit'] === 'HEAD' || empty($defaults['commit'])) && is_dir($baseDir . '/.git')) {
            $gitHead = self::getGitCommitSha();
            if ($gitHead) {
                $defaults['commit'] = $gitHead;
            }
        }

        return $defaults;
    }

    /**
     * Attempt to resolve short commit SHA from local .git
     */
    public static function getGitCommitSha(): ?string
    {
        $baseDir = self::getBaseDir();
        $headFile = $baseDir . '/.git/HEAD';
        if (!file_exists($headFile)) {
            return null;
        }

        $head = trim((string) file_get_contents($headFile));
        if (str_starts_with($head, 'ref: ')) {
            $refPath = $baseDir . '/.git/' . trim(substr($head, 5));
            if (file_exists($refPath)) {
                return substr(trim((string) file_get_contents($refPath)), 0, 7);
            }
            // Check packed-refs if ref file doesn't exist directly
            $packedRefs = $baseDir . '/.git/packed-refs';
            if (file_exists($packedRefs)) {
                $refName = trim(substr($head, 5));
                $lines = file($packedRefs, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
                foreach ($lines as $line) {
                    if (str_contains($line, $refName)) {
                        $parts = explode(' ', trim($line));
                        if (!empty($parts[0])) {
                            return substr($parts[0], 0, 7);
                        }
                    }
                }
            }
        } elseif (strlen($head) >= 7) {
            return substr($head, 0, 7);
        }

        return null;
    }

    /**
     * Get configured GitHub repository (e.g. 'Hasnain1385/Class_Aid')
     */
    public static function getRepo(): string
    {
        $repo = (string) Config::get('GITHUB_REPO', 'Hasnain1385/Class_Aid');
        return trim($repo) !== '' ? trim($repo) : 'Hasnain1385/Class_Aid';
    }

    /**
     * Get configured GitHub branch (default: 'main')
     */
    public static function getBranch(): string
    {
        $branch = (string) Config::get('GITHUB_BRANCH', 'main');
        return trim($branch) !== '' ? trim($branch) : 'main';
    }

    /**
     * Check if a relative path is protected from in-place overwriting
     */
    public static function isProtectedPath(string $relativePath): bool
    {
        $norm = trim(str_replace('\\', '/', $relativePath), '/');

        // Critical user data & configuration that must NEVER be overwritten
        $protectedExact = [
            '.env',
            'storage/maintenance.flag',
        ];

        if (in_array($norm, $protectedExact, true)) {
            return true;
        }

        // Exclude all .sqlite files and SQLite journal/wal files
        if (str_ends_with($norm, '.sqlite') ||
            str_ends_with($norm, '.sqlite-journal') ||
            str_ends_with($norm, '.sqlite-wal') ||
            str_ends_with($norm, '.sqlite-shm')) {
            return true;
        }

        // Exclude all .env variations
        if (str_starts_with($norm, '.env.') && !str_ends_with($norm, '.example')) {
            return true;
        }

        $protectedPrefixes = [
            'public/uploads/',
            'storage/',
            '.git/',
            'database/backups/',
            'node_modules/',
        ];

        foreach ($protectedPrefixes as $prefix) {
            if (str_starts_with($norm, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Comprehensive Pre-Flight Health & Diagnostic Check
     */
    public static function getPreFlightHealth(): array
    {
        $baseDir = self::getBaseDir();

        $directories = [
            'root' => ['path' => '/', 'writable' => is_writable($baseDir)],
            'src' => ['path' => 'src', 'writable' => is_writable($baseDir . '/src')],
            'views' => ['path' => 'views', 'writable' => is_writable($baseDir . '/views')],
            'public' => ['path' => 'public', 'writable' => is_writable($baseDir . '/public')],
            'storage' => ['path' => 'storage', 'writable' => is_writable($baseDir . '/storage')],
            'database' => ['path' => 'database', 'writable' => is_writable($baseDir . '/database')],
        ];

        $allWritable = true;
        foreach ($directories as $d) {
            if (!$d['writable']) {
                $allWritable = false;
                break;
            }
        }

        $phpVersion = PHP_VERSION;
        $phpOk = version_compare($phpVersion, '8.1.0', '>=');
        $zipOk = class_exists('ZipArchive');
        $curlOk = function_exists('curl_init');
        $opensslOk = extension_loaded('openssl');
        $isGit = is_dir($baseDir . '/.git');

        $freeBytes = @disk_free_space($baseDir);
        $diskFormatted = ($freeBytes !== false) ? self::formatBytes((int)$freeBytes) . ' free' : 'Available';

        return [
            'php_version' => $phpVersion,
            'php_ok' => $phpOk,
            'zip_ok' => $zipOk,
            'curl_ok' => $curlOk,
            'openssl_ok' => $opensslOk,
            'directories' => $directories,
            'all_writable' => $allWritable,
            'disk_space' => $diskFormatted,
            'is_git' => $isGit,
            'all_ok' => ($phpOk && $zipOk && $curlOk && $opensslOk && $allWritable),
        ];
    }

    /**
     * Query GitHub API to check for available releases or commits
     */
    public static function checkForUpdates(bool $forceRefresh = false): array
    {
        $baseDir = self::getBaseDir();
        $cacheFile = $baseDir . '/storage/framework/update_cache.json';
        $currentInfo = self::getCurrentVersionInfo();
        $currentVer = ltrim($currentInfo['version'], 'v');
        $currentCommit = substr((string) ($currentInfo['commit'] ?? 'HEAD'), 0, 7);
        $repo = self::getRepo();
        $branch = self::getBranch();

        // 1. Return cached result if fresh (< 15 minutes) and not forced
        if (!$forceRefresh && file_exists($cacheFile)) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($cached) && !empty($cached['cached_at']) && (time() - $cached['cached_at']) < 900) {
                return $cached;
            }
        }

        $hasUpdate = false;
        $latestVer = $currentVer;
        $latestCommit = $currentCommit;
        $releaseName = 'Latest Release';
        $releaseNotes = '';
        $publishedAt = '';
        $downloadUrl = '';
        $htmlUrl = "https://github.com/{$repo}";
        $updateType = 'release';
        $commitsBehind = 0;
        $recentCommits = [];
        $rateLimitInfo = null;
        $errorMessage = null;

        // 2. Query GitHub Releases
        $releaseUrl = "https://api.github.com/repos/{$repo}/releases/latest";
        $apiRes = self::httpGet($releaseUrl);
        $rateLimitInfo = self::extractRateLimitHeaders();

        if ($apiRes['status'] === 200) {
            $rel = json_decode($apiRes['body'], true);
            if (is_array($rel) && !empty($rel['tag_name'])) {
                $rawTag = (string) $rel['tag_name'];
                $cleanTag = ltrim($rawTag, 'v');
                $latestVer = $cleanTag;
                $releaseName = (string) ($rel['name'] ?? ('Release ' . $rawTag));
                $releaseNotes = (string) ($rel['body'] ?? 'No release notes provided.');
                $publishedAt = (string) ($rel['published_at'] ?? date('Y-m-d H:i:s'));
                $downloadUrl = (string) ($rel['zipball_url'] ?? "https://github.com/{$repo}/archive/refs/tags/{$rawTag}.zip");
                $htmlUrl = (string) ($rel['html_url'] ?? "https://github.com/{$repo}/releases/tag/{$rawTag}");

                // Semantic version comparison
                if (version_compare($latestVer, $currentVer, '>')) {
                    $hasUpdate = true;
                }
            }
        } elseif ($apiRes['status'] === 404 || $apiRes['status'] === 403 || $apiRes['status'] === 0) {
            // Check branch commits directly
            $commitsUrl = "https://api.github.com/repos/{$repo}/commits/{$branch}";
            $commitRes = self::httpGet($commitsUrl);
            $rateLimitInfo = self::extractRateLimitHeaders();

            if ($commitRes['status'] === 200) {
                $c = json_decode($commitRes['body'], true);
                if (is_array($c) && !empty($c['sha'])) {
                    $latestSha = substr((string) $c['sha'], 0, 7);
                    $latestCommit = $latestSha;
                    $updateType = 'commit';
                    $latestVer = $latestSha;
                    $releaseName = 'Branch Commit: ' . $latestSha;
                    $releaseNotes = (string) ($c['commit']['message'] ?? 'Direct branch commit update.');
                    $publishedAt = (string) ($c['commit']['committer']['date'] ?? date('Y-m-d H:i:s'));
                    $downloadUrl = "https://github.com/{$repo}/archive/refs/heads/{$branch}.zip";
                    $htmlUrl = "https://github.com/{$repo}/commit/{$c['sha']}";

                    // Determine if update is available:
                    // If current commit is not set or differs from latest upstream commit
                    if ($currentCommit === 'HEAD') {
                        // Compare latestVer with current version string
                        $hasUpdate = ($latestSha !== $currentVer);
                    } else {
                        $hasUpdate = ($latestSha !== $currentCommit);
                    }

                    // If an update is available and we have a valid current commit, fetch comparison details
                    if ($hasUpdate && $currentCommit !== 'HEAD' && $currentCommit !== '') {
                        $compareUrl = "https://api.github.com/repos/{$repo}/compare/{$currentCommit}...{$latestSha}";
                        $compareRes = self::httpGet($compareUrl);
                        if ($compareRes['status'] === 200) {
                            $cmp = json_decode($compareRes['body'], true);
                            if (is_array($cmp)) {
                                $commitsBehind = (int) ($cmp['ahead_by'] ?? 1);
                                if (!empty($cmp['commits']) && is_array($cmp['commits'])) {
                                    foreach (array_slice(array_reverse($cmp['commits']), 0, 5) as $cm) {
                                        $recentCommits[] = [
                                            'sha' => substr((string) $cm['sha'], 0, 7),
                                            'message' => explode("\n", (string) ($cm['commit']['message'] ?? ''))[0],
                                            'author' => (string) ($cm['commit']['author']['name'] ?? 'Dev'),
                                            'date' => (string) ($cm['commit']['author']['date'] ?? ''),
                                            'url' => (string) ($cm['html_url'] ?? ''),
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            } elseif ($commitRes['status'] === 403) {
                $errorMessage = "GitHub API rate limit exceeded (60 req/hr). Add a Personal Access Token in Updater Settings below.";
            } elseif ($commitRes['status'] === 0) {
                $errorMessage = "Unable to connect to api.github.com. Check server internet connectivity.";
            } else {
                $errorMessage = "GitHub API error (HTTP {$commitRes['status']}). Verify repository path [{$repo}].";
            }
        }

        $result = [
            'success' => ($errorMessage === null),
            'has_update' => $hasUpdate,
            'current_version' => $currentInfo['version'],
            'current_commit' => $currentCommit,
            'latest_version' => $latestVer,
            'latest_commit' => $latestCommit,
            'release_name' => $releaseName,
            'release_notes' => $releaseNotes,
            'published_at' => $publishedAt,
            'download_url' => $downloadUrl,
            'html_url' => $htmlUrl,
            'update_type' => $updateType,
            'commits_behind' => $commitsBehind,
            'recent_commits' => $recentCommits,
            'repo' => $repo,
            'branch' => $branch,
            'checked_at' => date('Y-m-d H:i:s'),
            'cached_at' => time(),
            'rate_limit' => $rateLimitInfo,
            'error' => $errorMessage,
        ];

        // Cache successful checks
        if ($result['success']) {
            $dir = dirname($cacheFile);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            @file_put_contents($cacheFile, json_encode($result, JSON_PRETTY_PRINT));
        }

        return $result;
    }

    /**
     * Download a remote release ZIP archive to local storage
     */
    public static function downloadRelease(string $downloadUrl, ?string $targetFile = null): string
    {
        $baseDir = self::getBaseDir();
        $updatesDir = $baseDir . '/storage/updates';
        if (!is_dir($updatesDir)) {
            @mkdir($updatesDir, 0755, true);
        }

        if (!$targetFile) {
            $targetFile = $updatesDir . '/update_' . date('Ymd_His') . '.zip';
        }

        // Direct stream to disk to keep RAM usage near 0 MB during large archive downloads
        if (self::$mockHttpClient === null && function_exists('curl_init')) {
            $fp = @fopen($targetFile, 'wb');
            if (!$fp) {
                throw new RuntimeException("Failed to open update archive target [{$targetFile}] for writing.");
            }

            $ch = curl_init($downloadUrl);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $headers = ['User-Agent: ClassAid-Updater/1.0', 'Accept: application/octet-stream'];
            $token = (string) Config::get('GITHUB_TOKEN', '');
            if (trim($token) !== '') {
                $headers[] = 'Authorization: Bearer ' . trim($token);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $success = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            fclose($fp);

            if (!$success || $status < 200 || $status >= 300 || filesize($targetFile) === 0) {
                @unlink($targetFile);
                throw new RuntimeException("Failed to stream download update archive from GitHub (HTTP {$status}: {$err}).");
            }

            return $targetFile;
        }

        $res = self::httpGet($downloadUrl, ['Accept' => 'application/octet-stream']);
        if ($res['status'] !== 200 || empty($res['body'])) {
            throw new RuntimeException("Failed to download update archive from GitHub (HTTP {$res['status']}).");
        }

        if (@file_put_contents($targetFile, $res['body']) === false) {
            throw new RuntimeException("Failed to write update archive to [{$targetFile}]. Check directory permissions.");
        }

        return $targetFile;
    }

    /**
     * Create a pre-update backup of current code and SQLite database
     */
    public static function createBackup(string $note = 'Pre-update safety backup'): array
    {
        $baseDir = self::getBaseDir();
        $backupsDir = $baseDir . '/storage/backups';
        if (!is_dir($backupsDir)) {
            @mkdir($backupsDir, 0755, true);
        }

        $timestamp = date('Ymd_His');
        $currentInfo = self::getCurrentVersionInfo();
        $verSlug = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $currentInfo['version']);
        $zipFilename = "backup_v{$verSlug}_{$timestamp}.zip";
        $zipPath = $backupsDir . '/' . $zipFilename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Cannot create backup zip archive at [{$zipPath}].");
        }

        // Directories to include in code backup
        $includeDirs = ['src', 'public', 'views', 'database'];
        $includeFiles = ['composer.json', 'version.json'];

        foreach ($includeFiles as $f) {
            $full = $baseDir . '/' . $f;
            if (file_exists($full)) {
                $zip->addFile($full, $f);
            }
        }

        foreach ($includeDirs as $dir) {
            $fullDir = $baseDir . '/' . $dir;
            if (!is_dir($fullDir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                $itemPath = (string) $item->getPathname();
                $relPath = ltrim(substr($itemPath, strlen($baseDir)), '/\\');
                $normRel = str_replace('\\', '/', $relPath);

                // Exclude sqlite database files from code zip (handled separately below)
                if (str_ends_with($normRel, '.sqlite') || str_ends_with($normRel, '.sqlite-journal')) {
                    continue;
                }

                if ($item->isDir()) {
                    $zip->addEmptyDir($normRel);
                } elseif ($item->isFile()) {
                    $zip->addFile($itemPath, $normRel);
                }
            }
        }

        $zip->close();

        // Snapshot SQLite database if present
        $dbSnapshotFile = null;
        $sqlitePath = $baseDir . '/' . ltrim((string) Config::get('DB_DATABASE', 'database/database.sqlite'), '/');
        if (file_exists($sqlitePath)) {
            $dbSnapshotFile = "db_{$verSlug}_{$timestamp}.sqlite";
            @copy($sqlitePath, $backupsDir . '/' . $dbSnapshotFile);
        }

        $record = [
            'filename' => $zipFilename,
            'db_snapshot' => $dbSnapshotFile,
            'version' => $currentInfo['version'],
            'note' => $note,
            'size' => file_exists($zipPath) ? filesize($zipPath) : 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Append to backups registry
        $registryFile = $backupsDir . '/backups.json';
        $existing = [];
        if (file_exists($registryFile)) {
            $existing = json_decode((string) file_get_contents($registryFile), true) ?: [];
        }
        array_unshift($existing, $record);
        @file_put_contents($registryFile, json_encode($existing, JSON_PRETTY_PRINT));

        return $record;
    }

    /**
     * Get list of previous backups and restore points
     */
    public static function getBackups(): array
    {
        $baseDir = self::getBaseDir();
        $registryFile = $baseDir . '/storage/backups/backups.json';
        if (!file_exists($registryFile)) {
            return [];
        }
        $data = json_decode((string) file_get_contents($registryFile), true);
        return is_array($data) ? $data : [];
    }

    /**
     * Delete a backup snapshot
     */
    public static function deleteBackup(string $filename): bool
    {
        $baseDir = self::getBaseDir();
        $backupsDir = $baseDir . '/storage/backups';
        $clean = basename($filename);

        $zipPath = $backupsDir . '/' . $clean;
        if (file_exists($zipPath)) {
            @unlink($zipPath);
        }

        $registryFile = $backupsDir . '/backups.json';
        if (file_exists($registryFile)) {
            $registry = json_decode((string) file_get_contents($registryFile), true) ?: [];
            $filtered = [];
            foreach ($registry as $rec) {
                if (($rec['filename'] ?? '') === $clean) {
                    if (!empty($rec['db_snapshot'])) {
                        $dbSnap = $backupsDir . '/' . $rec['db_snapshot'];
                        if (file_exists($dbSnap)) {
                            @unlink($dbSnap);
                        }
                    }
                    continue;
                }
                $filtered[] = $rec;
            }
            @file_put_contents($registryFile, json_encode($filtered, JSON_PRETTY_PRINT));
        }

        return true;
    }

    /**
     * Execute full in-place update process
     */
    public static function applyUpdate(?string $customZipPath = null): array
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '256M');

        $baseDir = self::getBaseDir();
        $maintenanceFlag = $baseDir . '/storage/maintenance.flag';
        $logFile = $baseDir . '/storage/logs/updates.log';
        $steps = [];
        $backupRecord = null;

        $logStep = function (string $msg) use (&$steps, $logFile) {
            $entry = ['time' => date('Y-m-d H:i:s'), 'message' => $msg];
            $steps[] = $entry;
            $dir = dirname($logFile);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            @file_put_contents($logFile, "[{$entry['time']}] {$msg}\n", FILE_APPEND);
        };

        try {
            $logStep("Initiating in-place software update sequence...");

            // Step 1: Pre-flight health and write permissions check
            $health = self::getPreFlightHealth();
            if (!$health['zip_ok']) {
                throw new RuntimeException("PHP ZipArchive extension is required for in-place updates.");
            }
            if (!$health['all_writable']) {
                $unwritable = [];
                foreach ($health['directories'] as $k => $d) {
                    if (!$d['writable']) {
                        $unwritable[] = $d['path'];
                    }
                }
                throw new RuntimeException("Directory permissions error: [" . implode(', ', $unwritable) . "] are not writable. Please check permissions.");
            }
            $logStep("Pre-flight system health checks passed.");

            // Step 2: Enable maintenance mode
            @touch($maintenanceFlag);
            $logStep("Maintenance mode flag engaged.");

            // Step 3: Create pre-update backup
            $logStep("Generating pre-update safety backup snapshot...");
            $backupRecord = self::createBackup("Pre-update safety backup");
            $logStep("Safety snapshot created: {$backupRecord['filename']} (" . number_format($backupRecord['size'] / 1024, 1) . " KB)");

            // Step 4: Resolve update ZIP
            $zipPath = $customZipPath;
            $targetVer = null;
            $targetCommit = null;
            $releaseName = null;

            if (!$zipPath || !file_exists($zipPath)) {
                $logStep("Connecting to upstream GitHub repository [" . self::getRepo() . "]...");
                $check = self::checkForUpdates(true);
                if (empty($check['download_url'])) {
                    throw new RuntimeException("No valid download URL found. GitHub check returned: " . ($check['error'] ?? 'empty download link'));
                }
                $targetVer = $check['latest_version'];
                $targetCommit = $check['latest_commit'];
                $releaseName = $check['release_name'];

                $logStep("Downloading update archive from [{$check['download_url']}]...");
                $zipPath = self::downloadRelease($check['download_url']);
                $logStep("Archive downloaded successfully: " . number_format(filesize($zipPath) / 1024, 1) . " KB.");
            } else {
                $logStep("Using uploaded package archive: [{$zipPath}] (" . number_format(filesize($zipPath) / 1024, 1) . " KB)");
            }

            // Step 5: Safe extraction
            $logStep("Extracting update package into application directory...");
            $extractedCount = self::extractZipSafe($zipPath, $baseDir);
            $logStep("Safely extracted and synchronized {$extractedCount} files (.env and databases preserved).");

            // Step 6: Database schema and column synchronization
            $logStep("Running database schema updates and table migrations...");
            $migrationResult = self::runMigrations();
            $logStep("Database synchronization: " . ($migrationResult['message'] ?? 'OK'));

            // Step 7: Update version manifest
            if ($targetVer) {
                self::setVersion($targetVer, $releaseName, $targetCommit);
                $logStep("Application version manifest updated to [{$targetVer}] (commit {$targetCommit}).");
            } else {
                // If custom zip contained version.json, reload it
                $current = self::getCurrentVersionInfo();
                $targetVer = $current['version'];
                $logStep("Application version resolved to [{$targetVer}].");
            }

            // Step 8: Cache flushing & OPcache reset
            @unlink($baseDir . '/storage/framework/update_cache.json');
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }
            $logStep("Framework update cache and OPcache invalidated.");

            // Step 9: Disable maintenance mode
            @unlink($maintenanceFlag);
            $logStep("Maintenance mode released. In-place update completed 100% cleanly!");

            return [
                'success' => true,
                'message' => 'Application updated successfully!',
                'new_version' => $targetVer,
                'new_commit' => $targetCommit,
                'backup' => $backupRecord['filename'],
                'steps' => $steps,
            ];

        } catch (Throwable $e) {
            $errorMsg = $e->getMessage();
            $logStep("ERROR during update: {$errorMsg}");

            // Attempt automatic rollback if backup exists
            $rolledBack = false;
            if ($backupRecord && !empty($backupRecord['filename'])) {
                $logStep("Initiating automatic safe rollback from [{$backupRecord['filename']}]...");
                try {
                    self::rollback($backupRecord['filename']);
                    $rolledBack = true;
                    $logStep("Automatic rollback succeeded. Previous stable application state restored.");
                } catch (Throwable $rbErr) {
                    $logStep("CRITICAL: Automatic rollback failed: " . $rbErr->getMessage());
                }
            }

            // Ensure maintenance mode is cleared
            @unlink($maintenanceFlag);

            return [
                'success' => false,
                'error' => $errorMsg,
                'rolled_back' => $rolledBack,
                'steps' => $steps,
            ];
        }
    }

    /**
     * Safely extract ZIP archive into target directory while skipping protected files
     */
    public static function extractZipSafe(string $zipPath, string $targetDir): int
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException("Cannot open ZIP archive [{$zipPath}]. File may be corrupted.");
        }

        // Detect top-level wrapper folder (GitHub release zip archives have `{repo}-{ref}/...`)
        $prefix = '';
        if ($zip->numFiles > 0) {
            $first = $zip->getNameIndex(0);
            if ($first && str_contains($first, '/')) {
                $candidate = explode('/', $first)[0];
                $allMatch = true;
                for ($i = 0; $i < min(20, $zip->numFiles); $i++) {
                    $name = $zip->getNameIndex($i);
                    if ($name && !str_starts_with($name, $candidate . '/')) {
                        $allMatch = false;
                        break;
                    }
                }
                if ($allMatch) {
                    $prefix = $candidate . '/';
                }
            }
        }

        $extractedCount = 0;
        $prefixLen = strlen($prefix);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (!$entryName) {
                continue;
            }

            // Strip wrapper prefix
            $cleanRel = ($prefixLen > 0 && str_starts_with($entryName, $prefix))
                ? substr($entryName, $prefixLen)
                : $entryName;

            $cleanRel = trim(str_replace('\\', '/', $cleanRel), '/');
            if ($cleanRel === '') {
                continue;
            }

            // Strict protection check: DO NOT overwrite user databases, .env, or uploads
            if (self::isProtectedPath($cleanRel)) {
                continue;
            }

            $destPath = $targetDir . '/' . $cleanRel;

            // Directory entry
            if (str_ends_with($entryName, '/')) {
                if (!is_dir($destPath)) {
                    @mkdir($destPath, 0755, true);
                }
                continue;
            }

            // File entry: ensure parent directory exists
            $parentDir = dirname($destPath);
            if (!is_dir($parentDir)) {
                @mkdir($parentDir, 0755, true);
            }

            // Read and write content
            $content = $zip->getFromIndex($i);
            if ($content !== false) {
                if (@file_put_contents($destPath, $content) !== false) {
                    $extractedCount++;
                }
            }
        }

        $zip->close();
        return $extractedCount;
    }

    /**
     * Restore from a backup archive
     */
    public static function rollback(string $backupFilename): array
    {
        $baseDir = self::getBaseDir();
        $backupsDir = $baseDir . '/storage/backups';
        $zipPath = $backupsDir . '/' . basename($backupFilename);

        if (!file_exists($zipPath)) {
            throw new RuntimeException("Backup archive [{$backupFilename}] not found.");
        }

        $extracted = self::extractZipSafe($zipPath, $baseDir);

        // Find corresponding database snapshot if available
        $registryFile = $backupsDir . '/backups.json';
        if (file_exists($registryFile)) {
            $registry = json_decode((string) file_get_contents($registryFile), true) ?: [];
            foreach ($registry as $rec) {
                if (($rec['filename'] ?? '') === $backupFilename && !empty($rec['db_snapshot'])) {
                    $snapshotFile = $backupsDir . '/' . $rec['db_snapshot'];
                    $sqliteDest = $baseDir . '/' . ltrim((string) Config::get('DB_DATABASE', 'database/database.sqlite'), '/');
                    if (file_exists($snapshotFile) && file_exists($sqliteDest)) {
                        @copy($snapshotFile, $sqliteDest);
                    }
                    break;
                }
            }
        }

        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        return [
            'success' => true,
            'restored_files' => $extracted,
            'backup' => $backupFilename,
        ];
    }

    /**
     * Run database schema synchronization & incremental table/column migrations
     */
    public static function runMigrations(): array
    {
        $baseDir = self::getBaseDir();
        $dbDriver = Config::get('DB_CONNECTION', 'sqlite');
        $schemaFile = ($dbDriver === 'mysql')
            ? $baseDir . '/database/schema_mysql.sql'
            : $baseDir . '/database/schema_sqlite.sql';

        if (!file_exists($schemaFile)) {
            return ['success' => true, 'message' => 'No schema definition file found.'];
        }

        try {
            $pdo = Database::getConnection();
            $sql = (string) file_get_contents($schemaFile);

            // Execute schema in chunks to create any new tables
            if ($dbDriver === 'mysql') {
                $pdo->exec($sql);
            } else {
                $stmts = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($stmts as $stmt) {
                    if ($stmt !== '' && !str_starts_with($stmt, '--')) {
                        try {
                            $pdo->exec($stmt);
                        } catch (Throwable) {
                            // Table may already exist; continue safely
                        }
                    }
                }
            }

            return ['success' => true, 'message' => 'Schema tables synchronized successfully.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Migration notice: ' . $e->getMessage()];
        }
    }

    /**
     * Update version manifest in version.json
     */
    public static function setVersion(string $version, ?string $releaseName = null, ?string $commitSha = null): void
    {
        $baseDir = self::getBaseDir();
        $verFile = $baseDir . '/version.json';
        $current = self::getCurrentVersionInfo();

        $current['version'] = ltrim($version, 'v');
        if ($releaseName) {
            $current['release_name'] = $releaseName;
        }
        if ($commitSha) {
            $current['commit'] = $commitSha;
        }
        $current['release_date'] = date('Y-m-d');
        $current['github_repo'] = self::getRepo();
        $current['github_branch'] = self::getBranch();

        @file_put_contents($verFile, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        Config::set('APP_VERSION', $current['version']);
    }

    /**
     * Low-level HTTP GET with cURL / file_get_contents fallback & mock support
     */
    public static function httpGet(string $url, array $extraHeaders = []): array
    {
        // 1. Mock hook for tests
        if (self::$mockHttpClient !== null) {
            return (self::$mockHttpClient)($url, $extraHeaders);
        }

        $headers = array_merge([
            'User-Agent: ClassAid-Updater/1.0',
            'Accept: application/vnd.github.v3+json',
        ], $extraHeaders);

        $token = (string) Config::get('GITHUB_TOKEN', '');
        if (trim($token) !== '') {
            $headers[] = 'Authorization: Bearer ' . trim($token);
        }

        self::$lastResponseHeaders = [];

        // 2. Use cURL if available
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);

            $curlHeaders = [];
            foreach ($headers as $k => $v) {
                $curlHeaders[] = is_numeric($k) ? $v : "{$k}: {$v}";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);

            $response = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                return ['status' => 0, 'body' => '', 'error' => $err, 'headers' => []];
            }

            $rawHeaders = substr((string)$response, 0, $headerSize);
            $body = substr((string)$response, $headerSize);

            $parsedHeaders = [];
            foreach (explode("\r\n", $rawHeaders) as $h) {
                if (str_contains($h, ':')) {
                    [$hk, $hv] = explode(':', $h, 2);
                    $parsedHeaders[strtolower(trim($hk))] = trim($hv);
                }
            }
            self::$lastResponseHeaders = $parsedHeaders;

            return ['status' => $status, 'body' => (string) $body, 'headers' => $parsedHeaders];
        }

        // 3. Fallback to file_get_contents with stream context
        $flatHeaders = '';
        foreach ($headers as $k => $v) {
            $flatHeaders .= (is_numeric($k) ? $v : "{$k}: {$v}") . "\r\n";
        }

        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $flatHeaders,
                'timeout' => 30,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $ctx);
        $status = 0;
        $parsedHeaders = [];
        if (isset($http_response_header) && is_array($http_response_header)) {
            if (preg_match('|HTTP/\d\.\d\s+(\d+)|', $http_response_header[0], $m)) {
                $status = (int) $m[1];
            }
            foreach ($http_response_header as $h) {
                if (str_contains($h, ':')) {
                    [$hk, $hv] = explode(':', $h, 2);
                    $parsedHeaders[strtolower(trim($hk))] = trim($hv);
                }
            }
        }
        self::$lastResponseHeaders = $parsedHeaders;

        return ['status' => $status, 'body' => (string) $body, 'headers' => $parsedHeaders];
    }

    /**
     * Extract rate limit information from last response
     */
    public static function extractRateLimitHeaders(): array
    {
        $h = self::$lastResponseHeaders;
        return [
            'limit' => isset($h['x-ratelimit-limit']) ? (int) $h['x-ratelimit-limit'] : null,
            'remaining' => isset($h['x-ratelimit-remaining']) ? (int) $h['x-ratelimit-remaining'] : null,
            'reset_time' => isset($h['x-ratelimit-reset']) ? date('H:i:s', (int) $h['x-ratelimit-reset']) : null,
        ];
    }

    /**
     * Helper to format bytes into readable units
     */
    public static function formatBytes(int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
