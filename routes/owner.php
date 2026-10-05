<?php

use App\Middlewares\AuthMiddleware;
use App\Middlewares\RoleMiddleware;

$router->get('/owner/dashboard', 'Owner\DashboardController@index', [
    AuthMiddleware::class,
    RoleMiddleware::class . ':owner',
]);