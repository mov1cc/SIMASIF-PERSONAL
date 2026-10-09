<?php

namespace App\Controllers\Guest;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Layanan;

class LayananController extends Controller
{
    private Layanan $layanan;

    public function __construct()
    {
        $this->layanan = new Layanan();
    }

    /**
     * GET /layanan
     * Daftar layanan aktif
     */
    public function index(): void
    {
        $this->render('guest/layanan/index', [
            'title' => 'Layanan Fotografi',
            'items' => $this->layanan->findActive(),
        ]);
    }

    /**
     * GET /layanan/detail?id=1
     * Detail satu layanan aktif
     */
    public function detail(): void
    {
        $raw = Request::get('id', 0);
        $id  = is_scalar($raw) ? (int) $raw : 0;

        $row = $id > 0 ? $this->layanan->findActiveById($id) : null;

        if ($row === null) {
            throw new \Exception('Layanan tidak ditemukan.', 404);
        }

        $this->render('guest/layanan/detail', [
            'title'   => $row['nama'],
            'layanan' => $row,
        ]);
    }
}