<?php

namespace App\Controllers\Guest;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Helpers\CsrfHelper;
use App\Helpers\ValidationHelper;
use App\Models\AddonLayanan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Pemesanan;
use App\Services\KodeUnikService;
use App\Services\PemesananService;

class PemesananController extends Controller
{
    private Layanan $layanan;
    private Pemesanan $pemesanan;
    private PemesananService $service;
    private AddonLayanan $addon;

    public function __construct()
    {
        $this->layanan   = new Layanan();
        $this->pemesanan = new Pemesanan();
        $this->service   = new PemesananService();
        $this->addon     = new AddonLayanan();
    }

    /**
     * GET /pemesanan?layanan_id=1
     */
    public function showForm(): void
    {
        $raw = Request::get('layanan_id', 0);

        $this->render('guest/pemesanan/form', [
            'title'    => 'Pesan Layanan',
            'items'    => $this->layanan->findActive(),
            'addons'   => $this->addon->allActive(),
            'selected' => is_scalar($raw) ? (int) $raw : 0,
        ]);
    }

    /**
     * POST /pemesanan/store
     */
    public function store(): void
    {
        $this->verifyCsrf();

        $input  = $this->collectInput();
        $errors = $this->validateInput($input);

        // Layanan harus ada dan aktif
        $layanan = null;

        if (!isset($errors['layanan_id'])) {
            $layanan = $this->layanan->findActiveById(
                (int) $input['layanan_id']
            );

            if ($layanan === null) {
                $errors['layanan_id'] = 'Layanan tidak tersedia.';
            }
        }

        // Add-on harus aktif dan sesuai layanan yang dipilih
        $addons = [];

        if ($layanan !== null && !empty($input['addon_ids'])) {
            $addons = $this->addon->findActiveByIds(
                $input['addon_ids'],
                (int) $layanan['id']
            );

            if (count($addons) !== count($input['addon_ids'])) {
                $errors['addon_id'] = 'Ada add-on yang tidak tersedia untuk layanan ini.';
                $addons = [];
            }
        }

        // Aturan jadwal (masa depan + jam operasional)
        // Durasi sudah termasuk addon
        if (
            $layanan !== null
            && !isset($errors['tanggal'])
            && !isset($errors['jam'])
        ) {
            $pesan = $this->service->validasiJadwal(
                $input['tanggal'],
                $input['jam'],
                PemesananService::durasiMenit(
                    $layanan,
                    $addons
                )
            );

            if ($pesan !== null) {
                $errors['jam'] = $pesan;
            }
        }

        if (!empty($errors)) {
            $this->gagal($input, $errors);
        }

        $input['no_hp'] = Pelanggan::normalizeNoHp(
            $input['no_hp']
        );

        $hasil = $this->service->buatBooking(
            $input,
            $layanan,
            $addons
        );

        if (!$hasil['ok']) {
            Session::flash(
                'error',
                'Jadwal pada tanggal dan jam tersebut sudah terisi. Silakan pilih waktu lain.'
            );

            $this->gagal($input, []);
        }

        $this->redirect(
            '/pemesanan/konfirmasi?kode=' . urlencode($hasil['kode'])
        );
    }

    /**
     * GET /pemesanan/konfirmasi?kode=SF-XXXXXX
     * (kode sudah divalidasi KodeUnikMiddleware)
     */
    public function konfirmasi(): void
    {
        $kode = KodeUnikService::normalize(
            (string) Request::get('kode', '')
        );

        $row = $this->pemesanan->findByKodeUnik($kode);

        if ($row === null) {
            throw new \Exception(
                'Pemesanan tidak ditemukan.',
                404
            );
        }

        $this->render('guest/pemesanan/konfirmasi', [
            'title'     => 'Pemesanan Berhasil',
            'pemesanan' => $row,
            'addons'    => $this->pemesanan->addonDipesan(
                (int) $row['id']
            ),
        ]);
    }


    // ------------------------------------------------------------------
    // Helper privat
    // ------------------------------------------------------------------

    private function gagal(array $input, array $errors): never
    {
        if (!empty($errors)) {
            Session::flash('errors', $errors);
        }

        Session::flash('old', $input);

        $this->redirect('/pemesanan');
    }


    private function verifyCsrf(): void
    {
        if (!CsrfHelper::verify(Request::post('_csrf'))) {
            throw new \Exception(
                'Token keamanan tidak valid.',
                403
            );
        }
    }


    private function postString(string $key): string
    {
        $value = Request::post($key, '');

        return is_scalar($value)
            ? trim((string) $value)
            : '';
    }


    private function collectInput(): array
    {
        return [
            'layanan_id' => $this->postString('layanan_id'),
            'nama'       => $this->postString('nama'),
            'no_hp'      => $this->postString('no_hp'),
            'tanggal'    => $this->postString('tanggal'),

            // input type=time bisa HH:MM atau HH:MM:SS
            'jam'        => substr(
                $this->postString('jam'),
                0,
                5
            ),

            'catatan'    => $this->postString('catatan_khusus'),

            'addon_ids'  => $this->collectAddonIds(),
        ];
    }


    /**
     * ID add-on dari checkbox addon_id[],
     * unik dan bilangan bulat positif
     */
    private function collectAddonIds(): array
    {
        $raw = Request::post('addon_id', []);

        if (!is_array($raw)) {
            return [];
        }

        $ids = [];

        foreach ($raw as $v) {
            if (
                is_scalar($v)
                && ctype_digit((string) $v)
                && (int) $v > 0
            ) {
                $ids[(int) $v] = true;
            }
        }

        return array_keys($ids);
    }


    private function validateInput(array $input): array
    {
        $errors = ValidationHelper::validate(
            $input,
            [
                'layanan_id' => ['required', 'integer', 'gt:0'],
                'nama'       => ['required', 'max:100'],
                'no_hp'      => ['required', 'max:30'],
                'tanggal'    => ['required'],
                'jam'        => ['required'],
                'catatan'    => ['max:1000'],
            ],
            [
                'layanan_id' => 'Layanan',
                'nama'       => 'Nama',
                'no_hp'      => 'Nomor HP',
                'tanggal'    => 'Tanggal',
                'jam'        => 'Jam',
                'catatan'    => 'Catatan khusus',
            ]
        );


        if (!isset($errors['no_hp'])) {
            $noHp = Pelanggan::normalizeNoHp(
                $input['no_hp']
            );

            if (!preg_match('/^0\d{8,14}$/', $noHp)) {
                $errors['no_hp'] =
                    'Format Nomor HP tidak valid '
                    . '(contoh: 081234567890 atau +6281234567890).';
            }
        }


        if (
            !isset($errors['tanggal'])
            && !preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $input['tanggal']
            )
        ) {
            $errors['tanggal'] = 'Format tanggal tidak valid.';
        }


        if (
            !isset($errors['jam'])
            && !preg_match(
                '/^([01]\d|2[0-3]):[0-5]\d$/',
                $input['jam']
            )
        ) {
            $errors['jam'] = 'Format jam tidak valid.';
        }


        return $errors;
    }
}