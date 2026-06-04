<div class="info-banner alert alert-info mb-4" role="status">
    <i class="bi bi-info-circle" aria-hidden="true"></i>
    <span>Заполните график до лимита часов. Считаются только будни; суббота, воскресенье и праздники РК — нет.</span>
</div>

<div class="row g-4">
    <?php if (empty($workloads)): ?>
    <div class="col-12">
        <div class="alert alert-secondary mb-0">Нагрузка не назначена.</div>
    </div>
    <?php endif; ?>
    <?php foreach ($workloads as $w): ?>
    <div class="col-md-6 col-xl-4">
        <div class="card workload-card h-100">
            <div class="card-body d-flex flex-column">
                <h3 class="card-title"><?= e($w['module_name']) ?></h3>
                <?php if (!empty($w['study_group'])): ?>
                <p class="meta-line mb-1">Группа: <?= e($w['study_group']) ?></p>
                <?php endif; ?>
                <p class="mb-2"><?= status_badge($w['status']) ?></p>
                <p class="meta-line"><?= (int)$w['practice_hours'] ?> часов · <?= (int)$w['progress_percent'] ?>%</p>
                <p class="meta-line mb-4">Срок: <?= e($w['deadline']) ?></p>
                <div class="mt-auto">
                    <a href="<?= base_url('teacher/workloads/' . $w['id']) ?>" class="btn btn-primary w-100">
                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                        <?= $w['status'] === 'submitted' ? 'Просмотр' : 'Заполнить график' ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
