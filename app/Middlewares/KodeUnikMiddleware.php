<?php

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Models\Pemesanan;
use App\Services\KodeUnikService;

class KodeUnikMiddleware implements Middleware
{
    public function handle(): void
    {
        $raw  = Request::get('kode', '');
        $kode = is_scalar($raw) ? KodeUnikService::normalize((string) $raw) : '';

        // Format salah ATAU tidak ada di DB -> pesan yang sama (404),
        // supaya orang tidak bisa menebak kode.
        if (!KodeUnikService::isValidFormat($kode)
            || !(new Pemesanan())->kodeExists($kode)
        ) {
            throw new \Exception('Kode pemesanan tidak ditemukan.', 404);
        }
    }
}