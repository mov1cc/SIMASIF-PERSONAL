<?php

namespace App\Controllers\Pegawai;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Pemesanan;
use DateTimeImmutable;

class JadwalController extends Controller
{
    private Pemesanan $pemesanan;

    public function __construct()
    {
        $this->pemesanan = new Pemesanan();
    }

    /**
     * GET /pegawai/jadwal?tanggal=2026-10-10
     */
    public function index(): void
    {
        $tanggal = $this->tanggalValid(Request::get('tanggal', ''));

        $dipilih = new DateTimeImmutable($tanggal);
        $senin   = $dipilih->modify('monday this week');
        $minggu  = $senin->modify('+6 days');

        $rows = $this->pemesanan->jadwalRentang(
            $senin->format('Y-m-d'),
            $minggu->format('Y-m-d')
        );

        // Kelompokkan per tanggal
        $perHari = [];
        foreach ($rows as $r) {
            $perHari[$r['tanggal_jadwal']][] = $r;
        }

        // Strip 7 hari
        $minggu7 = [];
        for ($i = 0; $i < 7; $i++) {
            $d   = $senin->modify("+{$i} days")->format('Y-m-d');
            $minggu7[] = [
                'tanggal' => $d,
                'jumlah'  => count($perHari[$d] ?? []),
            ];
        }

        $sesi = $this->tandaiBentrok($perHari[$tanggal] ?? []);

        $this->render('pegawai/jadwal/index', [
            'title'    => 'Jadwal Operasional',
            'tanggal'  => $tanggal,
            'hariIni'  => date('Y-m-d'),
            'sebelum'  => $dipilih->modify('-1 day')->format('Y-m-d'),
            'sesudah'  => $dipilih->modify('+1 day')->format('Y-m-d'),
            'minggu'   => $minggu7,
            'sesi'     => $sesi,
            'adaBentrok' => in_array(true, array_column($sesi, 'bentrok'), true),
        ]);
    }

    // ------------------------------------------------------------------
    // Helper privat
    // ------------------------------------------------------------------

    /** Tanggal Y-m-d yang valid, default hari ini */
    private function tanggalValid(mixed $raw): string
    {
        $v = is_scalar($raw) ? trim((string) $raw) : '';

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m)
            && checkdate((int) $m[2], (int) $m[3], (int) $m[1])
        ) {
            return $v;
        }

        return date('Y-m-d');
    }

    /**
     * Tambah kunci 'mulai', 'selesai' (menit sejak 00:00 dan teks H:i)
     * serta 'bentrok' (true kalau rentang waktunya beririsan dengan sesi lain).
     * Sesi yang berurutan rapat (selesai 10:00, mulai 10:00) tidak dianggap bentrok.
     */
    private function tandaiBentrok(array $rows): array
    {
        foreach ($rows as $i => $r) {
            [$h, $m] = array_map('intval', explode(':', substr($r['jam_jadwal'], 0, 5)));

            $mulai   = $h * 60 + $m;
            $selesai = $mulai + (int) $r['durasi_menit'];

            $rows[$i]['_mulai']   = $mulai;
            $rows[$i]['_selesai'] = $selesai;
            $rows[$i]['selesai_teks'] = sprintf('%02d:%02d', intdiv($selesai, 60) % 24, $selesai % 60);
            $rows[$i]['bentrok']  = false;
        }

        $n = count($rows);
        for ($a = 0; $a < $n; $a++) {
            for ($b = $a + 1; $b < $n; $b++) {
                if ($rows[$a]['_mulai'] < $rows[$b]['_selesai']
                    && $rows[$b]['_mulai'] < $rows[$a]['_selesai']
                ) {
                    $rows[$a]['bentrok'] = true;
                    $rows[$b]['bentrok'] = true;
                }
            }
        }

        return $rows;
    }
}