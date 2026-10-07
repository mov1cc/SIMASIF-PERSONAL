<?php

namespace App\Controllers\Pegawai;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Helpers\CsrfHelper;
use App\Helpers\ValidationHelper;
use App\Models\Layanan;
use PDOException;

class LayananController extends Controller
{
    private Layanan $layanan;

    public function __construct()
    {
        $this->layanan = new Layanan();
    }

        /**
     * GET /pegawai/layanan
     */
    public function index(): void
    {
        $this->render('pegawai/layanan/index', [
            'title' => 'Katalog Layanan',
            'items' => $this->layanan->all(),
            'stat'  => $this->layanan->statistikBulanIni(),
        ]);
    }

    /**
     * GET /pegawai/layanan/create
     */
    public function create(): void
    {
        $this->render('pegawai/layanan/form', [
            'title'   => 'Tambah Layanan',
            'layanan' => null,
            'action'  => '/pegawai/layanan/store',
        ]);
    }

    /**
     * POST /pegawai/layanan/store
     */
    public function store(): void
    {
        $this->verifyCsrf();

        $input  = $this->collectInput();
        $errors = $this->validateInput($input);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            $this->redirect('/pegawai/layanan/create');
        }

        $this->layanan->create($input);

        Session::flash('success', 'Layanan berhasil ditambahkan.');
        $this->redirect('/pegawai/layanan');
    }

    /**
     * GET /pegawai/layanan/edit?id=1
     */
    public function edit(): void
    {
        $row = $this->findOrFail((int) Request::get('id', 0));

        $this->render('pegawai/layanan/form', [
            'title'   => 'Edit Layanan',
            'layanan' => $row,
            'action'  => '/pegawai/layanan/update',
        ]);
    }

    /**
     * POST /pegawai/layanan/update
     */
    public function update(): void
    {
        $this->verifyCsrf();

        $id  = (int) Request::post('id', 0);
        $this->findOrFail($id);

        $input  = $this->collectInput();
        $errors = $this->validateInput($input);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            $this->redirect('/pegawai/layanan/edit?id=' . $id);
        }

        $this->layanan->update($id, $input);

        Session::flash('success', 'Layanan berhasil diperbarui.');
        $this->redirect('/pegawai/layanan');
    }

    /**
     * POST /pegawai/layanan/delete
     */
    public function destroy(): void
    {
        $this->verifyCsrf();

        $id = (int) Request::post('id', 0);
        $this->findOrFail($id);

        try {
            $this->layanan->delete($id);
            Session::flash('success', 'Layanan berhasil dihapus.');
        } catch (PDOException $e) {
            // 23001 = restrict_violation (ON DELETE RESTRICT)
            // 23503 = foreign_key_violation (jaga-jaga untuk constraint NO ACTION)
            if (in_array((string) $e->getCode(), ['23001', '23503'], true)) {
                Session::flash(
                    'error',
                    'Layanan tidak bisa dihapus karena sudah dipakai pada pemesanan. '
                    . 'Nonaktifkan saja agar tidak tampil di halaman publik.'
                );
            } else {
                throw $e;
            }
        }

        $this->redirect('/pegawai/layanan');
    }

        /**
     * POST /pegawai/layanan/toggle
     * Tampilkan/sembunyikan layanan di portal publik (switch pada kartu)
     */
    public function toggle(): void
    {
        $this->verifyCsrf();

        $id  = (int) Request::post('id', 0);
        $row = $this->findOrFail($id);

        $aktif = Request::post('is_active') !== null;

        $this->layanan->setActive($id, $aktif);

        Session::flash(
            'success',
            'Layanan "' . $row['nama'] . '" '
            . ($aktif ? 'ditampilkan di' : 'disembunyikan dari')
            . ' portal publik.'
        );

        $this->redirect('/pegawai/layanan');
    }

    // ------------------------------------------------------------------
    // Helper privat
    // ------------------------------------------------------------------

    private function verifyCsrf(): void
    {
        if (!CsrfHelper::verify(Request::post('_csrf'))) {
            throw new \Exception('Token keamanan tidak valid.', 403);
        }
    }

    /**
     * Ambil layanan atau lempar 404
     */
    private function findOrFail(int $id): array
    {
        $row = $id > 0 ? $this->layanan->find($id) : null;

        if ($row === null) {
            throw new \Exception('Layanan tidak ditemukan.', 404);
        }

        return $row;
    }

    /**
     * Ambil nilai POST sebagai string (aman dari input array)
     */
    private function postString(string $key): string
    {
        $value = Request::post($key, '');

        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function collectInput(): array
    {
        return [
            'nama'                  => $this->postString('nama'),
            'deskripsi'             => $this->postString('deskripsi'),
            'harga'                 => $this->postString('harga'),
            'estimasi_durasi_menit' => $this->postString('estimasi_durasi_menit'),
            'is_active'             => Request::post('is_active') !== null,
        ];
    }

    private function validateInput(array $input): array
    {
        $rules = [
            'nama'  => ['required', 'max:150'],
            'harga' => ['required', 'numeric', 'gte:0', 'lte:9999999999'],
        ];

        // Durasi opsional, tapi kalau diisi harus > 0 (sesuai CHECK database)
        if ($input['estimasi_durasi_menit'] !== '') {
            $rules['estimasi_durasi_menit'] = ['integer', 'gt:0', 'lte:1440'];
        }

        return ValidationHelper::validate(
            $input,
            $rules,
            [
                'nama'                  => 'Nama layanan',
                'harga'                 => 'Harga',
                'estimasi_durasi_menit' => 'Estimasi durasi',
            ]
        );
    }
}