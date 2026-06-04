<div class="app-card col-lg-6">
    <div class="app-card-header">Назначение преподавателя</div>
    <div class="card-body padded">
        <p class="text-muted mb-4">График: <strong><?= e($schedule['title']) ?></strong></p>
        <form method="post" action="<?= base_url('admin/schedules/' . $schedule['id'] . '/assign') ?>">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label class="form-label" for="assign-teacher">Преподаватель *</label>
                <select name="teacher_id" id="assign-teacher" class="form-select" required>
                    <option value="">Выберите...</option>
                    <?php foreach ($teachers as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"><?= e($t['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">Назначить</button>
                <a href="<?= base_url('admin/schedules/' . $schedule['id']) ?>" class="btn btn-outline-secondary">Назад</a>
            </div>
        </form>
    </div>
</div>
