<?php

declare(strict_types=1);

define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOAD_PATH', STORAGE_PATH . '/uploads');

session_start();

if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

require_once APP_PATH . '/helpers/functions.php';

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\Controllers\\' => APP_PATH . '/controllers/',
        'App\\Models\\'     => APP_PATH . '/models/',
        'App\\Middleware\\' => APP_PATH . '/middleware/',
        'App\\Services\\'   => APP_PATH . '/services/',
        'App\\Core\\'       => APP_PATH . '/core/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

$config = require APP_PATH . '/config/app.php';
date_default_timezone_set($config['timezone'] ?? 'Asia/Almaty');

if (!is_dir(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0755, true);
}
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
if (!is_dir(UPLOAD_PATH . '/schedules')) {
    mkdir(UPLOAD_PATH . '/schedules', 0755, true);
}
if (!is_dir(UPLOAD_PATH . '/submissions')) {
    mkdir(UPLOAD_PATH . '/submissions', 0755, true);
}
if (!is_dir(UPLOAD_PATH . '/templates')) {
    mkdir(UPLOAD_PATH . '/templates', 0755, true);
}
if (!is_dir(UPLOAD_PATH . '/workloads')) {
    mkdir(UPLOAD_PATH . '/workloads', 0755, true);
}
