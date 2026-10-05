<?php

namespace App\Controllers\Owner;

use App\Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->render('owner/dashboard', [
            'title' => 'Dashboard Owner',
        ]);
    }
}