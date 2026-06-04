<div class="page-toolbar">
    <form method="get" class="toolbar-search">
        <input type="search" name="q" class="form-control" value="<?= e($search) ?>" placeholder="ФИО, логин...">
        <button type="submit" class="btn btn-primary">Найти</button>
    </form>
    <a href="<?= base_url('admin/staff/create') ?>" class="btn btn-primary">
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
                        <th>Роль</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staff as $s): ?>
                    <tr>
                        <td><?= e($s['fullname']) ?></td>
                        <td><?= e($s['login']) ?></td>
                        <td><?= e(role_label('academic')) ?></td>
                        <td><?= !empty($s['is_active']) ? '<span class="badge bg-success">Активен</span>' : '<span class="badge bg-secondary">Отключён</span>' ?></td>
                        <td class="d-flex flex-wrap gap-2">
                            <a href="<?= base_url('admin/staff/' . $s['id'] . '/edit') ?>" class="btn btn-outline-primary">Изменить</a>
                            <form method="post" action="<?= base_url('admin/staff/' . $s['id'] . '/delete') ?>" onsubmit="return confirm('Удалить сотрудника?')">
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
