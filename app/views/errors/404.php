<?php
http_response_code(404);
$title = '404';
?>
<div class="error-page">
    <div class="error-code">404</div>
    <h2 class="mt-3">Страница не найдена</h2>
    <p class="lead text-muted">Запрашиваемый адрес не существует или был перемещён.</p>
    <a href="<?= base_url() ?>" class="btn btn-primary btn-lg mt-3">На главную</a>
</div>
