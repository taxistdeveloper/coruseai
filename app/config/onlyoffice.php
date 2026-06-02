<?php

/**
 * ONLYOFFICE — опционально, только если установлен Document Server (Docker).
 * По умолчанию выключено: используется скачивание .docx → Word на компьютере → загрузка.
 */
return [
    'enabled'             => false,
    'document_server_url' => 'http://localhost:8080',
    'app_url'             => 'http://localhost/ecollege',
    'jwt_secret'          => '',
    'token_secret'        => 'ecollege_doc_secret_change_me',
];
