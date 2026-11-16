<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    private static bool $isStarting = false;

    public static function start(): void
    {
        if (self::$isStarting) {
            return;
        }

        self::$isStarting = true;

        try {
            if (session_status() === PHP_SESSION_NONE) {
                if (!headers_sent()) {
                    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

                    @ini_set('session.use_strict_mode', '1');
                    @ini_set('session.cookie_httponly', '1');

                    session_set_cookie_params([
                        'lifetime' => 86400 * 7, // 7 days
                        'path' => '/',
                        'domain' => '',
                        'secure' => $isHttps,
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                }
                @session_start();
            }

            $isLoggedIn = !empty($_SESSION['auth_user']['id']);

            // Session Hijacking Protection: Bind session to User-Agent hash
            if (isset($_SESSION['_user_agent'])) {
                $currentAgent = hash('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'none'));
                if (!hash_equals($_SESSION['_user_agent'], $currentAgent)) {
                    unset($_SESSION['auth_user'], $_SESSION['_user_agent'], $_SESSION['_last_activity']);
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        @session_regenerate_id(true);
                    }
                    return;
                }
            } elseif ($isLoggedIn) {
                $_SESSION['_user_agent'] = hash('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'none'));
            }

            // Session-based Inactivity Locking: 4 hours (14400s) timeout for authenticated users
            if ($isLoggedIn) {
                $now = time();
                $lastActivity = (int) ($_SESSION['_last_activity'] ?? $now);
                if (($now - $lastActivity) > 14400) {
                    unset($_SESSION['auth_user'], $_SESSION['_user_agent'], $_SESSION['_last_activity']);
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        @session_regenerate_id(true);
                    }
                    $_SESSION['_flash_error'] = 'Your session has expired due to inactivity. Please log in again.';
                    return;
                }
                $_SESSION['_last_activity'] = $now;
            }
        } finally {
            self::$isStarting = false;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, ?string $message = null): ?string
    {
        self::start();
        $flashKey = "_flash_{$key}";
        if ($message !== null) {
            $_SESSION[$flashKey] = $message;
            return null;
        }

        if (isset($_SESSION[$flashKey])) {
            $msg = $_SESSION[$flashKey];
            unset($_SESSION[$flashKey]);
            return $msg;
        }

        return null;
    }

    public static function setAuthUser(array $user): void
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }
        $_SESSION['_user_agent'] = hash('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'none'));
        $_SESSION['_last_activity'] = time();

        self::set('auth_user', [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'] ?? 'teacher',
            'school_name' => $user['school_name'] ?? 'Class Aid School',
            'status' => $user['status'] ?? 'active',
            'phone' => $user['phone'] ?? null,
            'avatar' => $user['avatar'] ?? null,
            'created_at' => $user['created_at'] ?? null,
        ]);
    }

    public static function refreshAuthUser(): void
    {
        $id = self::userId();
        if ($id) {
            $fresh = \App\Models\User::findById($id);
            if ($fresh) {
                self::setAuthUser($fresh);
            }
        }
    }

    public static function isSuperAdmin(): bool
    {
        $u = self::user();
        return ($u['role'] ?? '') === 'super_admin';
    }

    public static function user(): ?array
    {
        return self::get('auth_user');
    }

    public static function userId(): ?int
    {
        $u = self::user();
        return $u['id'] ?? null;
    }

    public static function check(): bool
    {
        self::start();
        return !empty($_SESSION['auth_user']['id']);
    }

    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            self::start();
        }
        unset($_SESSION['auth_user'], $_SESSION['_user_agent'], $_SESSION['_last_activity']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }
    }
}
