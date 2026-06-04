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
            redirect(auth_home_path());
        }
    }

    public static function adminOrAcademic(): void
    {
        AuthMiddleware::handle();
        if (!is_staff()) {
            http_response_code(403);
            flash('error', 'Недостаточно прав для этого раздела.');
            redirect(auth_home_path());
        }
    }

    public static function teacher(): void
    {
        AuthMiddleware::handle();
        if (!is_teacher()) {
            http_response_code(403);
            flash('error', 'Доступ только для преподавателя.');
            redirect(auth_home_path());
        }
    }
}
