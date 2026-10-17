<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\User;

class AuthController
{
    public function showLogin(Request $request): void
    {
        View::render('auth.login', ['title' => 'Sign In'], 'auth');
    }

    public function login(Request $request): void
    {
        $email = strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password');

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Please enter both email and password.');
            Response::redirect('/login');
        }

        $user = User::findByEmail($email);
        if (!$user || !User::verifyPassword($password, $user['password_hash'])) {
            Session::flash('error', 'Invalid email address or password.');
            Response::redirect('/login');
        }

        Session::setAuthUser($user);
        Session::flash('success', "Welcome back, {$user['name']}!");
        Response::redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        Session::logout();
        Session::flash('success', 'You have been logged out securely.');
        Response::redirect('/login');
    }
}
