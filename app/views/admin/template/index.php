<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white">Текущий шаблон</div>
            <div class="card-body">
                <?php if ($template && !empty($template['file_path'])): ?>
                <p><i class="bi bi-file-earmark-word text-primary fs-2"></i></p>
                <p><strong><?= e($template['title']) ?></strong></p>
                <p class="text-muted"><?= e($template['original_filename']) ?></p>
                <p class="small text-muted">Загружен: <?= e($template['created_at']) ?></p>
                <span class="badge bg-success">Активен</span>
                <p class="small mt-3 mb-0">Каждому преподавателю создаётся <strong>копия</strong> этого файла: скачивает → заполняет в Word → загружает на сайт.</p>
                <?php else: ?>
                <p class="text-warning mb-0">Загрузите grafik.docx — без шаблона преподаватели не смогут заполнить график.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">Загрузить шаблон grafik.docx</div>
            <div class="card-body">
                <p class="text-muted small">Нужен формат <strong>.docx</strong> (Word 2007+). Преподаватели заполняют его в Word на компьютере.</p>
                <form method="post" enctype="multipart/form-data" action="<?= base_url('admin/template') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Название</label>
                        <input type="text" name="title" class="form-control" value="Шаблон графика">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Файл grafik.docx *</label>
                        <input type="file" name="template" class="form-control" accept=".docx" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Загрузить</button>
                </form>
            </div>
        </div>
    </div>
</div>
