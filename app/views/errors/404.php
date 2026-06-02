<?php
http_response_code(404);
$title = '404';
?>
<div class="text-center py-5">
    <h1>404</h1>
    <p>Страница не найдена</p>
    <a href="<?= base_url() ?>" class="btn btn-primary">На главную</a>
</div>
