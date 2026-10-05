<?php

use App\Middlewares\AuthMiddleware;
use App\Middlewares\RoleMiddleware;

$router->get('/pegawai/dashboard', 'Pegawai\DashboardController@index', [
    AuthMiddleware::class,
    RoleMiddleware::class . ':pegawai',
]);