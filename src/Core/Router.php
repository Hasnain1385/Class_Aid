<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middleware): void
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . rtrim($pattern, '/') . '/?$#';
        if ($path === '/') {
            $pattern = '#^/?$#';
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        // 1. Check if application is installed
        $isInstalled = Config::isInstalled();
        if (!$isInstalled && !str_starts_with($path, '/install')) {
            Response::redirect('/install');
        }

        if ($isInstalled && str_starts_with($path, '/install')) {
            // Already installed, redirect to dashboard or login
            Response::redirect(Session::check() ? '/dashboard' : '/login');
        }

        // 2. Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                // Filter string keys for named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware (e.g. auth check)
                if (in_array('auth', $route['middleware'], true)) {
                    if (!Session::check()) {
                        Session::flash('error', 'Please sign in to access this page.');
                        Response::redirect('/login');
                    }
                }

                if (in_array('guest', $route['middleware'], true)) {
                    if (Session::check()) {
                        Response::redirect('/dashboard');
                    }
                }

                // If POST and csrf validation is requested
                if ($method === 'POST' && !in_array('no_csrf', $route['middleware'], true)) {
                    if (!$request->validateCsrf()) {
                        Session::flash('error', 'Invalid security token (CSRF). Please try again.');
                        Response::redirect($path);
                    }
                }

                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func_array($handler, array_merge([$request], $params));
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    call_user_func_array([$controller, $action], array_merge([$request], $params));
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        View::render('errors.404', ['title' => 'Page Not Found', 'path' => $path], 'auth');
    }
}
