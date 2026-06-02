<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function validateCsrf(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Недействительный CSRF-токен.');
            redirect('/');
        }
    }

    protected function rememberOld(array $data): void
    {
        $_SESSION['_old'] = $data;
    }

    protected function clearOld(): void
    {
        unset($_SESSION['_old']);
    }
}
