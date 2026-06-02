<div class="card shadow-sm border-0 mb-3">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <span class="me-2"><?= status_badge($schedule['status']) ?></span>
            <span class="text-muted">Прогресс: <?= (int)$schedule['progress_percent'] ?>%</span>
        </div>
        <div>
            <a href="<?= base_url('teacher') ?>" class="btn btn-link">Назад</a>
            <a href="<?= base_url('teacher/schedules/' . $schedule['id'] . '/download') ?>" class="btn btn-outline-secondary btn-sm">Excel</a>
        </div>
    </div>
</div>

<form method="post" id="scheduleForm">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Комментарий к отправке</label>
        <textarea name="comment" class="form-control" rows="2"></textarea>
    </div>
    <?php
    $readonly = $readonly ?? false;
    require APP_PATH . '/views/partials/schedule_grid.php';
    ?>
    <?php if (!$readonly): ?>
    <div class="sticky-actions bg-white border-top py-3 mt-3 d-flex gap-2">
        <button type="submit" formaction="<?= base_url('teacher/schedules/' . $schedule['id'] . '/save') ?>" formmethod="post" class="btn btn-secondary">
            <i class="bi bi-save"></i> Сохранить черновик
        </button>
        <button type="submit" formaction="<?= base_url('teacher/schedules/' . $schedule['id'] . '/submit') ?>" class="btn btn-primary" onclick="return confirm('Отправить график администратору? После отправки редактирование будет недоступно.')">
            <i class="bi bi-send"></i> Отправить
        </button>
    </div>
    <?php endif; ?>
</form>
