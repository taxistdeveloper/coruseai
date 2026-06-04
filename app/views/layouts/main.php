<?php
$role = auth_user()['role'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title><?= e($title ?? config('name')) ?> — <?= e(config('name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="<?= auth_user() ? 'app-shell' : 'app-guest' ?>"
      data-home="<?= e(base_url(ltrim(auth_home_path(), '/'))) ?>">
<?php if (auth_user()): ?>
<div class="d-flex app-layout" id="wrapper">
    <div class="sidebar-backdrop d-lg-none" id="sidebarBackdrop" aria-hidden="true"></div>
    <?php require APP_PATH . '/views/partials/sidebar.php'; ?>
    <div id="page-content-wrapper" class="w-100 app-screen">
        <?php require APP_PATH . '/views/partials/topbar.php'; ?>
        <main class="container-fluid app-main">
            <?php require APP_PATH . '/views/partials/flash.php'; ?>
            <?php require APP_PATH . '/views/partials/content.php'; ?>
        </main>
        <?php require APP_PATH . '/views/partials/mobile_nav.php'; ?>
    </div>
</div>
<?php else: ?>
<main class="auth-wrapper">
    <?php require APP_PATH . '/views/partials/flash.php'; ?>
    <?php require APP_PATH . '/views/partials/content.php'; ?>
</main>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
