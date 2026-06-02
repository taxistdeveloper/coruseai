<?php

declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (!auth_user()) {
            flash('error', 'Войдите в систему.');
            redirect('/login');
        }
    }
}
