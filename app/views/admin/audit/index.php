<div class="app-card">
    <div class="card-body padded">
        <div class="table-responsive">
            <table class="table table-hover datatable mb-0">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Пользователь</th>
                        <th>Действие</th>
                        <th>Сущность</th>
                        <th>Детали</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="text-nowrap"><?= e($log['created_at']) ?></td>
                    <td><?= e($log['fullname'] ?? '—') ?></td>
                    <td><code><?= e($log['action']) ?></code></td>
                    <td><?= e(($log['entity_type'] ?? '') . ' #' . ($log['entity_id'] ?? '')) ?></td>
                    <td><?= e($log['details'] ?? '') ?></td>
                    <td><?= e($log['ip_address'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php require APP_PATH . '/views/partials/pagination.php'; ?>
    </div>
</div>
