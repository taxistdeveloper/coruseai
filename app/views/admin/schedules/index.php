<div class="page-toolbar">
    <form method="get" class="toolbar-search">
        <input type="search" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Поиск по названию...">
        <button type="submit" class="btn btn-primary">Найти</button>
    </form>
    <a href="<?= base_url('admin/schedules/upload') ?>" class="btn btn-primary">
        <i class="bi bi-upload"></i> Загрузить
    </a>
</div>

<div class="app-card">
    <div class="table-responsive">
        <table class="table table-hover datatable mb-0">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Преподаватель</th>
                    <th>Статус</th>
                    <th>Срок</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['title']) ?></td>
                <td><?= e($r['teacher_name'] ?? '—') ?></td>
                <td><?= status_badge($r['status']) ?></td>
                <td><?= e($r['deadline'] ?? '—') ?></td>
                <td>
                    <a href="<?= base_url('admin/schedules/' . $r['id']) ?>" class="btn btn-outline-primary">Открыть</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
