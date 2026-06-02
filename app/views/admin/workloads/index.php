<div class="d-flex justify-content-between mb-3">
    <form method="get" class="d-flex gap-2">
        <input type="search" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Поиск...">
        <button class="btn btn-primary">Найти</button>
    </form>
    <a href="<?= base_url('admin/workloads/create') ?>" class="btn btn-primary">+ Назначить нагрузку</a>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <table class="table datatable">
            <thead>
                <tr>
                    <th>Преподаватель</th>
                    <th>Модуль</th>
                    <th>Часов</th>
                    <th>Срок</th>
                    <th>Статус</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['teacher_name']) ?></td>
                    <td><?= e($r['module_name']) ?></td>
                    <td><?= (int)$r['practice_hours'] ?></td>
                    <td><?= e($r['deadline']) ?></td>
                    <td><?= status_badge($r['status']) ?></td>
                    <td>
                        <a href="<?= base_url('admin/workloads/' . $r['workload_id']) ?>" class="btn btn-sm btn-outline-primary">Открыть</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
