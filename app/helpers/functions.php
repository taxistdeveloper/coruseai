<?php

declare(strict_types=1);

function config(string $key, mixed $default = null): mixed
{
    static $app;
    if ($app === null) {
        $app = require APP_PATH . '/config/app.php';
    }

    $keys = explode('.', $key);
    $value = $app;
    foreach ($keys as $k) {
        if (!is_array($value) || !array_key_exists($k, $value)) {
            return $default;
        }
        $value = $value[$k];
    }
    return $value;
}

function editor_config(string $key, mixed $default = null): mixed
{
    return config('editor.' . $key, $default);
}

function base_url(string $path = ''): string
{
    $base = rtrim(config('url', ''), '/');
    $path = ltrim($path, '/');
    return $path === '' ? $base : $base . '/' . $path;
}

function asset(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

/** Путь запроса для роутера (без web_base и /public). */
function request_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $webBase = rtrim((string) config('web_base', ''), '/');

    if ($webBase !== '' && str_starts_with($uri, $webBase)) {
        $uri = substr($uri, strlen($webBase)) ?: '/';
    }

    $publicPrefix = '/public';
    if (str_starts_with($uri, $publicPrefix)) {
        $uri = substr($uri, strlen($publicPrefix)) ?: '/';
    }

    $uri = '/' . trim($uri, '/');
    return $uri === '/' ? '/' : rtrim($uri, '/');
}

function view(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $file = APP_PATH . '/views/' . str_replace('.', '/', $name) . '.php';
    if (!file_exists($file)) {
        http_response_code(500);
        echo "View not found: {$name}";
        exit;
    }
    $__viewFile = $file;
    require APP_PATH . '/views/layouts/main.php';
}

function old(string $key, string $default = ''): string
{
    return htmlspecialchars($_SESSION['_old'][$key] ?? $default, ENT_QUOTES, 'UTF-8');
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

function verify_csrf(): bool
{
    $token = $_POST['_csrf'] ?? '';
    return hash_equals($_SESSION['_csrf'] ?? '', $token);
}

function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function auth_id(): ?int
{
    return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
}

function is_admin(): bool
{
    return (auth_user()['role'] ?? '') === 'admin';
}

function is_teacher(): bool
{
    return (auth_user()['role'] ?? '') === 'teacher';
}

function status_badge(string $status): string
{
    $map = [
        'assigned'    => ['Назначена', 'info'],
        'in_progress' => ['В процессе', 'warning'],
        'submitted'   => ['Сдана', 'success'],
        'overdue'     => ['Просрочена', 'danger'],
    ];
    [$label, $class] = $map[$status] ?? [$status, 'secondary'];
    return '<span class="badge bg-' . $class . '">' . htmlspecialchars($label) . '</span>';
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function paginate(int $total, int $page, int $perPage): array
{
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $page,
        'total_pages' => $totalPages,
        'offset'      => ($page - 1) * $perPage,
    ];
}
