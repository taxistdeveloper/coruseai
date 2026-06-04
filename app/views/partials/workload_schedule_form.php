<?php
/**
 * @var list<array{module_name:string,section:string,topic:string,date:string,time_start:string,time_end:string,hours:float,place:string,is_dot:bool}> $entries
 * @var bool $readonly
 * @var int $progress
 * @var array|null $hoursSummary
 */
$scheduleService = $scheduleService ?? new \App\Services\WorkloadScheduleService();
$readonly = $readonly ?? false;
$progress = $progress ?? 0;
$rowMeta = isset($hoursSummary) && is_array($hoursSummary) ? ($hoursSummary['rows'] ?? []) : [];
?>
<?php if (!$readonly): ?>
<div class="schedule-progress-header">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="progress-label">Заполнение полей</span>
        <span class="badge bg-primary"><?= (int)$progress ?>%</span>
    </div>
    <div class="progress mb-3">
        <div class="progress-bar" style="width:<?= min(100, (int)$progress) ?>%"></div>
    </div>
</div>
<?php endif; ?>

<div class="workload-schedule-wrap">
    <table class="table table-bordered workload-schedule-table mb-0">
        <thead>
        <tr>
            <th style="width:3rem">№</th>
            <th class="schedule-curriculum-cell">Наименование модуля, раздела, темы</th>
            <th>Дата</th>
            <th>Начало</th>
            <th>Окончание</th>
            <th style="width:4.5rem">Часы</th>
            <th>Место проведения</th>
            <th style="width:4rem" class="text-center">ДОТ</th>
            <?php if (!$readonly): ?>
            <th style="width:3rem"></th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody id="schedule-entries-body">
        <?php foreach ($entries as $idx => $entry):
            $meta = $rowMeta[$idx] ?? null;
            $rowClass = '';
            if ($meta && !empty($meta['reason'])) {
                $rowClass = 'schedule-row-excluded';
            } elseif ($meta && !empty($meta['counted'])) {
                $rowClass = 'schedule-row-counted';
            }
            $rowHours = (float) ($entry['hours'] ?? 0);
        ?>
        <tr class="schedule-entry-row <?= e($rowClass) ?>" data-row-index="<?= (int)$idx ?>">
            <td class="text-muted text-center row-num"><?= $idx + 1 ?></td>
            <?php $curriculum = $scheduleService->formatCurriculum($entry); ?>
            <td class="schedule-curriculum-cell">
                <?php if ($readonly): ?>
                <?= e($curriculum) ?: '—' ?>
                <?php else: ?>
                <input type="text" name="entries[<?= $idx ?>][curriculum]" class="form-control form-control-sm"
                       value="<?= e($curriculum) ?>" placeholder="Модуль, раздел, тема">
                <?php endif; ?>
            </td>
            <td class="schedule-datetime-cell">
                <?php if ($readonly): ?>
                <?= e($entry['date']) ?: '—' ?>
                <?php else: ?>
                <input type="date" name="entries[<?= $idx ?>][date]" class="form-control form-control-sm field-hours-trigger entry-date"
                       value="<?= e($entry['date']) ?>">
                <?php endif; ?>
            </td>
            <td class="schedule-datetime-cell">
                <?php if ($readonly): ?>
                <?= e($entry['time_start']) ?: '—' ?>
                <?php else: ?>
                <input type="time" name="entries[<?= $idx ?>][time_start]" class="form-control form-control-sm field-hours-trigger entry-time-start"
                       value="<?= e($entry['time_start']) ?>">
                <?php endif; ?>
            </td>
            <td class="schedule-datetime-cell">
                <?php if ($readonly): ?>
                <?= e($entry['time_end']) ?: '—' ?>
                <?php else: ?>
                <input type="time" name="entries[<?= $idx ?>][time_end]" class="form-control form-control-sm field-hours-trigger entry-time-end"
                       value="<?= e($entry['time_end']) ?>">
                <?php endif; ?>
            </td>
            <td class="align-middle text-center">
                <span class="entry-hours-display fw-semibold <?= $rowHours > 0 ? 'text-success' : 'text-muted' ?>">
                    <?= $rowHours > 0 ? number_format($rowHours, 1, '.', '') : '—' ?>
                </span>
                <?php if ($meta && !empty($meta['label'])): ?>
                <div class="small <?= !empty($meta['reason']) ? 'text-warning' : (!empty($meta['counted']) ? 'text-success' : 'text-danger') ?> row-hours-hint mt-1">
                    <?= e($meta['label']) ?>
                </div>
                <?php else: ?>
                <div class="small text-muted row-hours-hint mt-1 d-none"></div>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($readonly): ?>
                <?= e($entry['place']) ?: '—' ?>
                <?php if (!empty($entry['is_dot'])): ?>
                <span class="badge bg-info text-dark ms-1">ДОТ</span>
                <?php endif; ?>
                <?php else: ?>
                <input type="text" name="entries[<?= $idx ?>][place]"
                       class="form-control form-control-sm place-input field-hours-trigger <?= !empty($entry['is_dot']) ? 'is-dot-required' : '' ?>"
                       value="<?= e($entry['place']) ?>"
                       placeholder="<?= !empty($entry['is_dot']) ? 'Платформа, ссылка (обязательно для ДОТ)' : 'Аудитория / адрес' ?>">
                <?php endif; ?>
            </td>
            <td class="text-center align-middle">
                <?php if ($readonly): ?>
                <?= !empty($entry['is_dot']) ? '✓' : '—' ?>
                <?php else: ?>
                <input type="checkbox" name="entries[<?= $idx ?>][is_dot]" value="1" class="form-check-input dot-checkbox field-hours-trigger"
                       <?= !empty($entry['is_dot']) ? 'checked' : '' ?>>
                <?php endif; ?>
            </td>
            <?php if (!$readonly): ?>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-outline-danger btn-remove-row" title="Удалить строку">&times;</button>
            </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (!$readonly): ?>
