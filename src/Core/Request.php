<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private string $method;
    private string $path;
    private array $queryParams;
    private array $postParams;
    private array $server;
    private array $files;

    public function __construct()
    {
        $rawMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->method = ($rawMethod === 'HEAD') ? 'GET' : $rawMethod;
        $this->server = $_SERVER;
        $this->queryParams = $_GET;
        $this->postParams = $_POST;
        $this->files = $_FILES;

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Strip subfolder only if app is served from a subfolder ending in index.php
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if (str_ends_with($scriptName, 'index.php')) {
            $scriptDir = rtrim(dirname($scriptName), '/\\');
            if (!empty($scriptDir) && $scriptDir !== '/' && $scriptDir !== '.' && str_starts_with($path, $scriptDir)) {
                $path = substr($path, strlen($scriptDir));
            }
        }

        $this->path = '/' . trim($path, '/');
        if ($this->path === '') {
            $this->path = '/';
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isJson(): bool
    {
        $accept = (string) ($this->server['HTTP_ACCEPT'] ?? '');
        $contentType = (string) ($this->server['CONTENT_TYPE'] ?? '');
        $requestedWith = (string) ($this->server['HTTP_X_REQUESTED_WITH'] ?? '');
        return str_contains($accept, 'application/json') 
            || str_contains($contentType, 'application/json') 
            || strtolower($requestedWith) === 'xmlhttprequest';
    }

    public function isAjax(): bool
    {
        $requestedWith = (string) ($this->server['HTTP_X_REQUESTED_WITH'] ?? '');
        return strtolower($requestedWith) === 'xmlhttprequest' || $this->isJson();
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    public function allQuery(): array
    {
        return $this->queryParams;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->postParams[$key] ?? $this->queryParams[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->queryParams, $this->postParams);
    }

    public function getJson(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw) {
            $data = json_decode($raw, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    public function file(string $key): ?array
    {
        return isset($this->files[$key]) && !empty($this->files[$key]['name']) ? $this->files[$key] : null;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && is_array($this->files[$key]) && !empty($this->files[$key]['tmp_name']) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function validateCsrf(): bool
    {
        $token = $this->input('_csrf_token');
        if (!$token && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        return Csrf::validate($token);
    }
}
