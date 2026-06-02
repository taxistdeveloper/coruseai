<div class="mb-3 d-flex justify-content-between">
    <form method="get" class="d-flex gap-2">
        <input type="search" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Поиск...">
        <button class="btn btn-primary">Найти</button>
    </form>
    <a href="<?= base_url('admin/schedules/upload') ?>" class="btn btn-primary">Загрузить</a>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <table class="table datatable">
            <thead><tr><th>Название</th><th>Преподаватель</th><th>Статус</th><th>Срок</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['title']) ?></td>
                <td><?= e($r['teacher_name'] ?? '—') ?></td>
                <td><?= status_badge($r['status']) ?></td>
                <td><?= e($r['deadline'] ?? '—') ?></td>
                <td><a href="<?= base_url('admin/schedules/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary">Открыть</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
