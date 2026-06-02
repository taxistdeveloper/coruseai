<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

use App\Core\Router;

$router = new Router();
require ROOT_PATH . '/routes/web.php';

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', request_path());
