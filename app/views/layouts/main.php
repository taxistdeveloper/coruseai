<?php
$role = auth_user()['role'] ?? 'guest';
$isAdmin = $role === 'admin';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? config('name')) ?> — <?= e(config('name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>
<?php if (auth_user()): ?>
<div class="d-flex" id="wrapper">
    <?php require APP_PATH . '/views/partials/sidebar.php'; ?>
    <div id="page-content-wrapper" class="w-100">
        <?php require APP_PATH . '/views/partials/topbar.php'; ?>
        <main class="container-fluid py-4 px-4">
            <?php require APP_PATH . '/views/partials/flash.php'; ?>
            <?php require APP_PATH . '/views/partials/content.php'; ?>
        </main>
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
