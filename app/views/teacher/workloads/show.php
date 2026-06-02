<div class="card shadow-sm border-0 mb-3">
    <div class="card-body py-2">
        <h5 class="mb-1"><?= e($workload['module_name']) ?></h5>
        <span><?= status_badge($workload['status']) ?></span>
        <span class="text-muted small ms-2"><?= (int)$workload['practice_hours'] ?> ч. · до <?= e($workload['deadline']) ?></span>
    </div>
</div>

<?php if ($readonly): ?>
<div class="alert alert-success">График сдан <?= e($workload['submitted_at'] ?? '') ?>.</div>
<?php endif; ?>

<ul class="nav nav-tabs mb-3" id="editorTabs" role="tablist">
    <?php if ($superdocEnabled): ?>
    <li class="nav-item">
        <button class="nav-link <?= ($defaultTab ?? 'superdoc') === 'superdoc' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-superdoc" type="button">
            <i class="bi bi-window"></i> Редактор на сайте
        </button>
    </li>
    <?php endif; ?>
    <li class="nav-item">
        <button class="nav-link <?= !($superdocEnabled) || ($defaultTab ?? '') === 'external' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-external" type="button">
            <i class="bi bi-laptop"></i> Word на компьютере
        </button>
    </li>
    <?php if ($wordOnline->isEnabled()): ?>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-wordonline" type="button">
            <i class="bi bi-microsoft"></i> Word Online
        </button>
    </li>
    <?php endif; ?>
    <?php if ($ooEnabled): ?>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-onlyoffice" type="button">
            <i class="bi bi-cloud"></i> ONLYOFFICE
        </button>
    </li>
    <?php endif; ?>
</ul>

<div class="tab-content">
    <?php if ($superdocEnabled): ?>
    <div class="tab-pane fade <?= ($defaultTab ?? 'superdoc') === 'superdoc' ? 'show active' : '' ?>" id="tab-superdoc">
        <?php if (!$readonly): ?>
        <div class="alert alert-info py-2 small">
            Редактируйте документ прямо здесь — тот же .docx, как в Word. После правок нажмите «Сохранить» или «Отправить».
        </div>
        <div class="d-flex gap-2 mb-2 flex-wrap align-items-end">
            <div class="flex-grow-1">
                <label class="form-label small mb-0">Комментарий</label>
                <input type="text" id="superdoc-comment" class="form-control form-control-sm" value="<?= e($workload['comment'] ?? '') ?>">
            </div>
            <button type="button" id="superdoc-save-draft" class="btn btn-secondary btn-sm">
                <i class="bi bi-save"></i> Сохранить черновик
            </button>
            <button type="button" id="superdoc-submit" class="btn btn-primary btn-sm">
                <i class="bi bi-send"></i> Отправить
            </button>
        </div>
        <?php endif; ?>
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div id="superdoc-editor" style="height: calc(100vh - 280px); min-height: 480px;"></div>
            </div>
        </div>
        <link rel="stylesheet" href="<?= e(editor_config('superdoc.cdn_css')) ?>">
        <script>
        window.__workloadEditor = {
            docUrl: <?= json_encode($docUrl) ?>,
            uploadUrl: <?= json_encode($uploadUrl) ?>,
            csrf: <?= json_encode(csrf_token()) ?>,
            readonly: <?= $readonly ? 'true' : 'false' ?>,
            superdocEnabled: true,
            cdnJs: <?= json_encode(editor_config('superdoc.cdn_js')) ?>
        };
        </script>
        <script type="module" src="<?= asset('js/superdoc-workload.js') ?>"></script>
    </div>
    <?php endif; ?>

    <div class="tab-pane fade <?= !($superdocEnabled) || ($defaultTab ?? '') === 'external' ? 'show active' : '' ?>" id="tab-external">
        <?php if (!$readonly): ?>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card h-100 border-primary">
                    <div class="card-body text-center">
                        <h6>1. Скачать</h6>
                        <a href="<?= base_url('teacher/workloads/' . $workload['id'] . '/download') ?>" class="btn btn-primary w-100 btn-sm">
                            <i class="bi bi-download"></i> grafik.docx
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h6>2. Word</h6>
                        <p class="small text-muted mb-0">Откройте и заполните в Microsoft Word</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-success">
                    <div class="card-body text-center">
                        <h6>3. Загрузить</h6>
                        <p class="small text-muted mb-0">Загрузите файл ниже</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" action="<?= base_url('teacher/workloads/' . $workload['id'] . '/upload') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <input type="file" name="grafik" class="form-control" accept=".docx" required>
                    </div>
                    <div class="mb-3">
                        <textarea name="comment" class="form-control" rows="2" placeholder="Комментарий"><?= e($workload['comment'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" name="action" value="draft" class="btn btn-secondary btn-sm">Черновик</button>
                    <button type="submit" name="action" value="submit" class="btn btn-primary btn-sm" onclick="return confirm('Отправить?')">Отправить</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <a href="<?= base_url('teacher/workloads/' . $workload['id'] . '/download') ?>" class="btn btn-outline-primary">Скачать документ</a>
        <?php endif; ?>
    </div>

    <?php if ($wordOnline->isEnabled()): ?>
    <div class="tab-pane fade" id="tab-wordonline">
        <?php if ($wordOnlineReady && $wordViewerUrl): ?>
        <div class="alert alert-info py-2 small">
            <strong>Microsoft Word Online</strong> — просмотр документа. Для редактирования через Microsoft нужна настройка WOPI и Microsoft 365 (см. <code>docs/WORD_ONLINE.md</code>).
            <?php if (!$readonly): ?>
            Для заполнения графика используйте вкладку <strong>«Редактор на сайте»</strong>.
            <?php endif; ?>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <iframe src="<?= e($wordViewerUrl) ?>" width="100%" height="700" frameborder="0" style="border:0; min-height:480px;"></iframe>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <h6 class="alert-heading">Word Online на localhost не работает</h6>
            <p class="mb-2">Microsoft открывает файлы только с <strong>публичного HTTPS</strong>-адреса.</p>
            <ul class="mb-2 small">
                <li>Разместите сайт на сервере с SSL, например <code>https://college.edu.kz/ecollege</code></li>
                <li>В <code>app/config/wordonline.php</code> укажите <code>public_base_url</code></li>
            </ul>
            <p class="mb-0 small"><strong>Сейчас на MAMP:</strong> вкладка «Редактор на сайте» (SuperDoc) или «Word на компьютере».</p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($ooEnabled && $editorConfig): ?>
    <div class="tab-pane fade" id="tab-onlyoffice">
        <div class="alert alert-warning small">Требуется ONLYOFFICE Document Server (Docker).</div>
        <div id="onlyoffice-editor" style="height: calc(100vh - 280px); min-height: 480px;"></div>
        <script src="<?= e($documentServerUrl) ?>/web-apps/apps/api/documents/api.js"></script>
        <script>
        document.querySelector('[data-bs-target="#tab-onlyoffice"]')?.addEventListener('shown.bs.tab', function () {
            if (typeof DocsAPI !== 'undefined' && !window.__ooLoaded) {
                window.__ooLoaded = true;
                new DocsAPI.DocEditor('onlyoffice-editor', <?= json_encode($editorConfig, JSON_UNESCAPED_UNICODE) ?>);
            }
        });
        </script>
    </div>
    <?php endif; ?>
</div>

<a href="<?= base_url('teacher') ?>" class="btn btn-link mt-3">← К списку</a>
