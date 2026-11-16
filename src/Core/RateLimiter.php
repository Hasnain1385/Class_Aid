<?php

declare(strict_types=1);

namespace App\Core;

class RateLimiter
{
    private static string $storageDir = '';

    private static function init(): void
    {
        if (empty(self::$storageDir)) {
            $baseDir = (string) Config::get('BASE_DIR', dirname(__DIR__, 2));
            self::$storageDir = rtrim($baseDir, '/') . '/storage/framework/rate_limits';
            if (!is_dir(self::$storageDir)) {
                @mkdir(self::$storageDir, 0755, true);
            }
        }
    }

    /**
     * Determine if the given key has exceeded the maximum attempts.
     */
    public static function tooManyAttempts(string $key, int $maxAttempts = 5): bool
    {
        return self::attempts($key) >= $maxAttempts;
    }

    /**
     * Check if request can proceed (attempts < maxAttempts).
     */
    public static function check(string $key, int $maxAttempts = 5): bool
    {
        return !self::tooManyAttempts($key, $maxAttempts);
    }

    /**
     * Get the number of recorded attempts for the given key.
     */
    public static function attempts(string $key): int
    {
        self::init();
        $file = self::getFilePath($key);
        if (!file_exists($file)) {
            return 0;
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return 0;
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            return 0;
        }

        // Expired window check
        if (time() > (int) ($data['reset_at'] ?? 0)) {
            @unlink($file);
            return 0;
        }

        return (int) ($data['attempts'] ?? 0);
    }

    /**
     * Increment the attempts for a given key.
     */
    public static function hit(string $key, int $decaySeconds = 60): int
    {
        self::init();
        $file = self::getFilePath($key);
        $now = time();

        $data = ['attempts' => 0, 'reset_at' => $now + $decaySeconds];
        if (file_exists($file)) {
            $existing = json_decode((string) @file_get_contents($file), true);
            if (is_array($existing) && $now <= (int) ($existing['reset_at'] ?? 0)) {
                $data = $existing;
            }
        }

        $data['attempts'] = (int) ($data['attempts'] ?? 0) + 1;
        @file_put_contents($file, json_encode($data), LOCK_EX);

        return $data['attempts'];
    }

    /**
     * Clear / reset the attempts for the given key.
     */
    public static function clear(string $key): void
    {
        self::init();
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Alias for clear().
     */
    public static function reset(string $key): void
    {
        self::clear($key);
    }

    /**
     * Get the number of seconds until the "key" is released.
     */
    public static function availableIn(string $key): int
    {
        self::init();
        $file = self::getFilePath($key);
        if (!file_exists($file)) {
            return 0;
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return 0;
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            return 0;
        }

        $remaining = (int) ($data['reset_at'] ?? 0) - time();
        return max(0, $remaining);
    }

    private static function getFilePath(string $key): string
    {
        $hash = hash('sha256', $key);
        return self::$storageDir . '/' . $hash . '.json';
    }
}
