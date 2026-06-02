<?php foreach (['success', 'error', 'warning'] as $type): ?>
    <?php if ($msg = flash($type)): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : ($type === 'warning' ? 'warning' : 'success') ?> alert-dismissible fade show" role="alert">
            <?= e($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
