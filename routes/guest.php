<?php

// Halaman publik: TANPA middleware (akses bebas tanpa login)

// Beranda sementara = daftar layanan
$router->get('/', 'Guest\LayananController@index');

// Layanan (Tahap 6)
$router->get('/layanan',        'Guest\LayananController@index');
$router->get('/layanan/detail', 'Guest\LayananController@detail');