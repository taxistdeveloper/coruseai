<?php
/**
 * @var array $displayRows строки с ячейками (value, editable, text)
 * @var bool $readonly
 * @var int $progress
 */
$readonly = $readonly ?? false;
$progress = $progress ?? 0;
?>
<?php if (!$readonly): ?>
<div class="d-flex justify-content-between align-items-center mb-2">
    <span class="text-muted small">Заполнение графика</span>
    <span class="badge bg-primary"><?= (int)$progress ?>%</span>
</div>
<div class="progress mb-3" style="height:6px">
    <div class="progress-bar" style="width:<?= min(100, (int)$progress) ?>%"></div>
</div>
<?php endif; ?>

<div class="template-form-wrap">
    <table class="table table-bordered template-form-table mb-0">
        <tbody>
        <?php foreach ($displayRows as $row): ?>
        <tr>
            <?php foreach ($row['cells'] as $cell):
                $colspan = (int)($cell['colspan'] ?? 1);
                $isHeader = empty($cell['editable']);
            ?>
            <td class="<?= $isHeader ? 'template-label' : 'template-input-cell' ?>" <?= $colspan > 1 ? 'colspan="' . $colspan . '"' : '' ?>>
                <?php if (!$isHeader && !$readonly): ?>
                <input type="text"
                       name="cells[<?= e($cell['id']) ?>]"
                       value="<?= e($cell['value'] ?? '') ?>"
                       class="form-control form-control-sm template-input"
                       placeholder="<?= e($cell['text'] ?: '...') ?>">
                <?php elseif (!$isHeader && $readonly): ?>
                <span class="template-filled"><?= e($cell['value'] ?? '') ?: '—' ?></span>
                <?php else: ?>
                <span class="template-label-text"><?= e($cell['value'] ?? $cell['text'] ?? '') ?></span>
                <?php endif; ?>
            </td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
