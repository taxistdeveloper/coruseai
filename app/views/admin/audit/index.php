<div class="card shadow-sm border-0">
    <div class="card-body">
        <table class="table table-sm datatable">
            <thead><tr><th>Дата</th><th>Пользователь</th><th>Действие</th><th>Сущность</th><th>Детали</th><th>IP</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= e($log['created_at']) ?></td>
                <td><?= e($log['fullname'] ?? '—') ?></td>
                <td><code><?= e($log['action']) ?></code></td>
                <td><?= e(($log['entity_type'] ?? '') . ' #' . ($log['entity_id'] ?? '')) ?></td>
                <td><?= e($log['details'] ?? '') ?></td>
                <td><?= e($log['ip_address'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php require APP_PATH . '/views/partials/pagination.php'; ?>
    </div>
</div>
