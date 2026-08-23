<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Container;
use App\Models\User;

class Auth
{
    public function __construct(private Container $c) {}

    public function user(): ?array { return $_SESSION['user'] ?? null; }
    public function id(): ?int { return isset($this->user()['id']) ? (int)$this->user()['id'] : null; }
    public function role(): ?string { return $this->user()['role'] ?? null; }
    public function check(): bool { return $this->user() !== null; }

    public function requireLogin(): void
    {
        if (!$this->check()) {
            header('Location: ' . $this->c->get('config')['base_url'] . 'login');
            exit;
        }
    }

    public function attempt(string $email, string $password): bool
    {
        $u = User::byEmail($this->c->get('db'), $email);
        if (!$u || !password_verify($password, $u['password_hash'])) return false;
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$u['id'],
            'name' => $u['name'],
            'email' => $u['email'],
            'role' => $u['role'],
        ];
        return true;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
    }

    public function requireRole(string ...$roles): void
    {
        $this->requireLogin();
        if (!in_array($this->role(), $roles, true)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }
}
