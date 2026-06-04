<div class="row g-4">
    <div class="col-lg-4">
        <div class="app-card mb-0">
            <div class="card-body padded">
                <p><strong>Преподаватель:</strong> <?= e($schedule['teacher_name'] ?? 'Не назначен') ?></p>
                <p><strong>Статус:</strong> <?= status_badge($schedule['status']) ?></p>
                <p><strong>Прогресс:</strong> <?= (int)$schedule['progress_percent'] ?>%</p>
                <p><strong>Срок:</strong> <?= e($schedule['deadline'] ?? '—') ?></p>
                <?php if (empty($schedule['teacher_id'])): ?>
                <a href="<?= base_url('admin/schedules/' . $schedule['id'] . '/assign') ?>" class="btn btn-primary w-100 mb-2">Назначить</a>
                <?php endif; ?>
                <form method="post" action="<?= base_url('admin/schedules/' . $schedule['id'] . '/delete') ?>" onsubmit="return confirm('Удалить график?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger w-100">Удалить</button>
                </form>
            </div>
        </div>
        <div class="app-card mt-4">
            <div class="app-card-header">Версии</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($versions as $v): ?>
                <li class="list-group-item">
                    v<?= (int)$v['version'] ?> — <?= e($v['action']) ?>
                    <span class="text-muted d-block"><?= e($v['created_at']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-8">
        <?php require APP_PATH . '/views/partials/schedule_grid.php'; ?>
    </div>
</div>
