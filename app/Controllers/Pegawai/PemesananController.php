<?php

namespace App\Controllers\Pegawai;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\FormatHelper;
use App\Models\Pemesanan;

class PemesananController extends Controller
{
    private Pemesanan $pemesanan;

    public function __construct()
    {
        $this->pemesanan = new Pemesanan();
    }

    /**
     * GET /pegawai/pemesanan?q=&status=&dari=&sampai=
     */
    public function index(): void
    {
        $status = $this->queryString('status');

        // Whitelist: hanya nilai enum yang sah yang boleh masuk query
        if (!array_key_exists($status, FormatHelper::daftarStatusPemesanan())) {
            $status = '';
        }

        $filter = [
            'q'      => $this->queryString('q'),
            'status' => $status,
            'dari'   => $this->queryDate('dari'),
            'sampai' => $this->queryDate('sampai'),
        ];

        $this->render('pegawai/pemesanan/index', [
            'title'  => 'Data Pemesanan',
            'items'  => $this->pemesanan->all($filter),
            'filter' => $filter,
        ]);
    }

    /**
     * GET /pegawai/pemesanan/detail?id=1
     */
    public function detail(): void
    {
        $raw = Request::get('id', 0);
        $id  = is_scalar($raw) ? (int) $raw : 0;

        $row = $id > 0 ? $this->pemesanan->findDetail($id) : null;

        if ($row === null) {
            throw new \Exception('Pemesanan tidak ditemukan.', 404);
        }

        $this->render('pegawai/pemesanan/detail', [
            'title'    => 'Detail ' . $row['kode_unik'],
            'p'        => $row,
            'riwayat'  => $this->pemesanan->riwayatWorkflow($id),
        ]);
    }

    // ------------------------------------------------------------------
    // Helper privat
    // ------------------------------------------------------------------

    private function queryString(string $key): string
    {
        $value = Request::get($key, '');

        return is_scalar($value) ? trim((string) $value) : '';
    }

    /** Kembalikan Y-m-d kalau valid, selain itu string kosong */
    private function queryDate(string $key): string
    {
        $v = $this->queryString($key);

        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m)
            || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])
        ) {
            return '';
        }

        return $v;
    }
}