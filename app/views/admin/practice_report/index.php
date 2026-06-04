<div class="app-card mb-4">
    <div class="card-body padded filter-bar">
        <form class="row g-3 align-items-end" method="get" action="<?= base_url('admin/practice-report') ?>">
            <div class="col-md-4">
                <label class="form-label" for="report-date">Дата</label>
                <input type="date" id="report-date" name="date" class="form-control" value="<?= e($date) ?>" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Показать</button>
            </div>
        </form>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header flex-column align-items-start">
        <h2 class="h4 mb-1">Практики на <?= e(date('d.m.Y', strtotime($date))) ?></h2>
        <p class="text-muted mb-0">Занятия из графиков преподавателей на выбранный день</p>
    </div>
    <div class="card-body padded">
        <?php if ($rows === []): ?>
        <p class="text-muted mb-0">На эту дату в графиках нет занятий.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                <tr>
                    <th>Название практики</th>
                    <th>Руководитель</th>
                    <th>Время</th>
                    <th>Аудитория</th>
                    <th>Группа</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row['practice_name']) ?: '—' ?></td>
                    <td><?= e($row['supervisor']) ?: '—' ?></td>
                    <td class="text-nowrap"><?= e($row['time']) ?: '—' ?></td>
                    <td><?= e($row['auditorium']) ?: '—' ?></td>
                    <td><?= e($row['group']) ?: '—' ?></td>
                    <td>
                        <a href="<?= base_url('admin/workloads/' . (int)$row['workload_id']) ?>" class="btn btn-outline-primary">График</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="text-muted mt-4 mb-0">Всего: <?= count($rows) ?></p>
        <?php endif; ?>
    </div>
</div>
