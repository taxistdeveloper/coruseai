<div class="page-toolbar">
    <form method="get" class="toolbar-search">
        <input type="search" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Поиск по ФИО, модулю...">
        <button type="submit" class="btn btn-primary">Найти</button>
    </form>
    <a href="<?= base_url('admin/workloads/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Назначить нагрузку
    </a>
</div>

<div class="app-card">
    <div class="table-responsive">
        <table class="table table-hover datatable mb-0">
            <thead>
                <tr>
                    <th>Преподаватель</th>
                    <th>Модуль</th>
                    <th>Группа</th>
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
                    <td><?= e($r['study_group'] ?? '') ?: '—' ?></td>
                    <td><?= (int)$r['practice_hours'] ?></td>
                    <td><?= e($r['deadline']) ?></td>
                    <td><?= status_badge($r['status']) ?></td>
                    <td>
                        <a href="<?= base_url('admin/workloads/' . $r['workload_id']) ?>" class="btn btn-outline-primary">Открыть</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
