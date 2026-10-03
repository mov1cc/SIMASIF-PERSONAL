<?php

return [

    'name' => $_ENV['APP_NAME'] ?? 'SIMASIF',

    'url' => $_ENV['APP_URL'] ?? 'http://localhost',

    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Jakarta',

    'environment' => $_ENV['APP_ENV'] ?? 'production',

    'debug' => filter_var(
        $_ENV['APP_DEBUG'] ?? false,
        FILTER_VALIDATE_BOOLEAN
    ),

    'session' => [
        'name' => $_ENV['SESSION_NAME'] ?? 'SIMASIF_SESSION',

        'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 120)
    ]

];