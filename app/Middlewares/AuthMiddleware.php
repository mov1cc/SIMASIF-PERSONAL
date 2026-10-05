<?php

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware implements Middleware
{
    public function handle(): void
    {
        if (!Session::has('user_id')) {
            Session::flash('error', 'Silakan login terlebih dahulu.');
            Response::redirect('/login');
        }
    }
}