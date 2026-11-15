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
                // Filter string keys for parameters and convert to positional array
                $params = array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));

                // Run middleware (e.g. auth check)
                if (in_array('auth', $route['middleware'], true)) {
                    if (!Session::check()) {
                        Session::flash('error', 'Please sign in to access this page.');
                        Response::redirect('/login');
                    }

                    // Paywall & Subscription Verification Gate (for Teachers)
                    if (!Session::isSuperAdmin()) {
                        $exemptPrefixes = [
                            '/subscription',
                            '/profile',
                            '/logout',
                            '/api/notifications'
                        ];

                        $isExempt = false;
                        foreach ($exemptPrefixes as $ep) {
                            if ($path === $ep || str_starts_with($path, $ep . '/')) {
                                $isExempt = true;
                                break;
                            }
                        }

                        if (!$isExempt) {
                            $userId = (int) Session::userId();
                            // Run renewal checks (7-day pre-expiry invoice generation & 7-day grace period suspension)
                            $unpaidInvoice = \App\Models\Subscription::checkAndProcessRenewals($userId);

                            $user = \App\Models\User::findById($userId);
                            $isSuspended = ($user && $user['status'] === 'suspended');

                            if ($isSuspended) {
                                Session::flash('error', 'Your account is suspended due to an overdue renewal invoice. All your classroom records and student marks are completely safe and preserved. Settle your invoice to immediately restore full access.');
                                if ($unpaidInvoice) {
                                    Response::redirect('/subscription/invoice/' . $unpaidInvoice['id']);
                                } else {
                                    Response::redirect('/subscription');
                                }
                                return;
                            }

                            if (!\App\Models\Subscription::isUserUnlocked($userId)) {
                                if ($unpaidInvoice) {
                                    Session::flash('warning', 'Your plan has expired. Please settle your renewal invoice within the 7-day grace period to continue uninterrupted.');
                                    Response::redirect('/subscription/invoice/' . $unpaidInvoice['id']);
                                } else {
                                    Session::flash('warning', 'Payment verification required. Your workspace features are locked until your subscription is active.');
                                    Response::redirect('/subscription/paywall');
                                }
                                return;
                            }
                        }
                    }
                }

                if (in_array('guest', $route['middleware'], true)) {
                    if (Session::check()) {
                        Response::redirect(Session::isSuperAdmin() ? '/admin' : '/dashboard');
                    }
                }

                // Super Admin check
                if (in_array('super_admin', $route['middleware'], true)) {
                    if (!Session::check()) {
                        Session::flash('error', 'Please sign in with administrator credentials.');
                        Response::redirect('/login');
                    }
                    if (!Session::isSuperAdmin()) {
                        Session::flash('error', 'Access denied. Administrator privileges required.');
                        Response::redirect('/dashboard');
                    }
                }

                // If POST and csrf validation is requested
                if ($method === 'POST' && !in_array('no_csrf', $route['middleware'], true)) {
                    if (!$request->validateCsrf()) {
                        if ($request->isJson()) {
                            Response::json([
                                'success' => false,
                                'message' => 'Invalid or expired security token (CSRF). Please refresh the page and try again.',
                            ], 403);
                            return;
                        }
                        Session::flash('error', 'Invalid security token (CSRF). Please try again.');
                        Response::redirect($path);
                        return;
                    }
                }

                $args = array_merge([$request], $params);

                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func_array($handler, $args);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    call_user_func_array([$controller, $action], $args);
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        View::render('errors.404', ['title' => 'Page Not Found', 'path' => $path], 'auth');
    }
}
