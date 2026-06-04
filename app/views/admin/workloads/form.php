<?php $isEdit = !empty($workload); ?>
<div class="app-card col-lg-8">
    <div class="app-card-header"><?= $isEdit ? 'Редактирование нагрузки' : 'Новая нагрузка' ?></div>
    <div class="card-body padded">
        <form method="post" action="<?= $isEdit ? base_url('admin/workloads/' . $workload['id']) : base_url('admin/workloads') ?>">
            <?= csrf_field() ?>
            <?php if (!$isEdit): ?>
            <div class="mb-3">
                <label class="form-label">Преподаватель *</label>
                <select name="teacher_id" class="form-select" required>
                    <option value="">Выберите...</option>
                    <?php foreach ($teachers as $t): ?>
                    <option value="<?= (int)$t['id'] ?>" <?= (int)($_GET['teacher_id'] ?? 0) === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Название модуля *</label>
                <input type="text" name="module_name" class="form-control" required value="<?= e($workload['module_name'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Группа</label>
                <input type="text" name="study_group" class="form-control" placeholder="ИС-21" value="<?= e($workload['study_group'] ?? '') ?>">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Нагрузка практики (часов) *</label>
                    <input type="number" name="practice_hours" class="form-control" min="1" required value="<?= (int)($workload['practice_hours'] ?? 0) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Срок сдачи *</label>
                    <input type="date" name="deadline" class="form-control" required value="<?= e($workload['deadline'] ?? '') ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="<?= base_url('admin/workloads') ?>" class="btn btn-outline-secondary">Отмена</a>
        </form>
    </div>
</div>
