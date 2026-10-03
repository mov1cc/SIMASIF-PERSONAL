<?php

namespace App\Controllers;

use App\Core\Controller;

class TestController extends Controller
{
    public function index(): void
    {
        $this->render('test', [
            'title' => 'Hello SIMASIF'
        ]);
    }
}