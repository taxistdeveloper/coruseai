<div class="app-card col-lg-8">
    <div class="app-card-header">Загрузка графика Excel</div>
    <div class="card-body padded">
        <?php if (!$excelOk): ?>
        <div class="alert alert-warning">
            Для полного импорта Excel выполните <code>composer install</code> в корне проекта.
            Базовый импорт xlsx/xlsm работает без библиотеки.
        </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" action="<?= base_url('admin/schedules/upload') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="sched-title">Название графика</label>
                <input type="text" name="title" id="sched-title" class="form-control" value="График <?= date('Y') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="sched-file">Файл grafik.xlsm / xlsx *</label>
                <input type="file" name="grafik" id="sched-file" class="form-control" accept=".xlsm,.xlsx,.xls" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="sched-deadline">Срок сдачи</label>
                <input type="date" name="deadline" id="sched-deadline" class="form-control">
            </div>
            <div class="mb-4">
                <label class="form-label" for="sched-teacher">Назначить преподавателю (необязательно)</label>
                <select name="teacher_id" id="sched-teacher" class="form-select">
                    <option value="">— Не назначать —</option>
                    <?php foreach ($teachers as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"><?= e($t['fullname']) ?> — <?= e($t['department'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-upload" aria-hidden="true"></i> Загрузить
            </button>
        </form>
    </div>
</div>
