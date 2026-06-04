<div class="app-card mb-4">
    <div class="card-body padded filter-bar">
        <form class="row g-3 align-items-end" method="get">
            <div class="col-md-4">
                <label class="form-label" for="sub-q">Поиск</label>
                <input type="search" name="q" id="sub-q" class="form-control" value="<?= e($search) ?>" placeholder="ФИО, модуль...">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="sub-teacher">Преподаватель</label>
                <select name="teacher_id" id="sub-teacher" class="form-select">
                    <option value="">Все преподаватели</option>
                    <?php foreach ($teachers as $t): ?>
                    <option value="<?= (int)$t['id'] ?>" <?= ($teacherId ?? null) == $t['id'] ? 'selected' : '' ?>><?= e($t['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Применить</button>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('admin/submissions/export?' . http_build_query(array_filter(['q' => $search, 'teacher_id' => $teacherId]))) ?>" class="btn btn-outline-primary w-100">Экспорт DOCX</a>
            </div>
        </form>
    </div>
</div>

<div class="app-card">
    <div class="card-body padded">
        <div class="table-responsive">
            <table class="table table-hover datatable mb-0">
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
                            <div class="d-flex flex-wrap gap-2">
                                <a href="<?= base_url('admin/workloads/' . $s['id']) ?>" class="btn btn-outline-primary">График</a>
                                <a href="<?= base_url('admin/submissions/export?' . http_build_query(['workload_id' => $s['id']])) ?>" class="btn btn-outline-secondary" title="Скачать grafik.docx">DOCX</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php require APP_PATH . '/views/partials/pagination.php'; ?>
    </div>
</div>
