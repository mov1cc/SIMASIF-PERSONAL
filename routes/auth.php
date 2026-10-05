<?php

use App\Middlewares\AuthMiddleware;

$router->get('/login', 'AuthController@showLoginForm');
$router->post('/login', 'AuthController@login');

$router->post('/logout', 'AuthController@logout', [
    AuthMiddleware::class,
]);