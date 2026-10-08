<?php

namespace App\Controllers\Pegawai;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Helpers\CsrfHelper;
use App\Helpers\ValidationHelper;
use App\Models\Pelanggan;
use PDOException;

class PelangganController extends Controller
{
    private Pelanggan $pelanggan;

    public function __construct()
    {
        $this->pelanggan = new Pelanggan();
    }

    /**
     * GET /pegawai/pelanggan
     */
    public function index(): void
    {
        $q = $this->queryString('q');

        $this->render('pegawai/pelanggan/index', [
            'title' => 'Data Pelanggan',
            'items' => $this->pelanggan->all($q),
            'q'     => $q,
        ]);
    }

    /**
     * GET /pegawai/pelanggan/create
     */
    public function create(): void
    {
        $this->render('pegawai/pelanggan/form', [
            'title'     => 'Tambah Pelanggan',
            'pelanggan' => null,
            'action'    => '/pegawai/pelanggan/store',
        ]);
    }

    /**
     * POST /pegawai/pelanggan/store
     */
    public function store(): void
    {
        $this->verifyCsrf();

        $input  = $this->collectInput();
        $errors = $this->validateInput($input);
        $noHp   = Pelanggan::normalizeNoHp($input['no_hp']);

        if (!isset($errors['no_hp'])) {
            $dupe = $this->pelanggan->findByNoHp($noHp);
            if ($dupe !== null) {
                $errors['no_hp'] = 'Nomor HP sudah terdaftar atas nama '
                    . $dupe['nama'] . '.';
            }
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            $this->redirect('/pegawai/pelanggan/create');
        }

        try {
            $this->pelanggan->create([
                'nama'  => $input['nama'],
                'no_hp' => $noHp,
            ]);
        } catch (PDOException $e) {
            // 23505 = unique_violation (jaga-jaga kalau 2 request bersamaan)
            if ((string) $e->getCode() === '23505') {
                Session::flash('errors', ['no_hp' => 'Nomor HP sudah terdaftar.']);
                Session::flash('old', $input);
                $this->redirect('/pegawai/pelanggan/create');
            }
            throw $e;
        }

        Session::flash('success', 'Pelanggan berhasil ditambahkan.');
        $this->redirect('/pegawai/pelanggan');
    }

    /**
     * GET /pegawai/pelanggan/edit?id=1
     */
    public function edit(): void
    {
        $row = $this->findOrFail((int) Request::get('id', 0));

        $this->render('pegawai/pelanggan/form', [
            'title'     => 'Edit Pelanggan',
            'pelanggan' => $row,
            'action'    => '/pegawai/pelanggan/update',
        ]);
    }

    /**
     * POST /pegawai/pelanggan/update
     */
    public function update(): void
    {
        $this->verifyCsrf();

        $id = (int) Request::post('id', 0);
        $this->findOrFail($id);

        $input  = $this->collectInput();
        $errors = $this->validateInput($input);
        $noHp   = Pelanggan::normalizeNoHp($input['no_hp']);

        if (!isset($errors['no_hp'])) {
            $dupe = $this->pelanggan->findByNoHp($noHp, $id);
            if ($dupe !== null) {
                $errors['no_hp'] = 'Nomor HP sudah dipakai pelanggan lain ('
                    . $dupe['nama'] . ').';
            }
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            $this->redirect('/pegawai/pelanggan/edit?id=' . $id);
        }

        try {
            $this->pelanggan->update($id, [
                'nama'  => $input['nama'],
                'no_hp' => $noHp,
            ]);
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23505') {
                Session::flash('errors', ['no_hp' => 'Nomor HP sudah terdaftar.']);
                Session::flash('old', $input);
                $this->redirect('/pegawai/pelanggan/edit?id=' . $id);
            }
            throw $e;
        }

        Session::flash('success', 'Data pelanggan berhasil diperbarui.');
        $this->redirect('/pegawai/pelanggan');
    }

    /**
     * POST /pegawai/pelanggan/delete
     */
    public function destroy(): void
    {
        $this->verifyCsrf();

        $id = (int) Request::post('id', 0);
        $this->findOrFail($id);

        try {
            $this->pelanggan->delete($id);
            Session::flash('success', 'Pelanggan berhasil dihapus.');
        } catch (PDOException $e) {
            // 23001 = restrict_violation, 23503 = foreign_key_violation
            if (in_array((string) $e->getCode(), ['23001', '23503'], true)) {
                Session::flash(
                    'error',
                    'Pelanggan tidak bisa dihapus karena sudah memiliki pemesanan.'
                );
            } else {
                throw $e;
            }
        }

        $this->redirect('/pegawai/pelanggan');
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

    private function findOrFail(int $id): array
    {
        $row = $id > 0 ? $this->pelanggan->find($id) : null;

        if ($row === null) {
            throw new \Exception('Pelanggan tidak ditemukan.', 404);
        }

        return $row;
    }

    private function postString(string $key): string
    {
        $value = Request::post($key, '');

        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function queryString(string $key): string
    {
        $value = Request::get($key, '');

        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function collectInput(): array
    {
        return [
            'nama'  => $this->postString('nama'),
            'no_hp' => $this->postString('no_hp'),
        ];
    }

    private function validateInput(array $input): array
    {
        $errors = ValidationHelper::validate(
            $input,
            [
                'nama'  => ['required', 'max:100'],
                'no_hp' => ['required', 'max:30'],
            ],
            [
                'nama'  => 'Nama pelanggan',
                'no_hp' => 'Nomor HP',
            ]
        );

        // Format nomor HP (setelah dinormalisasi): diawali 0, total 9-15 digit
        if (!isset($errors['no_hp'])) {
            $noHp = Pelanggan::normalizeNoHp($input['no_hp']);

            if (!preg_match('/^0\d{8,14}$/', $noHp)) {
                $errors['no_hp'] = 'Format Nomor HP tidak valid '
                    . '(contoh: 081234567890 atau +6281234567890).';
            }
        }

        return $errors;
    }
}