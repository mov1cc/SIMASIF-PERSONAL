<?php

namespace App\Controllers\Pegawai;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Helpers\CsrfHelper;
use App\Helpers\ValidationHelper;
use App\Models\Layanan;
use App\Models\LayananGambar;
use App\Services\UploadService;

class LayananGambarController extends Controller
{
    /** Batas jumlah gambar per layanan (sistem bukan penyimpanan skala besar) */
    private const MAKS_GAMBAR = 8;

    /** Batas ukuran per gambar: 5 MB */
    private const MAKS_BYTE = 5 * 1024 * 1024;

    private Layanan $layanan;
    private LayananGambar $gambar;

    public function __construct()
    {
        $this->layanan = new Layanan();
        $this->gambar  = new LayananGambar();
    }

    /**
     * GET /pegawai/layanan/gambar?layanan_id=1
     */
    public function index(): void
    {
        $layanan = $this->layananOrFail($this->queryInt('layanan_id'));

        $this->render('pegawai/layanan-gambar/index', [
            'title'    => 'Galeri ' . $layanan['nama'],
            'layanan'  => $layanan,
            'items'    => $this->gambar->allByLayanan((int) $layanan['id']),
            'maks'     => self::MAKS_GAMBAR,
            'maksMb'   => self::MAKS_BYTE / 1048576,
        ]);
    }

    /**
     * POST /pegawai/layanan/gambar/upload   (multipart/form-data)
     */
    public function upload(): void
    {
        // Kalau post_max_size terlewati PHP mengosongkan $_POST dan $_FILES,
        // sehingga CSRF akan gagal dengan pesan yang membingungkan.
        if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            Session::flash('error', 'Ukuran berkas melebihi batas server (post_max_size di php.ini).');
            $this->redirect('/pegawai/layanan');
        }

        $this->verifyCsrf();

        $layanan = $this->layananOrFail((int) $this->postString('layanan_id'));
        $id      = (int) $layanan['id'];
        $balik   = '/pegawai/layanan/gambar?layanan_id=' . $id;

        $jumlah = $this->gambar->countByLayanan($id);

        if ($jumlah >= self::MAKS_GAMBAR) {
            Session::flash('error', 'Maksimal ' . self::MAKS_GAMBAR . ' gambar per layanan. Hapus salah satu dulu.');
            $this->redirect($balik);
        }

        $keterangan = $this->postString('keterangan');

        if (mb_strlen($keterangan) > 150) {
            Session::flash('error', 'Keterangan maksimal 150 karakter.');
            $this->redirect($balik);
        }

        $file  = Request::file('gambar');
        $hasil = UploadService::simpan(
            is_array($file) ? $file : null,
            $this->dirAbs(),
            UploadService::MIME_GAMBAR,
            self::MAKS_BYTE,
            true
        );

        if (!$hasil['ok']) {
            Session::flash('error', $hasil['error']);
            $this->redirect($balik);
        }

        try {
            $this->gambar->create([
                'layanan_id'     => $id,
                'nama_file_asli' => $hasil['nama_asli'],
                'nama_file'      => $hasil['nama_file'],
                'path_file'      => LayananGambar::DIR_RELATIF . '/' . $hasil['nama_file'],
                'ukuran_file'    => $hasil['ukuran'],
                'mime_type'      => $hasil['mime'],
                'keterangan'     => $keterangan !== '' ? $keterangan : null,
                // Gambar pertama otomatis jadi cover
                'is_cover'       => $jumlah === 0,
                'diunggah_oleh'  => (int) Session::get('user_id'),
            ]);
        } catch (\Throwable $e) {
            // Jangan tinggalkan file yatim kalau insert gagal
            UploadService::hapus($this->dirAbs(), $hasil['nama_file']);
            throw $e;
        }

        Session::flash('success', 'Gambar berhasil diunggah.');
        $this->redirect($balik);
    }

    /**
     * POST /pegawai/layanan/gambar/cover
     */
    public function cover(): void
    {
        $this->verifyCsrf();

        $row = $this->gambarOrFail((int) $this->postString('id'));

        $this->gambar->setCover((int) $row['layanan_id'], (int) $row['id']);

        Session::flash('success', 'Gambar cover diperbarui.');
        $this->redirect($this->balik($row));
    }

    /**
     * POST /pegawai/layanan/gambar/update   (keterangan + urutan)
     */
    public function update(): void
    {
        $this->verifyCsrf();

        $row = $this->gambarOrFail((int) $this->postString('id'));

        $keterangan = $this->postString('keterangan');
        $urutan     = $this->postString('urutan');

        $errors = ValidationHelper::validate(
            ['keterangan' => $keterangan, 'urutan' => $urutan === '' ? '0' : $urutan],
            [
                'keterangan' => ['max:150'],
                'urutan'     => ['integer', 'gte:0', 'lte:999'],
            ],
            ['keterangan' => 'Keterangan', 'urutan' => 'Urutan']
        );

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            $this->redirect($this->balik($row));
        }

        $this->gambar->updateMeta(
            (int) $row['id'],
            $keterangan !== '' ? $keterangan : null,
            $urutan === '' ? 0 : (int) $urutan
        );

        Session::flash('success', 'Detail gambar diperbarui.');
        $this->redirect($this->balik($row));
    }

    /**
     * POST /pegawai/layanan/gambar/delete
     */
    public function destroy(): void
    {
        $this->verifyCsrf();

        $row     = $this->gambarOrFail((int) $this->postString('id'));
        $dihapus = $this->gambar->hapus((int) $row['id']);

        if ($dihapus !== null) {
            UploadService::hapus($this->dirAbs(), $dihapus['nama_file']);
        }

        Session::flash('success', 'Gambar berhasil dihapus.');
        $this->redirect($this->balik($row));
    }

    // ------------------------------------------------------------------
    // Helper privat
    // ------------------------------------------------------------------

    private function dirAbs(): string
    {
        return BASE_PATH . '/public/' . LayananGambar::DIR_RELATIF;
    }

    private function balik(array $row): string
    {
        return '/pegawai/layanan/gambar?layanan_id=' . (int) $row['layanan_id'];
    }

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

    private function gambarOrFail(int $id): array
    {
        $row = $id > 0 ? $this->gambar->find($id) : null;

        if ($row === null) {
            throw new \Exception('Gambar tidak ditemukan.', 404);
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
}