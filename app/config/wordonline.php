<?php

/**
 * Microsoft Word Online (Office for the web)
 *
 * ПРОСМОТР (viewer):
 *   - Нужен публичный HTTPS-адрес сайта (не localhost)
 *   - Укажите public_base_url
 *
 * РЕДАКТИРОВАНИЕ (wopi):
 *   - Нужен WOPI-host + Office Online Server или Microsoft 365 (CSPP)
 *   - Лицензии Microsoft 365 у пользователей
 *   - См. docs/WORD_ONLINE.md
 */
return [
    'enabled'        => true,
    'viewer_enabled' => true,
    // Публичный URL сайта с HTTPS, например: https://college.edu.kz/ecollege
    // На localhost просмотр Word Online не работает — только на боевом сервере.
    'public_base_url' => '',

    'wopi' => [
        'enabled'        => false,
        'office_online_url' => 'https://word-edit.officeapps.live.com/we/wordeditorframe.aspx',
        'host_id'        => 'ecollege',
    ],
];
