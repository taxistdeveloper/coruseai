<div class="row mb-3">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <p><strong>Преподаватель:</strong> <?= e($workload['teacher_name']) ?></p>
                <p><strong>Модуль:</strong> <?= e($workload['module_name']) ?></p>
                <p><strong>Часов:</strong> <?= (int)$workload['practice_hours'] ?></p>
                <p><strong>Срок:</strong> <?= e($workload['deadline']) ?></p>
                <p><strong>Статус:</strong> <?= status_badge($workload['status']) ?></p>
                <?php if (!empty($workload['submitted_at'])): ?>
                <p><strong>Сдано:</strong> <?= e($workload['submitted_at']) ?></p>
                <?php endif; ?>
                <a href="<?= base_url('admin/workloads/' . $workload['id'] . '/doc') ?>" class="btn btn-success btn-sm w-100 mb-2">
                    <i class="bi bi-download"></i> Скачать .docx
                </a>
                <a href="<?= base_url('admin/workloads/' . $workload['id'] . '/edit') ?>" class="btn btn-outline-primary btn-sm w-100">Изменить нагрузку</a>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <ul class="nav nav-tabs mb-2">
            <?php if ($superdocEnabled): ?>
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#admin-tab-view" type="button">Просмотр на сайте</button>
            </li>
            <?php endif; ?>
            <?php if ($ooEnabled): ?>
            <li class="nav-item">
                <button class="nav-link <?= !$superdocEnabled ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#admin-tab-oo" type="button">ONLYOFFICE</button>
            </li>
            <?php endif; ?>
        </ul>
        <div class="tab-content">
            <?php if ($superdocEnabled): ?>
            <div class="tab-pane fade show active" id="admin-tab-view">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div id="superdoc-editor" style="height: 600px;"></div>
                    </div>
                </div>
                <link rel="stylesheet" href="<?= e(editor_config('superdoc.cdn_css')) ?>">
                <script>
                window.__workloadEditor = {
                    docUrl: <?= json_encode($docUrl) ?>,
                    readonly: true,
                    superdocEnabled: true,
                    cdnJs: <?= json_encode(editor_config('superdoc.cdn_js')) ?>
                };
                </script>
                <script type="module" src="<?= asset('js/superdoc-workload.js') ?>"></script>
            </div>
            <?php endif; ?>
            <?php if ($ooEnabled && $editorConfig): ?>
            <div class="tab-pane fade <?= !$superdocEnabled ? 'show active' : '' ?>" id="admin-tab-oo">
                <div id="onlyoffice-editor" style="height: 600px;"></div>
                <script src="<?= e($documentServerUrl) ?>/web-apps/apps/api/documents/api.js"></script>
                <script>
                document.querySelector('[data-bs-target="#admin-tab-oo"]')?.addEventListener('shown.bs.tab', function () {
                    if (typeof DocsAPI !== 'undefined' && !window.__ooAdminLoaded) {
                        window.__ooAdminLoaded = true;
                        new DocsAPI.DocEditor('onlyoffice-editor', <?= json_encode($editorConfig, JSON_UNESCAPED_UNICODE) ?>);
                    }
                });
                if (document.querySelector('#admin-tab-oo.show')) {
                    document.querySelector('[data-bs-target="#admin-tab-oo"]')?.click();
                }
                </script>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
