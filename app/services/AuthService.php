<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;

class AuthService
{
    private User $users;
    private AuditService $audit;

    public function __construct()
    {
        $this->users = new User();
        $this->audit = new AuditService();
    }

    public function attempt(string $login, string $password): bool
    {
        $user = $this->users->findByLogin($login);
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        unset($user['password']);
        if ($user['role'] === 'teacher') {
            $teacher = (new Teacher())->findByUserId((int) $user['id']);
            $user['teacher_id'] = $teacher['id'] ?? null;
        }

        $_SESSION['user'] = $user;
        $this->audit->record('login', 'user', (int) $user['id']);
        return true;
    }

    public function logout(): void
    {
        if (auth_id()) {
            $this->audit->record('logout', 'user', auth_id());
        }
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }
}
