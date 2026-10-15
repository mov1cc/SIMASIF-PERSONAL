<?php

namespace App\Controllers\Pegawai;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Helpers\CsrfHelper;
use App\Helpers\ValidationHelper;
use App\Models\AddonLayanan;
use App\Models\Layanan;
use PDOException;

class AddonLayananController extends Controller
{
    private Layanan $layanan;
    private AddonLayanan $addon;

    public function __construct()
    {
        $this->layanan = new Layanan();
        $this->addon   = new AddonLayanan();
    }

    /**
     * GET /pegawai/layanan/addon?layanan_id=1
     */
    public function index(): void
    {
        $layanan = $this->layananOrFail($this->queryInt('layanan_id'));

        $this->render('pegawai/addon/index', [
            'title'   => 'Add-on ' . $layanan['nama'],
            'layanan' => $layanan,
            'items'   => $this->addon->allByLayanan((int) $layanan['id']),
            'edit'    => null,
        ]);
    }

    /**
     * GET /pegawai/layanan/addon/edit?id=1
     */
    public function edit(): void
    {
        $row     = $this->addonOrFail($this->queryInt('id'));
        $layanan = $this->layananOrFail((int) $row['layanan_id']);

        $this->render('pegawai/addon/index', [
            'title'   => 'Add-on ' . $layanan['nama'],
            'layanan' => $layanan,
            'items'   => $this->addon->allByLayanan((int) $layanan['id']),
            'edit'    => $row,
        ]);
    }

    /**
     * POST /pegawai/layanan/addon/store
     */
    public function store(): void
    {
        $this->verifyCsrf();

        $layanan = $this->layananOrFail((int) $this->postString('layanan_id'));
        $balik   = '/pegawai/layanan/addon?layanan_id=' . (int) $layanan['id'];

        $input  = $this->collectInput();
        $errors = $this->validateInput($input);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            $this->redirect($balik);
        }

        try {
            $this->addon->create($input + ['layanan_id' => (int) $layanan['id']]);
        } catch (PDOException $e) {
            // 23505 = unique_violation (layanan_id + nama)
            if ((string) $e->getCode() === '23505') {
                Session::flash('errors', ['nama' => 'Add-on dengan nama ini sudah ada di layanan ini.']);
                Session::flash('old', $input);
                $this->redirect($balik);
            }
            throw $e;
        }

        Session::flash('success', 'Add-on berhasil ditambahkan.');
        $this->redirect($balik);
    }

    /**
     * POST /pegawai/layanan/addon/update
     */
    public function update(): void
    {
        $this->verifyCsrf();

        $row   = $this->addonOrFail((int) $this->postString('id'));
        $balik = '/pegawai/layanan/addon?layanan_id=' . (int) $row['layanan_id'];
        $edit  = '/pegawai/layanan/addon/edit?id=' . (int) $row['id'];

        $input  = $this->collectInput();
        $errors = $this->validateInput($input);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            $this->redirect($edit);
        }

        try {
            $this->addon->update((int) $row['id'], $input);
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23505') {
                Session::flash('errors', ['nama' => 'Add-on dengan nama ini sudah ada di layanan ini.']);
                Session::flash('old', $input);
                $this->redirect($edit);
            }
            throw $e;
        }

        Session::flash('success', 'Add-on berhasil diperbarui.');
        $this->redirect($balik);
    }

    /**
     * POST /pegawai/layanan/addon/delete
     */
    public function destroy(): void
    {
        $this->verifyCsrf();

        $row   = $this->addonOrFail((int) $this->postString('id'));
        $balik = '/pegawai/layanan/addon?layanan_id=' . (int) $row['layanan_id'];

        try {
            $this->addon->delete((int) $row['id']);
            Session::flash('success', 'Add-on berhasil dihapus.');
        } catch (PDOException $e) {
            // 23001 = restrict_violation, 23503 = foreign_key_violation
            if (in_array((string) $e->getCode(), ['23001', '23503'], true)) {
                Session::flash(
                    'error',
                    'Add-on tidak bisa dihapus karena sudah pernah dipesan. '
                    . 'Nonaktifkan saja agar tidak bisa dipilih pelanggan baru.'
                );
            } else {
                throw $e;
            }
        }

        $this->redirect($balik);
    }

    /**
     * POST /pegawai/layanan/addon/toggle
     */
    public function toggle(): void
    {
        $this->verifyCsrf();

        $row   = $this->addonOrFail((int) $this->postString('id'));
        $aktif = Request::post('is_active') !== null;

        $this->addon->setActive((int) $row['id'], $aktif);

        Session::flash(
            'success',
            'Add-on "' . $row['nama'] . '" '
            . ($aktif ? 'diaktifkan.' : 'dinonaktifkan.')
        );

        $this->redirect('/pegawai/layanan/addon?layanan_id=' . (int) $row['layanan_id']);
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

    private function layananOrFail(int $id): array
    {
        $row = $id > 0 ? $this->layanan->find($id) : null;

        if ($row === null) {
            throw new \Exception('Layanan tidak ditemukan.', 404);
        }

        return $row;
    }

    private function addonOrFail(int $id): array
    {
        $row = $id > 0 ? $this->addon->find($id) : null;

        if ($row === null) {
            throw new \Exception('Add-on tidak ditemukan.', 404);
        }

        return $row;
    }

    private function queryInt(string $key): int
    {
        $v = Request::get($key, 0);

        return is_scalar($v) ? (int) $v : 0;
    }

    private function postString(string $key): string
    {
        $value = Request::post($key, '');

        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function collectInput(): array
    {
        $durasi = $this->postString('tambahan_durasi_menit');

        return [
            'nama'                  => $this->postString('nama'),
            'deskripsi'             => $this->postString('deskripsi'),
            'harga'                 => $this->postString('harga'),
            // Kosong dianggap 0 (add-on tidak menambah durasi)
            'tambahan_durasi_menit' => $durasi === '' ? '0' : $durasi,
            'is_active'             => Request::post('is_active') !== null,
        ];
    }

    private function validateInput(array $input): array
    {
        return ValidationHelper::validate(
            $input,
            [
                'nama'                  => ['required', 'max:150'],
                'deskripsi'             => ['max:1000'],
                'harga'                 => ['required', 'numeric', 'gte:0', 'lte:9999999999'],
                'tambahan_durasi_menit' => ['integer', 'gte:0', 'lte:600'],
            ],
            [
                'nama'                  => 'Nama add-on',
                'deskripsi'             => 'Deskripsi',
                'harga'                 => 'Harga',
                'tambahan_durasi_menit' => 'Tambahan durasi',
            ]
        );
    }
}