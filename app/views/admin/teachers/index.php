<div class="d-flex justify-content-between mb-3">
    <form class="d-flex gap-2" method="get">
        <input type="search" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Поиск...">
        <button class="btn btn-primary">Найти</button>
    </form>
    <a href="<?= base_url('admin/teachers/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Добавить</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <table class="table table-hover datatable">
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
                    <td>
                        <a href="<?= base_url('admin/teachers/' . $t['teacher_id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Изменить</a>
                        <form method="post" action="<?= base_url('admin/teachers/' . $t['teacher_id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Удалить?')">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger">Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php require APP_PATH . '/views/partials/pagination.php'; ?>
    </div>
</div>
