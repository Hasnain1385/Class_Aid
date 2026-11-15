<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, role, school_name, status, phone, avatar, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getPasswordHash(int $id): ?string
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $hash = $stmt->fetchColumn();
        return $hash ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, role, school_name, status, phone, avatar) 
            VALUES (:name, :email, :password_hash, :role, :school_name, :status, :phone, :avatar)
        ");
        $stmt->execute([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'teacher',
            'school_name' => $data['school_name'] ?? 'Class Aid School',
            'status' => $data['status'] ?? 'active',
            'phone' => !empty($data['phone']) ? trim($data['phone']) : null,
            'avatar' => $data['avatar'] ?? null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['name'])) {
            $fields[] = 'name = :name';
            $params['name'] = trim($data['name']);
        }
        if (isset($data['email'])) {
            $fields[] = 'email = :email';
            $params['email'] = strtolower(trim($data['email']));
        }
        if (isset($data['school_name'])) {
            $fields[] = 'school_name = :school_name';
            $params['school_name'] = trim($data['school_name']);
        }
        if (isset($data['phone'])) {
            $fields[] = 'phone = :phone';
            $params['phone'] = trim($data['phone']);
        }
        if (isset($data['status'])) {
            $fields[] = 'status = :status';
            $params['status'] = $data['status'];
        }
        if (array_key_exists('avatar', $data)) {
            $fields[] = 'avatar = :avatar';
            $params['avatar'] = $data['avatar'];
        }
        if (!empty($data['password'])) {
            $fields[] = 'password_hash = :password_hash';
            $params['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET status = :status WHERE id = :id");
        return $stmt->execute(['id' => $id, 'status' => $status]);
    }

    public static function getAllTeachers(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.*,
                   (SELECT COUNT(*) FROM classes c WHERE c.teacher_id = u.id) as classes_count,
                   (SELECT COUNT(*) FROM students s JOIN classes c ON c.id = s.class_id WHERE c.teacher_id = u.id) as students_count,
                   sub.plan_code, sub.plan_name, sub.billing_cycle, sub.price, sub.status as subscription_status, sub.expires_at as subscription_expires_at
            FROM users u
            LEFT JOIN subscriptions sub ON sub.id = (
                SELECT s2.id FROM subscriptions s2 WHERE s2.user_id = u.id ORDER BY s2.id DESC LIMIT 1
            )
            WHERE u.role = 'teacher'
            ORDER BY u.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countTeachers(): int
    {
        $pdo = Database::getConnection();
        return (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role != 'super_admin'");
        return $stmt->execute(['id' => $id]);
    }

    public static function generateResetToken(int $userId): string
    {
        $pdo = Database::getConnection();
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $stmt = $pdo->prepare("UPDATE users SET reset_token = :token, reset_token_expires_at = :expires WHERE id = :id");
        $stmt->execute([
            'token' => $token,
            'expires' => $expiresAt,
            'id' => $userId,
        ]);

        return $token;
    }

    public static function findByResetToken(string $token): ?array
    {
        if (empty($token)) {
            return null;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_token_expires_at > :now LIMIT 1");
        $stmt->execute(['token' => $token, 'now' => date('Y-m-d H:i:s')]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function resetPasswordWithToken(string $token, string $newPassword): bool
    {
        $user = self::findByResetToken($token);
        if (!$user) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE users 
            SET password_hash = :hash, reset_token = NULL, reset_token_expires_at = NULL 
            WHERE id = :id
        ");
        return $stmt->execute([
            'hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => $user['id'],
        ]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
