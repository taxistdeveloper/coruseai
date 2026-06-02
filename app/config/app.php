<?php

return [
    'name'     => 'Графики преподавателей',
    'url'      => 'http://localhost/ecollege',
    'web_base' => '/ecollege',
    'timezone' => 'Asia/Almaty',
    'upload'   => [
        'max_size'  => 15 * 1024 * 1024,
        'word_ext'  => ['doc', 'docx'],
    ],
    'pagination' => 15,
    'colors' => [
        'primary'   => '#2D5B87',
        'secondary' => '#4A90E2',
    ],
    'onlyoffice' => require __DIR__ . '/onlyoffice.php',
    'editor'     => require __DIR__ . '/editor.php',
    'wordonline' => require __DIR__ . '/wordonline.php',
];
