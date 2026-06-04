<?php $hs = $hoursSummary; ?>
<div class="row mb-3">
    <div class="col-lg-4">
        <div class="app-card">
            <div class="card-body padded">
                <p><strong>Преподаватель:</strong> <?= e($workload['teacher_name']) ?></p>
                <?php if (!empty($workload['study_group'])): ?>
                <p><strong>Группа:</strong> <?= e($workload['study_group']) ?></p>
                <?php endif; ?>
                <p><strong>Модуль (нагрузка):</strong> <?= e($workload['module_name']) ?></p>
                <p><strong>Лимит:</strong> <?= (int)$workload['practice_hours'] ?> ч.</p>
                <p><strong>В зачёт:</strong> <?= number_format($hs['counted'], 1, '.', '') ?> ч.</p>
                <?php if ($hs['excluded'] > 0): ?>
                <p><strong>Не в зачёт:</strong> <?= number_format($hs['excluded'], 1, '.', '') ?> ч.</p>
                <?php endif; ?>
                <p><strong>Срок:</strong> <?= e($workload['deadline']) ?></p>
                <p><strong>Статус:</strong> <?= status_badge($workload['status']) ?></p>
                <?php if (!empty($workload['submitted_at'])): ?>
                <p><strong>Сдано:</strong> <?= e($workload['submitted_at']) ?></p>
                <?php endif; ?>
                <?php if (!empty($workload['comment'])): ?>
                <p><strong>Комментарий:</strong> <?= e($workload['comment']) ?></p>
                <?php endif; ?>
                <a href="<?= base_url('admin/workloads/' . $workload['id'] . '/edit') ?>" class="btn btn-outline-primary w-100 mb-2">Изменить нагрузку</a>
                <?php if (($workload['status'] ?? '') === 'submitted'): ?>
                <a href="<?= base_url('admin/submissions/export?workload_id=' . (int)$workload['id']) ?>" class="btn btn-outline-secondary w-100">Скачать DOCX</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="app-card">
            <div class="app-card-header">
                <span>График занятий</span>
                <span class="badge bg-primary"><?= number_format($hs['counted'], 1, '.', '') ?> / <?= (int)$hs['limit'] ?> ч.</span>
            </div>
            <div class="card-body padded">
                <?php if (empty($entries)): ?>
                <p class="text-muted mb-0">Преподаватель ещё не заполнил график.</p>
                <?php else: ?>
                <p class="text-muted">Учитываются только будни без праздников РК.</p>
                <?php
                $readonly = true;
                $progress = $hs['percent'];
                require APP_PATH . '/views/partials/workload_schedule_form.php';
                ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
