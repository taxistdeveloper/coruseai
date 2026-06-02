<?php if (!$template || empty($template['file_path'])): ?>
<div class="alert alert-warning">
    Администратор не загрузил шаблон grafik.docx.
</div>
<?php else: ?>
<div class="alert alert-info py-2">
    <i class="bi bi-window"></i> Откройте нагрузку — график можно заполнить <strong>внутри сайта</strong> (редактор Word) или скачать в Word на компьютер.
</div>
<?php endif; ?>

<div class="row g-3">
    <?php if (empty($workloads)): ?>
    <div class="col-12"><div class="alert alert-secondary">Нагрузка не назначена.</div></div>
    <?php endif; ?>
    <?php foreach ($workloads as $w): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?= e($w['module_name']) ?></h5>
                <p class="mb-1"><?= status_badge($w['status']) ?></p>
                <p class="text-muted small mb-1"><?= (int)$w['practice_hours'] ?> часов</p>
                <p class="text-muted small mb-3">Срок: <?= e($w['deadline']) ?></p>
                <div class="mt-auto">
                    <a href="<?= base_url('teacher/workloads/' . $w['id']) ?>" class="btn btn-primary w-100">
                        <i class="bi bi-file-earmark-word"></i>
                        <?= $w['status'] === 'submitted' ? 'Просмотр' : 'Заполнить график' ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
