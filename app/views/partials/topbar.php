<?php
$user = auth_user();
$initials = '';
if (!empty($user['fullname'])) {
    $parts = preg_split('/\s+/u', trim($user['fullname']), 2);
    $initials = mb_strtoupper(mb_substr($parts[0], 0, 1));
    if (isset($parts[1])) {
        $initials .= mb_strtoupper(mb_substr($parts[1], 0, 1));
    }
}
$initials = $initials ?: '?';
?>
<header class="topbar app-header d-flex align-items-center gap-2">
    <div class="app-header-start d-flex align-items-center gap-2 min-w-0 flex-grow-1">
        <button type="button" class="btn btn-icon btn-back d-none" id="appBackBtn" aria-label="Назад">
            <i class="bi bi-chevron-left"></i>
        </button>
        <div class="app-header-titles min-w-0">
            <span class="app-header-appname d-lg-none">Графики</span>
            <h1 class="page-title text-truncate mb-0"><?= e($title ?? '') ?></h1>
        </div>
    </div>
    <div class="app-header-actions d-flex align-items-center gap-2 flex-shrink-0">
        <span class="topbar-user d-none d-lg-inline"><?= e($user['fullname'] ?? '') ?></span>
        <span class="app-avatar d-lg-none" aria-hidden="true"><?= e($initials) ?></span>
        <a href="<?= base_url('logout') ?>" class="btn btn-icon btn-logout d-lg-none" aria-label="Выход">
            <i class="bi bi-box-arrow-right"></i>
        </a>
        <a href="<?= base_url('logout') ?>" class="btn btn-outline-secondary d-none d-lg-inline-flex">
            <i class="bi bi-box-arrow-right"></i>
            <span>Выход</span>
        </a>
    </div>
</header>
