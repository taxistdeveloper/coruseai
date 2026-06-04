<?php
$readonly = $readonly ?? true;
if (empty($cells)) {
    $cells = ['A1' => ''];
}
$coords = array_keys($cells);
$maxRow = 20;
$maxCol = 8;
foreach ($coords as $c) {
    if (preg_match('/^([A-Z]+)(\d+)$/', $c, $m)) {
        $col = ord($m[1][strlen($m[1]) - 1]) - 64;
        $row = (int) $m[2];
        $maxRow = max($maxRow, min($row + 2, 50));
        $maxCol = max($maxCol, min($col + 2, 15));
    }
}
$cols = range('A', chr(64 + $maxCol));
?>
<div class="app-card">
    <div class="app-card-header">Таблица графика</div>
    <div class="card-body table-responsive schedule-grid-wrap p-0">
        <table class="table table-bordered table-sm schedule-grid mb-0">
            <thead>
                <tr><th></th>
                <?php foreach ($cols as $col): ?><th class="text-center"><?= $col ?></th><?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <?php for ($r = 1; $r <= $maxRow; $r++): ?>
            <tr>
                <th class="text-muted"><?= $r ?></th>
                <?php foreach ($cols as $col):
                    $ref = $col . $r;
                    $val = $cells[$ref] ?? '';
                ?>
                <td>
                    <?php if ($readonly): ?>
                    <span class="cell-readonly"><?= e($val) ?></span>
                    <?php else: ?>
                    <input type="text" name="cells[<?= $ref ?>]" value="<?= e($val) ?>" class="form-control form-control-sm border-0">
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>
