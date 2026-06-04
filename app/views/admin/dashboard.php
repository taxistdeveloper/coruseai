<div class="row g-4 mb-4 stat-scroll-row">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-label">Всего преподавателей</div>
                <div class="stat-value text-primary"><?= (int) $stats['total_teachers'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-label">Сдали график</div>
                <div class="stat-value text-success"><?= (int) $stats['submitted'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-label">Не сдали</div>
                <div class="stat-value text-danger"><?= (int) $stats['not_submitted'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-label">Выполнение</div>
                <div class="stat-value text-secondary"><?= e((string) $stats['percent']) ?>%</div>
            </div>
        </div>
    </div>
</div>

<div class="app-card mb-4">
    <div class="card-body padded filter-bar">
        <form class="row g-3 align-items-end" method="get" action="<?= base_url('admin') ?>">
            <div class="col-md-4">
                <label class="form-label" for="dash-q">Поиск</label>
                <input type="search" name="q" id="dash-q" class="form-control" value="<?= e($search) ?>" placeholder="ФИО, модуль...">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="dash-teacher">Преподаватель</label>
                <select name="teacher_id" id="dash-teacher" class="form-select">
                    <option value="">Все</option>
                    <?php foreach ($teachers as $t): ?>
                    <option value="<?= (int)$t['id'] ?>" <?= ($teacherId ?? null) == $t['id'] ? 'selected' : '' ?>><?= e($t['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="dash-status">Статус</label>
                <select name="status" id="dash-status" class="form-select">
                    <option value="">Все</option>
                    <?php foreach (['assigned','in_progress','submitted','overdue'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($statusFilter ?? '') === $s ? 'selected' : '' ?>><?= strip_tags(status_badge($s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Применить</button>
            </div>
        </form>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header">
        <span>Нагрузки и сдача графиков</span>
        <div class="btn-group-actions">
            <a href="<?= base_url('admin/workloads/create') ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Нагрузка
            </a>
            <a href="<?= base_url('admin/submissions/export') ?>" class="btn btn-outline-primary">Экспорт</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover datatable mb-0">
            <thead>
                <tr>
                    <th>ФИО</th>
                    <th>Модуль</th>
                    <th>Часов</th>
                    <th>Срок</th>
                    <th>Статус</th>
                    <th>%</th>
                    <th>Дата сдачи</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row['teacher_name']) ?></td>
                    <td><?= e($row['module_name']) ?></td>
                    <td><?= (int)$row['practice_hours'] ?></td>
                    <td><?= e($row['deadline']) ?></td>
                    <td><?= status_badge($row['status']) ?></td>
                    <td><?= (int)($row['progress_percent'] ?? 0) ?>%</td>
                    <td><?= e($row['submitted_at'] ?? '—') ?></td>
                    <td>
                        <a href="<?= base_url('admin/workloads/' . $row['workload_id']) ?>" class="btn btn-outline-primary">Открыть</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
