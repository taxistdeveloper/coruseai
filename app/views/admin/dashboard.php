<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Всего преподавателей</div>
                <div class="display-6 fw-bold text-primary"><?= (int) $stats['total_teachers'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Сдали график</div>
                <div class="display-6 fw-bold text-success"><?= (int) $stats['submitted'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Не сдали</div>
                <div class="display-6 fw-bold text-danger"><?= (int) $stats['not_submitted'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Выполнение</div>
                <div class="display-6 fw-bold text-secondary"><?= e((string) $stats['percent']) ?>%</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= base_url('admin') ?>">
            <div class="col-md-4">
                <label class="form-label small">Поиск</label>
                <input type="search" name="q" class="form-control" value="<?= e($search) ?>" placeholder="ФИО, модуль...">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Преподаватель</label>
                <select name="teacher_id" class="form-select">
                    <option value="">Все</option>
                    <?php foreach ($teachers as $t): ?>
                    <option value="<?= (int)$t['id'] ?>" <?= ($teacherId ?? null) == $t['id'] ? 'selected' : '' ?>><?= e($t['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Статус</label>
                <select name="status" class="form-select">
                    <option value="">Все</option>
                    <?php foreach (['assigned','in_progress','submitted','overdue'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($statusFilter ?? '') === $s ? 'selected' : '' ?>><?= strip_tags(status_badge($s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Фильтр</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between">
        <span>Нагрузки и сдача графиков</span>
        <div>
            <a href="<?= base_url('admin/workloads/create') ?>" class="btn btn-sm btn-primary">+ Нагрузка</a>
            <a href="<?= base_url('admin/submissions/export') ?>" class="btn btn-sm btn-outline-primary">Экспорт</a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-hover datatable">
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
                        <a href="<?= base_url('admin/workloads/' . $row['workload_id']) ?>" class="btn btn-sm btn-outline-primary">Открыть</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
