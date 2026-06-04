<?php require APP_PATH . '/views/partials/nav_helpers.php'; ?>
<nav id="sidebar" class="sidebar d-none d-lg-flex" aria-label="Главное меню">
    <div class="sidebar-brand">
        <i class="bi bi-calendar3-week" aria-hidden="true"></i>
        <span>Графики</span>
    </div>
    <ul class="nav flex-column">
        <?php if (is_admin()): ?>
        <li class="nav-item">
            <a class="nav-link<?= $navIsAdminHomeExclusive() ? ' active' : '' ?>" href="<?= base_url('admin') ?>">
                <i class="bi bi-speedometer2" aria-hidden="true"></i>Обзор
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $navIsActive('/admin/teachers') ? ' active' : '' ?>" href="<?= base_url('admin/teachers') ?>">
                <i class="bi bi-people" aria-hidden="true"></i>Преподаватели
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $navIsActive('/admin/staff') ? ' active' : '' ?>" href="<?= base_url('admin/staff') ?>">
                <i class="bi bi-person-badge" aria-hidden="true"></i>Учебный процесс
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $navIsActive('/admin/workloads') ? ' active' : '' ?>" href="<?= base_url('admin/workloads') ?>">
                <i class="bi bi-journal-bookmark" aria-hidden="true"></i>Нагрузки
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $navIsActive('/admin/submissions') ? ' active' : '' ?>" href="<?= base_url('admin/submissions') ?>">
                <i class="bi bi-check2-square" aria-hidden="true"></i>Сданные графики
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $navIsActive('/admin/practice-report') ? ' active' : '' ?>" href="<?= base_url('admin/practice-report') ?>">
                <i class="bi bi-calendar-event" aria-hidden="true"></i>Отчеты по практике
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $navIsActive('/admin/audit') ? ' active' : '' ?>" href="<?= base_url('admin/audit') ?>">
                <i class="bi bi-journal-text" aria-hidden="true"></i>Аудит
            </a>
        </li>
        <?php elseif (is_academic()): ?>
        <li class="nav-item">
            <a class="nav-link<?= $navIsActive('/admin/workloads') ? ' active' : '' ?>" href="<?= base_url('admin/workloads') ?>">
                <i class="bi bi-journal-bookmark" aria-hidden="true"></i>Нагрузки
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $navIsActive('/admin/submissions') ? ' active' : '' ?>" href="<?= base_url('admin/submissions') ?>">
                <i class="bi bi-check2-square" aria-hidden="true"></i>Сданные графики
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $navIsActive('/admin/practice-report') ? ' active' : '' ?>" href="<?= base_url('admin/practice-report') ?>">
                <i class="bi bi-calendar-event" aria-hidden="true"></i>Отчеты по практике
            </a>
        </li>
        <?php else: ?>
        <li class="nav-item">
            <a class="nav-link<?= $navIsActive('/teacher') ? ' active' : '' ?>" href="<?= base_url('teacher') ?>">
                <i class="bi bi-list-task" aria-hidden="true"></i>Моя нагрузка
            </a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
