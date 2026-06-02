<nav id="sidebar" class="sidebar text-white">
    <div class="sidebar-brand px-3 py-4">
        <i class="bi bi-file-earmark-word me-2"></i>
        <span>Графики</span>
    </div>
    <ul class="nav flex-column px-2">
        <?php if (is_admin()): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('admin') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('admin/teachers') ?>"><i class="bi bi-people me-2"></i>Преподаватели</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('admin/workloads') ?>"><i class="bi bi-journal-bookmark me-2"></i>Нагрузки</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('admin/template') ?>"><i class="bi bi-layout-text-window me-2"></i>Шаблон графика</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('admin/submissions') ?>"><i class="bi bi-check2-square me-2"></i>Сданные графики</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('admin/audit') ?>"><i class="bi bi-journal-text me-2"></i>Аудит</a>
        </li>
        <?php else: ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('teacher') ?>"><i class="bi bi-list-task me-2"></i>Моя нагрузка</a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
