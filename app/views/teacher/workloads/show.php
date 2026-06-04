<div class="app-card mb-4">
    <div class="card-body padded">
        <h2 class="h4 mb-2"><?= e($workload['module_name']) ?></h2>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span><?= status_badge($workload['status']) ?></span>
            <span class="badge bg-light text-dark border">Группа <?= e($workload['study_group'] ?? '') ?: '—' ?></span>
            <span class="text-muted">нагрузка <?= (int)$workload['practice_hours'] ?> ч. · срок до <?= e($workload['deadline']) ?></span>
        </div>
    </div>
</div>

<?php if ($readonly): ?>
<div class="alert alert-success">График сдан <?= e($workload['submitted_at'] ?? '') ?>.</div>
<?php endif; ?>

<?php
$hs = $hoursSummary;
$barClass = $hs['counted'] > $hs['limit'] ? 'bg-danger' : ($hs['percent'] >= 100 ? 'bg-success' : 'bg-primary');
?>
<div class="alert alert-light border mb-4" id="hours-summary-panel">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <strong>Часы по графику:</strong>
            <span id="hours-counted-text"><?= number_format($hs['counted'], 1, '.', '') ?></span>
            из <span id="hours-limit-text"><?= (int)$hs['limit'] ?></span> ч.
            <span class="text-muted small <?= $hs['excluded'] > 0 ? '' : 'd-none' ?>" id="hours-excluded-wrap">
                (не в зачёт: <span id="hours-excluded-text"><?= number_format($hs['excluded'], 1, '.', '') ?></span> ч.)
            </span>
        </div>
        <span class="badge <?= $hs['counted'] > $hs['limit'] ? 'bg-danger' : 'bg-primary' ?>" id="hours-percent-badge"><?= (int)$hs['percent'] ?>%</span>
    </div>
    <div class="progress mt-2">
        <div class="progress-bar <?= $barClass ?>" id="hours-progress-bar" style="width:<?= min(100, (int)$hs['percent']) ?>%"></div>
    </div>
    <p class="text-muted mb-0 mt-2">
        В зачёт идут только будние дни. Суббота, воскресенье и <a href="#" data-bs-toggle="collapse" data-bs-target="#kz-holidays-hint">праздники Казахстана</a> не считаются.
    </p>
    <div class="collapse text-muted" id="kz-holidays-hint">
        Даты праздников заданы в системе (см. <code>app/config/kazakhstan_holidays.php</code>).
    </div>
</div>

<form method="post" id="workloadScheduleForm"
      data-hours-limit="<?= (int)$hs['limit'] ?>"
      data-holiday-dates="<?= e(json_encode($holidayDates ?? [], JSON_UNESCAPED_UNICODE)) ?>">
    <?= csrf_field() ?>
    <div class="app-card mb-4">
        <div class="card-body padded">
            <?php
            $progress = $hs['percent'];
            $hoursSummary = $hs;
            require APP_PATH . '/views/partials/workload_schedule_form.php';
            ?>
        </div>
    </div>

    <?php if (!$readonly): ?>
    <div class="mb-3">
        <label class="form-label">Комментарий к отправке</label>
        <textarea name="comment" class="form-control" rows="2" placeholder="Необязательно"><?= e($workload['comment'] ?? '') ?></textarea>
    </div>
    <div class="form-actions-bar sticky-actions">
        <button type="submit" formaction="<?= base_url('teacher/workloads/' . $workload['id'] . '/save') ?>" formmethod="post" class="btn btn-secondary">
            <i class="bi bi-save"></i> Сохранить черновик
        </button>
        <button type="submit" formaction="<?= base_url('teacher/workloads/' . $workload['id'] . '/submit') ?>" formmethod="post" class="btn btn-primary" id="btn-submit-schedule"
                onclick="return confirm('Отправить график администратору? После отправки редактирование будет недоступно.')">
            <i class="bi bi-send"></i> Отправить
        </button>
    </div>
    <?php endif; ?>
</form>

<a href="<?= base_url('teacher') ?>" class="btn btn-outline-secondary mt-4">← К списку</a>

<?php if (!$readonly): ?>
<script src="<?= asset('js/workload-schedule.js') ?>"></script>
<?php endif; ?>
