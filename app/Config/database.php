<?php

return [
    'driver' => $_ENV['DB_CONNECTION'] ?? 'pgsql',

    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',

    'port' => $_ENV['DB_PORT'] ?? 5432,

    'database' => $_ENV['DB_DATABASE'] ?? 'simasif',

    'username' => $_ENV['DB_USERNAME'] ?? 'postgres',

    'password' => $_ENV['DB_PASSWORD'] ?? '',

    'charset' => 'utf8'
];