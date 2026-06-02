<div class="row">
    <div class="col-lg-4 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <p><strong>Преподаватель:</strong> <?= e($schedule['teacher_name'] ?? 'Не назначен') ?></p>
                <p><strong>Статус:</strong> <?= status_badge($schedule['status']) ?></p>
                <p><strong>Прогресс:</strong> <?= (int)$schedule['progress_percent'] ?>%</p>
                <p><strong>Срок:</strong> <?= e($schedule['deadline'] ?? '—') ?></p>
                <?php if (empty($schedule['teacher_id'])): ?>
                <a href="<?= base_url('admin/schedules/' . $schedule['id'] . '/assign') ?>" class="btn btn-primary btn-sm">Назначить</a>
                <?php endif; ?>
                <form method="post" class="mt-2" action="<?= base_url('admin/schedules/' . $schedule['id'] . '/delete') ?>" onsubmit="return confirm('Удалить?')">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-danger btn-sm">Удалить</button>
                </form>
            </div>
        </div>
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-header">Версии</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($versions as $v): ?>
                <li class="list-group-item small">
                    v<?= (int)$v['version'] ?> — <?= e($v['action']) ?>
                    <span class="text-muted"><?= e($v['created_at']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-8">
        <?php require APP_PATH . '/views/partials/schedule_grid.php'; ?>
    </div>
</div>
