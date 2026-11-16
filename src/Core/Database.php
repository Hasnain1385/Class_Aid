<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $connectionType = Config::get('DB_CONNECTION', 'sqlite');
        $baseDir = Config::get('BASE_DIR', dirname(__DIR__, 2));

        try {
            if ($connectionType === 'sqlite') {
                $dbPath = Config::get('DB_DATABASE', 'database/database.sqlite');
                if (!str_starts_with($dbPath, '/')) {
                    $dbPath = $baseDir . '/' . ltrim($dbPath, '/');
                }
                
                $dir = dirname($dbPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                $dsn = "sqlite:" . $dbPath;
                $pdo = new PDO($dsn, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $pdo->exec("PRAGMA foreign_keys = ON;");
                $pdo->exec("PRAGMA journal_mode = WAL;");
                $pdo->exec("PRAGMA synchronous = NORMAL;");
                $pdo->exec("PRAGMA cache_size = -2000;"); // Cap SQLite cache to ~2MB RAM
                $pdo->exec("PRAGMA temp_store = MEMORY;");
                self::$instance = $pdo;
            } elseif ($connectionType === 'mysql') {
                $host = Config::get('DB_HOST', '127.0.0.1');
                $port = Config::get('DB_PORT', '3306');
                $dbName = Config::get('DB_NAME', 'class_aid');
                $user = Config::get('DB_USER', 'root');
                $pass = Config::get('DB_PASSWORD', '');

                $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
                $pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                self::$instance = $pdo;
            } else {
                throw new RuntimeException("Unsupported DB_CONNECTION: {$connectionType}");
            }

            return self::$instance;
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection error: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public static function testConnection(string $type, array $params): array
    {
        try {
            if ($type === 'sqlite') {
                $baseDir = Config::get('BASE_DIR', dirname(__DIR__, 2));
                $path = $params['database'] ?? 'database/database.sqlite';
                if (!str_starts_with($path, '/')) {
                    $path = $baseDir . '/' . ltrim($path, '/');
                }
                $dir = dirname($path);
                if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
                    return ['success' => false, 'message' => "Cannot create directory {$dir} for SQLite database."];
                }
                if (!is_writable($dir)) {
                    return ['success' => false, 'message' => "Directory {$dir} is not writable."];
                }
                $pdo = new PDO("sqlite:{$path}");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return ['success' => true, 'message' => 'SQLite connection verified successfully.'];
            } elseif ($type === 'mysql') {
                $host = $params['host'] ?? '127.0.0.1';
                $port = $params['port'] ?? '3306';
                $dbName = $params['name'] ?? 'class_aid';
                $user = $params['user'] ?? '';
                $pass = $params['password'] ?? '';

                $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
                new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 3,
                ]);
                return ['success' => true, 'message' => "Successfully connected to MySQL database '{$dbName}'."];
            }
            return ['success' => false, 'message' => "Unknown database type."];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function runMigrations(string $type = 'sqlite'): void
    {
        $pdo = self::getConnection();
        $baseDir = Config::get('BASE_DIR', dirname(__DIR__, 2));
        $schemaFile = ($type === 'mysql') 
            ? $baseDir . '/database/schema_mysql.sql' 
            : $baseDir . '/database/schema_sqlite.sql';

        if (!file_exists($schemaFile)) {
            throw new RuntimeException("Migration file not found: {$schemaFile}");
        }

        $sql = file_get_contents($schemaFile);
        if ($sql === false) {
            throw new RuntimeException("Could not read schema file.");
        }

        if ($type === 'sqlite') {
            $pdo->exec($sql);
        } else {
            // For MySQL, split statements by semicolon if needed
            $pdo->exec($sql);
        }
    }

    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}
