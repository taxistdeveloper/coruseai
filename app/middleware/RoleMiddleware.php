<?php

declare(strict_types=1);

namespace App\Middleware;

class RoleMiddleware
{
    public static function admin(): void
    {
        AuthMiddleware::handle();
        if (!is_admin()) {
            http_response_code(403);
            flash('error', 'Доступ только для администратора.');
            redirect('/teacher');
        }
    }

    public static function teacher(): void
    {
        AuthMiddleware::handle();
        if (!is_teacher()) {
            http_response_code(403);
            flash('error', 'Доступ только для преподавателя.');
            redirect('/admin');
        }
    }
}
