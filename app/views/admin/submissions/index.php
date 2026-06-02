<form class="row g-2 mb-3" method="get">
    <div class="col-md-4"><input type="search" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Поиск"></div>
    <div class="col-md-4">
        <select name="teacher_id" class="form-select">
            <option value="">Все преподаватели</option>
            <?php foreach ($teachers as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= ($teacherId ?? null) == $t['id'] ? 'selected' : '' ?>><?= e($t['fullname']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Фильтр</button></div>
    <div class="col-md-2"><a href="<?= base_url('admin/submissions/export?' . http_build_query(array_filter(['q'=>$search,'teacher_id'=>$teacherId]))) ?>" class="btn btn-outline-primary w-100">CSV</a></div>
</form>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <table class="table datatable">
            <thead>
                <tr>
                    <th>Преподаватель</th>
                    <th>Модуль</th>
                    <th>Часов</th>
                    <th>Заполнение</th>
                    <th>Дата сдачи</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $s): ?>
                <tr>
                    <td><?= e($s['teacher_name']) ?></td>
                    <td><?= e($s['module_name']) ?></td>
                    <td><?= (int)$s['practice_hours'] ?></td>
                    <td><?= (int)($s['progress_percent'] ?? 100) ?>%</td>
                    <td><?= e($s['submitted_at']) ?></td>
                    <td>
                        <a href="<?= base_url('admin/workloads/' . $s['id']) ?>" class="btn btn-sm btn-outline-primary">График</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php require APP_PATH . '/views/partials/pagination.php'; ?>
    </div>
</div>