<p class="text-muted mt-3 mb-2">Часы считаются автоматически: окончание минус начало (например 08:30–14:00 = 5,5 ч.).</p>
<button type="button" class="btn btn-outline-primary mt-1" id="btn-add-schedule-row">
    <i class="bi bi-plus-lg" aria-hidden="true"></i> Добавить строку
</button>
<template id="schedule-row-template">
    <tr class="schedule-entry-row">
        <td class="text-muted text-center row-num"></td>
        <td class="schedule-curriculum-cell"><input type="text" class="form-control form-control-sm" data-field="curriculum" placeholder="Модуль, раздел, тема"></td>
        <td class="schedule-datetime-cell"><input type="date" class="form-control form-control-sm field-hours-trigger entry-date" data-field="date"></td>
        <td class="schedule-datetime-cell"><input type="time" class="form-control form-control-sm field-hours-trigger entry-time-start" data-field="time_start"></td>
        <td class="schedule-datetime-cell"><input type="time" class="form-control form-control-sm field-hours-trigger entry-time-end" data-field="time_end"></td>
        <td class="align-middle text-center">
            <span class="entry-hours-display fw-semibold text-muted">—</span>
            <div class="small text-muted row-hours-hint mt-1 d-none"></div>
        </td>
        <td><input type="text" class="form-control form-control-sm place-input field-hours-trigger" data-field="place" placeholder="Аудитория / адрес"></td>
        <td class="text-center align-middle">
            <input type="checkbox" value="1" class="form-check-input dot-checkbox field-hours-trigger" data-field="is_dot">
        </td>
        <td class="text-center align-middle">
            <button type="button" class="btn btn-outline-danger btn-remove-row" title="Удалить строку">&times;</button>
        </td>
    </tr>
</template>
<?php endif; ?>
