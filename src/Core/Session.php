<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 86400 * 7, // 7 days
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
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
        self::set('auth_user', [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'] ?? 'teacher',
            'school_name' => $user['school_name'] ?? 'Class Aid School',
        ]);
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
        return self::has('auth_user') && !empty(self::get('auth_user')['id']);
    }

    public static function logout(): void
    {
        self::start();
        unset($_SESSION['auth_user']);
        session_regenerate_id(true);
    }
}
