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
     * Get base directory of project
     */
    public static function getBaseDir(): string
    {
        return rtrim((string) Config::get('BASE_DIR', dirname(__DIR__, 2)), '/');
    }

    /**
     * Get current version information
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
            'github_repo' => (string) Config::get('GITHUB_REPO', 'class-aid/class-aid'),
            'github_branch' => (string) Config::get('GITHUB_BRANCH', 'main'),
        ];

        if (file_exists($verFile)) {
            $json = json_decode((string) file_get_contents($verFile), true);
            if (is_array($json)) {
                return array_merge($defaults, $json);
            }
        }

        return $defaults;
    }

    /**
     * Get configured GitHub repository (e.g. 'owner/repo')
     */
    public static function getRepo(): string
    {
        $repo = (string) Config::get('GITHUB_REPO', 'class-aid/class-aid');
        return trim($repo) !== '' ? trim($repo) : 'class-aid/class-aid';
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
            'database/database.sqlite',
            'database/database.sqlite-journal',
            'database/test.sqlite',
            'storage/maintenance.flag',
        ];

        if (in_array($norm, $protectedExact, true)) {
            return true;
        }

        $protectedPrefixes = [
            'public/uploads/',
            'storage/',
            '.git/',
            'database/backups/',
        ];

        foreach ($protectedPrefixes as $prefix) {
            if (str_starts_with($norm, $prefix)) {
                return true;
            }
        }

        return false;
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
        $repo = self::getRepo();
        $branch = self::getBranch();

        // 1. Return cached result if fresh (< 30 minutes) and not forced
        if (!$forceRefresh && file_exists($cacheFile)) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($cached) && !empty($cached['cached_at']) && (time() - $cached['cached_at']) < 1800) {
                return $cached;
            }
        }

        // 2. Query GitHub Releases
        $releaseUrl = "https://api.github.com/repos/{$repo}/releases/latest";
        $apiRes = self::httpGet($releaseUrl);

        $hasUpdate = false;
        $latestVer = $currentVer;
        $releaseName = 'Latest Release';
        $releaseNotes = '';
        $publishedAt = '';
        $downloadUrl = '';
        $htmlUrl = "https://github.com/{$repo}";
        $updateType = 'release';

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
        } elseif ($apiRes['status'] === 404 || $apiRes['status'] === 403) {
            // No published releases found (or rate limited on releases) -> Check branch commits
            $commitsUrl = "https://api.github.com/repos/{$repo}/commits/{$branch}";
            $commitRes = self::httpGet($commitsUrl);

            if ($commitRes['status'] === 200) {
                $c = json_decode($commitRes['body'], true);
                if (is_array($c) && !empty($c['sha'])) {
                    $latestSha = substr((string) $c['sha'], 0, 7);
                    $currentSha = substr((string) ($currentInfo['commit'] ?? 'HEAD'), 0, 7);
                    $updateType = 'commit';
                    $latestVer = $latestSha;
                    $releaseName = 'Branch commit: ' . $latestSha;
                    $releaseNotes = (string) ($c['commit']['message'] ?? 'Direct branch commit update.');
                    $publishedAt = (string) ($c['commit']['committer']['date'] ?? date('Y-m-d H:i:s'));
                    $downloadUrl = "https://github.com/{$repo}/archive/refs/heads/{$branch}.zip";
                    $htmlUrl = "https://github.com/{$repo}/commit/{$c['sha']}";

                    if ($currentSha !== 'HEAD' && $latestSha !== $currentSha) {
                        $hasUpdate = true;
                    }
                }
            }
        }

        $result = [
            'success' => true,
            'has_update' => $hasUpdate,
            'current_version' => $currentInfo['version'],
            'latest_version' => $latestVer,
            'release_name' => $releaseName,
            'release_notes' => $releaseNotes,
            'published_at' => $publishedAt,
            'download_url' => $downloadUrl,
            'html_url' => $htmlUrl,
            'update_type' => $updateType,
            'repo' => $repo,
            'branch' => $branch,
            'checked_at' => date('Y-m-d H:i:s'),
            'cached_at' => time(),
            'api_status' => $apiRes['status'],
        ];

        // Cache the successful result
        @file_put_contents($cacheFile, json_encode($result, JSON_PRETTY_PRINT));

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
    public static function createBackup(string $note = 'Pre-update backup'): array
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
     * Execute full in-place update process
     */
    public static function applyUpdate(?string $customZipPath = null): array
    {
        $baseDir = self::getBaseDir();
        $maintenanceFlag = $baseDir . '/storage/maintenance.flag';
        $logFile = $baseDir . '/storage/logs/updates.log';
        $steps = [];
        $backupRecord = null;

        $logStep = function (string $msg) use (&$steps, $logFile) {
            $entry = ['time' => date('Y-m-d H:i:s'), 'message' => $msg];
            $steps[] = $entry;
            @file_put_contents($logFile, "[{$entry['time']}] {$msg}\n", FILE_APPEND);
        };

        try {
            $logStep("Starting in-place application update...");

            // Step 1: Check environment & ZIP extension
            if (!class_exists('ZipArchive')) {
                throw new RuntimeException("PHP ZipArchive extension is required for in-place updates.");
            }

            // Step 2: Enable maintenance mode
            @touch($maintenanceFlag);
            $logStep("Maintenance mode enabled.");

            // Step 3: Create pre-update backup
            $logStep("Creating pre-update backup of files & database...");
            $backupRecord = self::createBackup("Pre-update safety backup");
            $logStep("Safety backup created successfully: {$backupRecord['filename']}");

            // Step 4: Resolve update ZIP
            $zipPath = $customZipPath;
            if (!$zipPath || !file_exists($zipPath)) {
                $logStep("Checking GitHub for latest release archive...");
                $check = self::checkForUpdates(true);
                if (empty($check['download_url'])) {
                    throw new RuntimeException("No valid download URL found for GitHub repository [{$check['repo']}].");
                }
                $logStep("Downloading release archive from [{$check['download_url']}]...");
                $zipPath = self::downloadRelease($check['download_url']);
                $logStep("Archive downloaded to [{$zipPath}] (" . number_format(filesize($zipPath) / 1024, 1) . " KB).");
            } else {
                $logStep("Using provided update archive: [{$zipPath}]");
            }

            // Step 5: Extract and overwrite files safely
            $logStep("Extracting update package into application directory...");
            $extractedCount = self::extractZipSafe($zipPath, $baseDir);
            $logStep("Extracted {$extractedCount} files safely (protected files preserved).");

            // Step 6: Run database migrations
            $logStep("Running database schema synchronization & migrations...");
            $migrationResult = self::runMigrations();
            $logStep("Database migrations completed: " . ($migrationResult['message'] ?? 'OK'));

            // Step 7: Update version.json if present in update or bump version
            $targetVer = !empty($check['latest_version']) ? $check['latest_version'] : null;
            if ($targetVer) {
                self::setVersion($targetVer, $check['release_name'] ?? ('v' . $targetVer));
                $logStep("Application version bumped to [{$targetVer}].");
            }

            // Step 8: Clear caches & invalidate update check
            @unlink($baseDir . '/storage/framework/update_cache.json');
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }
            $logStep("Application caches & OPcache flushed.");

            // Step 9: Disable maintenance mode
            @unlink($maintenanceFlag);
            $logStep("Maintenance mode disabled. In-place update completed successfully!");

            return [
                'success' => true,
                'message' => 'Application updated successfully!',
                'new_version' => $targetVer ?? self::getCurrentVersionInfo()['version'],
                'backup' => $backupRecord['filename'],
                'steps' => $steps,
            ];

        } catch (Throwable $e) {
            $errorMsg = $e->getMessage();
            $logStep("ERROR during update: {$errorMsg}");

            // Attempt automatic rollback if backup exists
            $rolledBack = false;
            if ($backupRecord && !empty($backupRecord['filename'])) {
                $logStep("Attempting automatic rollback from backup [{$backupRecord['filename']}]...");
                try {
                    self::rollback($backupRecord['filename']);
                    $rolledBack = true;
                    $logStep("Automatic rollback succeeded. Restored previous stable state.");
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
                // Check if all files start with this candidate
                $allMatch = true;
                for ($i = 0; $i < min(15, $zip->numFiles); $i++) {
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
     * Run database schema synchronization & incremental migrations
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
                // SQLite: split by semicolon and execute
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
            return ['success' => false, 'message' => 'Migration warning: ' . $e->getMessage()];
        }
    }

    /**
     * Update version in version.json
     */
    public static function setVersion(string $version, ?string $releaseName = null): void
    {
        $baseDir = self::getBaseDir();
        $verFile = $baseDir . '/version.json';
        $current = self::getCurrentVersionInfo();

        $current['version'] = ltrim($version, 'v');
        if ($releaseName) {
            $current['release_name'] = $releaseName;
        }
        $current['release_date'] = date('Y-m-d');

        @file_put_contents($verFile, json_encode($current, JSON_PRETTY_PRINT));
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

        // 2. Use cURL if available
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $curlHeaders = [];
            foreach ($headers as $k => $v) {
                $curlHeaders[] = is_numeric($k) ? $v : "{$k}: {$v}";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);

            $body = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($body === false) {
                return ['status' => 0, 'body' => '', 'error' => $err];
            }

            return ['status' => $status, 'body' => (string) $body];
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
        if (isset($http_response_header) && is_array($http_response_header)) {
            if (preg_match('|HTTP/\d\.\d\s+(\d+)|', $http_response_header[0], $m)) {
                $status = (int) $m[1];
            }
        }

        return ['status' => $status, 'body' => (string) $body];
    }
}
