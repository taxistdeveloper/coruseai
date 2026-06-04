<?php require APP_PATH . '/views/partials/nav_helpers.php'; ?>
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
<nav class="app-bottom-nav d-lg-none" aria-label="Нижняя навигация">
    <?php if (is_admin()): ?>
    <a href="<?= base_url('admin') ?>" class="app-tab<?= $navIsAdminHomeExclusive() ? ' active' : '' ?>">
        <i class="bi bi-house-door" aria-hidden="true"></i>
        <span>Главная</span>
    </a>
    <a href="<?= base_url('admin/teachers') ?>" class="app-tab<?= $navIsActive('/admin/teachers') ? ' active' : '' ?>">
        <i class="bi bi-people" aria-hidden="true"></i>
        <span>Кадры</span>
    </a>
    <a href="<?= base_url('admin/workloads') ?>" class="app-tab<?= $navIsActive('/admin/workloads') ? ' active' : '' ?>">
        <i class="bi bi-journal-bookmark" aria-hidden="true"></i>
        <span>Нагрузки</span>
    </a>
    <a href="<?= base_url('admin/submissions') ?>" class="app-tab<?= $navIsActive('/admin/submissions') ? ' active' : '' ?>">
        <i class="bi bi-check2-circle" aria-hidden="true"></i>
        <span>Сдачи</span>
    </a>
    <button type="button" class="app-tab<?= $navIsMoreSection() ? ' active' : '' ?>" data-bs-toggle="offcanvas" data-bs-target="#appMoreMenu" aria-controls="appMoreMenu">
        <i class="bi bi-grid" aria-hidden="true"></i>
        <span>Ещё</span>
    </button>
    <?php elseif (is_academic()): ?>
    <a href="<?= base_url('admin/workloads') ?>" class="app-tab<?= $navIsActive('/admin/workloads') ? ' active' : '' ?>">
        <i class="bi bi-journal-bookmark" aria-hidden="true"></i>
        <span>Нагрузки</span>
    </a>
    <a href="<?= base_url('admin/submissions') ?>" class="app-tab<?= $navIsActive('/admin/submissions') ? ' active' : '' ?>">
        <i class="bi bi-check2-circle" aria-hidden="true"></i>
        <span>Сдачи</span>
    </a>
    <a href="<?= base_url('admin/practice-report') ?>" class="app-tab app-tab--wide<?= $navIsActive('/admin/practice-report') ? ' active' : '' ?>">
        <i class="bi bi-calendar-event" aria-hidden="true"></i>
        <span>Отчёты</span>
    </a>
    <?php else: ?>
    <a href="<?= base_url('teacher') ?>" class="app-tab app-tab--wide<?= $navIsActive('/teacher') ? ' active' : '' ?>">
        <i class="bi bi-calendar3-week" aria-hidden="true"></i>
        <span>Мои графики</span>
    </a>
    <?php endif; ?>
</nav>

<?php if (is_admin()): ?>
<div class="offcanvas offcanvas-bottom app-more-sheet d-lg-none" tabindex="-1" id="appMoreMenu" aria-labelledby="appMoreMenuLabel">
    <div class="offcanvas-header border-0 pb-0">
        <div>
            <h2 class="offcanvas-title h5 mb-1" id="appMoreMenuLabel">Меню</h2>
            <p class="text-muted mb-0 small"><?= e($user['fullname'] ?? '') ?></p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
    </div>
    <div class="offcanvas-body pt-2">
        <div class="app-sheet-grid">
            <a href="<?= base_url('admin/staff') ?>" class="app-sheet-tile">
                <i class="bi bi-person-badge"></i>
                <span>Уч. процесс</span>
            </a>
            <a href="<?= base_url('admin/practice-report') ?>" class="app-sheet-tile">
                <i class="bi bi-calendar-event"></i>
                <span>Практики</span>
            </a>
            <a href="<?= base_url('admin/audit') ?>" class="app-sheet-tile">
                <i class="bi bi-journal-text"></i>
                <span>Аудит</span>
            </a>
            <a href="<?= base_url('admin/workloads/create') ?>" class="app-sheet-tile app-sheet-tile--accent">
                <i class="bi bi-plus-lg"></i>
                <span>Нагрузка</span>
            </a>
        </div>
        <a href="<?= base_url('logout') ?>" class="app-sheet-logout">
            <i class="bi bi-box-arrow-right"></i> Выйти из аккаунта
        </a>
    </div>
</div>
<?php endif; ?>
