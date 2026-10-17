<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function redirect(string $url, int $statusCode = 302): void
    {
        // Handle subfolder deployment if necessary
        $baseUrl = rtrim(Config::get('APP_URL', ''), '/');
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = '/' . ltrim($url, '/');
        }

        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }

    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function csv(string $filename, array $headers, array $rows): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // Add UTF-8 BOM so Excel opens it with proper encoding
        fputs($output, "\xEF\xBB\xBF");
        
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}
