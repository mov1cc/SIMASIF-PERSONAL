<?php

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Response;
use App\Core\Session;

class RoleMiddleware implements Middleware
{
    /** @var string[] */
    private array $roles;

    /**
     * Dipanggil Router dari penulisan: RoleMiddleware::class . ':owner'
     * atau RoleMiddleware::class . ':owner,pegawai'
     */
    public function __construct(string ...$roles)
    {
        $this->roles = $roles;
    }

    public function handle(): void
    {
        // Belum login -> ke halaman login
        if (!Session::has('user_id')) {
            Session::flash('error', 'Silakan login terlebih dahulu.');
            Response::redirect('/login');
        }

        // Login tapi role tidak cocok -> 403
        if (!in_array(Session::get('role'), $this->roles, true)) {
            throw new \Exception('Akses ditolak.', 403);
        }
    }
}