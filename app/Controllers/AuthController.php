<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\User;

class AuthController
{
    public function __construct(private \App\Core\Container $c) {}

    public function loginForm(): void
    {
        View::render('auth/login', ['title' => 'Login', 'csrf' => $this->c->get('csrf')]);
    }

    public function registerForm(): void
    {
        View::render('auth/register', ['title' => 'Sign Up', 'csrf' => $this->c->get('csrf')]);
    }

    public function login(): void
    {
        $csrf = $this->c->get('csrf');
        if (!$csrf->validate($_POST['_token'] ?? '')) {
            http_response_code(419);
            exit('Invalid request token');
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!$this->c->get('rate')->hit('login:' . $ip, $ip, 10, 900)) {
            http_response_code(429);
            header('Retry-After: 900');
            exit('Too many login attempts. Please try again later.');
        }

        $email = strtolower(trim($_POST['email'] ?? ''));
        $ok = $this->c->get('auth')->attempt($email, $_POST['password'] ?? '');
        if (!$ok) {
            $_SESSION['flash'] = 'Invalid email/password.';
            header('Location: ' . $this->c->get('config')['base_url'] . 'login');
            return;
        }

        $role = $this->c->get('auth')->role();
        $base = $this->c->get('config')['base_url'];
        header('Location: ' . $base . ($role === 'admin' ? 'panel/admin' : ($role === 'mod' ? 'panel/mod' : 'me')));
    }

    public function register(): void
    {
        $csrf = $this->c->get('csrf');
        if (!$csrf->validate($_POST['_token'] ?? '')) {
            http_response_code(419);
            exit('Invalid request token');
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!$this->c->get('rate')->hit('register:' . $ip, $ip, 5, 900)) {
            http_response_code(429);
            header('Retry-After: 900');
            exit('Too many registration attempts. Please try again later.');
        }

        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pass = $_POST['password'] ?? '';
        if ($name === '' || strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 12) {
            $_SESSION['flash'] = 'Please provide valid registration details. Passwords must be at least 12 characters.';
            header('Location: ' . $this->c->get('config')['base_url'] . 'register');
            return;
        }

        if (User::byEmail($this->c->get('db'), $email)) {
            $_SESSION['flash'] = 'Email already in use.';
            header('Location: ' . $this->c->get('config')['base_url'] . 'register');
            return;
        }

        User::create($this->c->get('db'), $name, $email, $pass);
        $this->c->get('auth')->attempt($email, $pass);
        header('Location: ' . $this->c->get('config')['base_url'] . 'me');
    }

    public function logout(): void
    {
        if (!$this->c->get('csrf')->validate($_POST['_token'] ?? '')) {
            http_response_code(419);
            exit('Invalid request token');
        }

        $this->c->get('auth')->logout();
        header('Location: ' . $this->c->get('config')['base_url']);
    }
}
