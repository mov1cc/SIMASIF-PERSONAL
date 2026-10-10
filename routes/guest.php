<?php

use App\Middlewares\KodeUnikMiddleware;

// Halaman publik: TANPA middleware auth (akses bebas tanpa login)

// Beranda sementara = daftar layanan
$router->get('/', 'Guest\LayananController@index');

// Layanan (Tahap 6)
$router->get('/layanan',        'Guest\LayananController@index');
$router->get('/layanan/detail', 'Guest\LayananController@detail');

// Pemesanan (Tahap 7)
$router->get('/pemesanan',             'Guest\PemesananController@showForm');
$router->post('/pemesanan/store',      'Guest\PemesananController@store');
$router->get('/pemesanan/konfirmasi',  'Guest\PemesananController@konfirmasi', [
    KodeUnikMiddleware::class,
]);