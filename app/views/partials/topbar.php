<header class="topbar border-bottom bg-white px-4 py-3 d-flex justify-content-between align-items-center">
    <button class="btn btn-outline-primary btn-sm d-lg-none" id="sidebarToggle" type="button">
        <i class="bi bi-list"></i>
    </button>
    <h1 class="h5 mb-0 text-primary"><?= e($title ?? '') ?></h1>
    <div class="d-flex align-items-center gap-3">
        <span class="text-muted small d-none d-md-inline"><?= e(auth_user()['fullname'] ?? '') ?></span>
        <a href="<?= base_url('logout') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-box-arrow-right"></i> Выход
        </a>
    </div>
</header>
