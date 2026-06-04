<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function home(): void
    {
        if (!auth_user()) {
            redirect('/login');
        }
        redirect(auth_home_path());
    }

    public function showLogin(): void
    {
        if (auth_user()) {
            $this->home();
        }
        view('auth.login', ['title' => 'Вход в систему']);
    }

    public function login(): void
    {
        $this->validateCsrf();
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($login === '' || $password === '') {
            flash('error', 'Введите логин и пароль.');
            $this->rememberOld(['login' => $login]);
            redirect('/login');
        }

        $auth = new AuthService();
        if (!$auth->attempt($login, $password)) {
            flash('error', 'Неверный логин или пароль.');
            $this->rememberOld(['login' => $login]);
            redirect('/login');
        }

        $this->clearOld();
        flash('success', 'Добро пожаловать!');
        $this->home();
    }

    public function logout(): void
    {
        (new AuthService())->logout();
        flash('success', 'Вы вышли из системы.');
        redirect('/login');
    }
}
