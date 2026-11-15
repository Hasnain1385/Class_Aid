<?php

declare(strict_types=1);

namespace App\Core;

class Config
{
    private static array $settings = [];
    private static bool $loaded = false;
    private static string $envPath = '';

    public static function load(string $baseDir): void
    {
        if (self::$loaded) {
            return;
        }

        self::$envPath = rtrim($baseDir, '/') . '/.env';
        self::$settings = [
            'BASE_DIR' => $baseDir,
            'APP_NAME' => 'Class Aid',
            'APP_VERSION' => '1.0.0',
            'APP_ENV' => 'production',
            'APP_KEY' => '',
            'APP_DEBUG' => false,
            'APP_URL' => 'http://localhost:8000',
            'APP_INSTALLED' => false,
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => 'database/database.sqlite',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_NAME' => 'class_aid',
            'DB_USER' => 'root',
            'DB_PASSWORD' => '',
            'GITHUB_REPO' => 'class-aid/class-aid',
            'GITHUB_BRANCH' => 'main',
            'GITHUB_TOKEN' => '',
        ];

        // Load version from version.json if present
        $versionFile = rtrim($baseDir, '/') . '/version.json';
        if (file_exists($versionFile)) {
            $verData = json_decode((string) file_get_contents($versionFile), true);
            if (is_array($verData) && !empty($verData['version'])) {
                self::$settings['APP_VERSION'] = (string) $verData['version'];
                if (!empty($verData['github_repo'])) {
                    self::$settings['GITHUB_REPO'] = (string) $verData['github_repo'];
                }
            }
        }

        if (file_exists(self::$envPath)) {
            $lines = file(self::$envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines !== false) {
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || str_starts_with($line, '#')) {
                        continue;
                    }
                    if (str_contains($line, '=')) {
                        [$key, $val] = explode('=', $line, 2);
                        $key = trim($key);
                        $val = trim($val);
                        // Strip quotes
                        if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                            (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                            $val = substr($val, 1, -1);
                        }
                        if ($val === 'true') {
                            $val = true;
                        } elseif ($val === 'false') {
                            $val = false;
                        } elseif ($val === 'null') {
                            $val = null;
                        }
                        self::$settings[$key] = $val;
                    }
                }
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$settings[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::$settings[$key] = $value;
    }

    public static function isInstalled(): bool
    {
        if (!self::get('APP_INSTALLED', false) || !file_exists(self::$envPath)) {
            return false;
        }

        try {
            $pdo = Database::getConnection();
            $count = (int) $pdo->query("SELECT count(*) FROM users")->fetchColumn();
            return $count > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function writeEnv(array $data): bool
    {
        $merged = array_merge(self::$settings, $data);
        unset($merged['BASE_DIR']);

        $content = "# Class Aid Configuration File\n";
        $content .= "# Generated: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($merged as $k => $v) {
            self::$settings[$k] = $v;
            if (is_bool($v)) {
                $valStr = $v ? 'true' : 'false';
            } elseif (is_null($v)) {
                $valStr = '';
            } elseif (str_contains((string) $v, ' ') || str_contains((string) $v, '#') || str_contains((string) $v, '=')) {
                $valStr = '"' . addcslashes((string) $v, '"') . '"';
            } else {
                $valStr = (string) $v;
            }
            $content .= "{$k}={$valStr}\n";
        }

        return file_put_contents(self::$envPath, $content) !== false;
    }
}
