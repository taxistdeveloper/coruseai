<?php

/**
 * Режимы редактирования графика:
 * - superdoc   — Word внутри сайта (без Docker, через SuperDoc)
 * - external   — скачать → Word на ПК → загрузить
 * - onlyoffice — редактор в браузере (нужен Docker + enabled в onlyoffice.php)
 */
return [
    'default_tab' => 'superdoc',
    'superdoc'    => [
        'enabled' => true,
        'cdn_css' => 'https://cdn.jsdelivr.net/npm/superdoc/dist/style.css',
        'cdn_js'  => 'https://cdn.jsdelivr.net/npm/superdoc/dist/superdoc.es.js',
    ],
];
