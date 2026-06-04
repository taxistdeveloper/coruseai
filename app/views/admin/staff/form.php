<?php $isEdit = !empty($member); ?>
<div class="row">
    <div class="col-lg-8">
        <div class="app-card mb-4">
            <div class="app-card-header">Сотрудник учебного процесса</div>
            <div class="card-body padded">
                <p class="text-muted small">Доступ: назначение нагрузок и просмотр отчётов.</p>
                <form method="post" action="<?= $isEdit ? base_url('admin/staff/' . $member['id']) : base_url('admin/staff') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">ФИО *</label>
                        <input type="text" name="fullname" class="form-control" required value="<?= e($member['fullname'] ?? old('fullname')) ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Логин *</label>
                            <input type="text" name="login" class="form-control" required value="<?= e($member['login'] ?? old('login')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Пароль <?= $isEdit ? '(пусто = не менять)' : '*' ?></label>
                            <input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>>
                        </div>
                    </div>
                    <?php if ($isEdit): ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= !empty($member['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Активен</label>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                        <a href="<?= base_url('admin/staff') ?>" class="btn btn-outline-secondary">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
