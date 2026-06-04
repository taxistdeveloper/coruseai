<?php $isEdit = !empty($teacher); ?>
<div class="row">
    <div class="col-lg-8">
        <div class="app-card mb-4">
            <div class="app-card-header">Данные преподавателя</div>
            <div class="card-body padded">
                <form method="post" action="<?= $isEdit ? base_url('admin/teachers/' . $teacher['id']) : base_url('admin/teachers') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">ФИО *</label>
                        <input type="text" name="fullname" class="form-control" required value="<?= e($teacher['fullname'] ?? old('fullname')) ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Логин *</label>
                            <input type="text" name="login" class="form-control" required value="<?= e($teacher['login'] ?? old('login')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Пароль <?= $isEdit ? '(пусто = не менять)' : '*' ?></label>
                            <input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Кафедра</label>
                        <input type="text" name="department" class="form-control" value="<?= e($teacher['department'] ?? old('department')) ?>">
                    </div>
                    <?php if ($isEdit): ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= !empty($teacher['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Активен</label>
                    </div>
                    <?php else: ?>
                    <hr>
                    <h6 class="text-primary">Нагрузка практики</h6>
                    <div class="mb-3">
                        <label class="form-label">Название модуля</label>
                        <input type="text" name="module_name" class="form-control" placeholder="Педагогическая практика" value="<?= old('module_name') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Группа</label>
                        <input type="text" name="study_group" class="form-control" placeholder="ИС-21" value="<?= old('study_group') ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Часов (нагрузка)</label>
                            <input type="number" name="practice_hours" class="form-control" min="0" value="<?= old('practice_hours', '0') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Срок сдачи</label>
                            <input type="date" name="deadline" class="form-control" value="<?= old('deadline') ?>">
                        </div>
                    </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="<?= base_url('admin/teachers') ?>" class="btn btn-outline-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
    <?php if ($isEdit && !empty($workloads)): ?>
    <div class="col-lg-4">
        <div class="app-card">
            <div class="app-card-header">Нагрузки</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($workloads as $w): ?>
                <li class="list-group-item">
                    <strong><?= e($w['module_name']) ?></strong>
                    <?php if (!empty($w['study_group'])): ?>
                    <span class="text-muted"> · <?= e($w['study_group']) ?></span>
                    <?php endif; ?>
                    <br>
                    <span class="text-muted"><?= (int)$w['practice_hours'] ?> ч. · до <?= e($w['deadline']) ?></span><br>
                    <?= status_badge($w['status']) ?>
                    <a href="<?= base_url('admin/workloads/' . $w['id']) ?>" class="btn btn-outline-primary mt-2">Открыть</a>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="card-body padded">
                <a href="<?= base_url('admin/workloads/create?teacher_id=' . $teacher['id']) ?>" class="btn btn-outline-primary w-100">+ Нагрузка</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
