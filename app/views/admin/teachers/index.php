<div class="page-toolbar">
    <form method="get" class="toolbar-search">
        <input type="search" name="q" class="form-control" value="<?= e($search) ?>" placeholder="ФИО, логин, кафедра...">
        <button type="submit" class="btn btn-primary">Найти</button>
    </form>
    <a href="<?= base_url('admin/teachers/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Добавить
    </a>
</div>

<div class="app-card">
    <div class="card-body padded">
        <div class="table-responsive">
            <table class="table table-hover datatable mb-0">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>Логин</th>
                        <th>Кафедра</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $t): ?>
                    <tr>
                        <td><?= e($t['fullname']) ?></td>
                        <td><?= e($t['login']) ?></td>
                        <td><?= e($t['department'] ?? '—') ?></td>
                        <td><?= !empty($t['is_active']) ? '<span class="badge bg-success">Активен</span>' : '<span class="badge bg-secondary">Отключён</span>' ?></td>
                        <td class="d-flex flex-wrap gap-2">
                            <a href="<?= base_url('admin/teachers/' . $t['teacher_id'] . '/edit') ?>" class="btn btn-outline-primary">Изменить</a>
                            <form method="post" action="<?= base_url('admin/teachers/' . $t['teacher_id'] . '/delete') ?>" onsubmit="return confirm('Удалить преподавателя?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php require APP_PATH . '/views/partials/pagination.php'; ?>
    </div>
</div>
