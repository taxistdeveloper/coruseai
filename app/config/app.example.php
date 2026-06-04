<?php

return [
    'name'     => 'Графики преподавателей',
    // Базовый URL приложения (без слэша в конце)
    'url'      => 'http://localhost:8888/ecollege',
    // Путь от корня сайта до папки проекта (для MAMP в htdocs — /ecollege)
    'web_base' => '/ecollege',
    'timezone' => 'Asia/Almaty',
    'upload'   => [
        'max_size'  => 15 * 1024 * 1024,
        'word_ext'  => ['doc', 'docx'],
    ],
    'pagination' => 15,
    'colors' => [
        'primary'   => '#2563eb',
        'secondary' => '#3b82f6',
    ],
    'editor'              => require __DIR__ . '/editor.php',
    'wordonline'          => require __DIR__ . '/wordonline.php',
    'kazakhstan_holidays' => require __DIR__ . '/kazakhstan_holidays.php',
];
