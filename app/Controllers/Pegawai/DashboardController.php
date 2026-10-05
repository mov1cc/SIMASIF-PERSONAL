<?php

namespace App\Controllers\Pegawai;

use App\Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->render('pegawai/dashboard', [
            'title' => 'Dashboard Pegawai',
        ]);
    }
}