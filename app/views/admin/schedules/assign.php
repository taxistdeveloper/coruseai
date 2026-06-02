<div class="card shadow-sm border-0 col-lg-6">
    <div class="card-body">
        <p class="text-muted">График: <strong><?= e($schedule['title']) ?></strong></p>
        <form method="post" action="<?= base_url('admin/schedules/' . $schedule['id'] . '/assign') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Преподаватель *</label>
                <select name="teacher_id" class="form-select" required>
                    <option value="">Выберите...</option>
                    <?php foreach ($teachers as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"><?= e($t['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Назначить</button>
            <a href="<?= base_url('admin') ?>" class="btn btn-link">Назад</a>
        </form>
    </div>
</div>
