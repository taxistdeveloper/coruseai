<div class="app-card mb-4">
    <div class="card-body padded d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <span class="me-2"><?= status_badge($schedule['status']) ?></span>
            <span class="text-muted">Прогресс: <?= (int)$schedule['progress_percent'] ?>%</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('teacher') ?>" class="btn btn-outline-secondary">Назад</a>
            <a href="<?= base_url('teacher/schedules/' . $schedule['id'] . '/download') ?>" class="btn btn-outline-primary">
                <i class="bi bi-download" aria-hidden="true"></i> Excel
            </a>
        </div>
    </div>
</div>

<form method="post" id="scheduleForm">
    <?= csrf_field() ?>
    <div class="app-card mb-4">
        <div class="card-body padded">
            <label class="form-label" for="schedule-comment">Комментарий к отправке</label>
            <textarea name="comment" id="schedule-comment" class="form-control" rows="2"></textarea>
        </div>
    </div>
    <?php
    $readonly = $readonly ?? false;
    require APP_PATH . '/views/partials/schedule_grid.php';
    ?>
    <?php if (!$readonly): ?>
    <div class="form-actions-bar sticky-actions">
        <button type="submit" formaction="<?= base_url('teacher/schedules/' . $schedule['id'] . '/save') ?>" formmethod="post" class="btn btn-secondary">
            <i class="bi bi-save" aria-hidden="true"></i> Сохранить черновик
        </button>
        <button type="submit" formaction="<?= base_url('teacher/schedules/' . $schedule['id'] . '/submit') ?>" class="btn btn-primary" onclick="return confirm('Отправить график администратору? После отправки редактирование будет недоступно.')">
            <i class="bi bi-send" aria-hidden="true"></i> Отправить
        </button>
    </div>
    <?php endif; ?>
</form>
