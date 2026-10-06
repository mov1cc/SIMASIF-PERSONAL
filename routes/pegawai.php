<?php

use App\Middlewares\AuthMiddleware;
use App\Middlewares\RoleMiddleware;

$pegawai = [
    AuthMiddleware::class,
    RoleMiddleware::class . ':pegawai',
];

// Dashboard
$router->get('/pegawai/dashboard', 'Pegawai\DashboardController@index', $pegawai);

// Layanan (Tahap 4)
$router->get('/pegawai/layanan',          'Pegawai\LayananController@index',   $pegawai);
$router->get('/pegawai/layanan/create',   'Pegawai\LayananController@create',  $pegawai);
$router->post('/pegawai/layanan/store',   'Pegawai\LayananController@store',   $pegawai);
$router->get('/pegawai/layanan/edit',     'Pegawai\LayananController@edit',    $pegawai);
$router->post('/pegawai/layanan/update',  'Pegawai\LayananController@update',  $pegawai);
$router->post('/pegawai/layanan/delete',  'Pegawai\LayananController@destroy', $pegawai);