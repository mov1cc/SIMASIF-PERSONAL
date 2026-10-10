<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format Rupiah: 250000 -> "Rp 250.000"
     * Desimal hanya tampil kalau memang ada (mis. 1500.50).
     */
    public static function rupiah(mixed $nilai): string
    {
        $angka    = (float) $nilai;
        $desimal  = floor($angka) == $angka ? 0 : 2;

        return 'Rp ' . number_format($angka, $desimal, ',', '.');
    }

    /**
     * Durasi menit -> teks: 45 -> "45 menit", 90 -> "1 jam 30 menit"
     */
    public static function durasi(mixed $menit): string
    {
        if ($menit === null || $menit === '') {
            return '-';
        }

        $menit = (int) $menit;

        if ($menit < 60) {
            return "{$menit} menit";
        }

        $jam  = intdiv($menit, 60);
        $sisa = $menit % 60;

        return $sisa === 0 ? "{$jam} jam" : "{$jam} jam {$sisa} menit";
    }

    /**
     * Escape output HTML (singkatan htmlspecialchars)
     */
    public static function e(mixed $nilai): string
    {
        return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Normalisasi nilai boolean dari PostgreSQL ('t'/'f'/true/false) ke bool PHP
     */
    public static function bool(mixed $nilai): bool
    {
        if (is_bool($nilai)) {
            return $nilai;
        }

        // FILTER_VALIDATE_BOOLEAN tidak mengenali 't', jadi dicek manual
        return in_array(
            strtolower(trim((string) $nilai)),
            ['1', 't', 'true', 'y', 'yes', 'on'],
            true
        );
    }

        // ------------------------------------------------------------------
    // Tahap 8: label status & tanggal Indonesia
    // ------------------------------------------------------------------

    private const LABEL_STATUS_PEMESANAN = [
        'pemesanan_dibuat'        => 'Pemesanan Dibuat',
        'menunggu_pembayaran'     => 'Menunggu Pembayaran',
        'pembayaran_diverifikasi' => 'Pembayaran Diverifikasi',
        'terjadwal'               => 'Terjadwal',
        'sesi_foto'               => 'Sesi Foto',
        'proses_editing'          => 'Proses Editing',
        'hasil_siap'              => 'Hasil Siap',
        'selesai'                 => 'Selesai',
    ];

    private const BADGE_STATUS_PEMESANAN = [
        'pemesanan_dibuat'        => 'secondary',
        'menunggu_pembayaran'     => 'warning',
        'pembayaran_diverifikasi' => 'info',
        'terjadwal'               => 'primary',
        'sesi_foto'               => 'primary',
        'proses_editing'          => 'primary',
        'hasil_siap'              => 'success',
        'selesai'                 => 'success',
    ];

    private const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    private const BULAN = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    /** @return array<string,string> kode status => label (urut sesuai alur) */
    public static function daftarStatusPemesanan(): array
    {
        return self::LABEL_STATUS_PEMESANAN;
    }

    public static function statusPemesanan(?string $status): string
    {
        return self::LABEL_STATUS_PEMESANAN[$status ?? ''] ?? '-';
    }

    /** Nama warna Bootstrap: dipakai sebagai text-bg-{warna} */
    public static function statusPemesananBadge(?string $status): string
    {
        return self::BADGE_STATUS_PEMESANAN[$status ?? ''] ?? 'secondary';
    }

    public static function statusPembayaran(?string $status): string
    {
        return match ($status) {
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'lunas'               => 'Lunas',
            'ditolak'             => 'Ditolak',
            default               => 'Belum Bayar',
        };
    }

    public static function statusPembayaranBadge(?string $status): string
    {
        return match ($status) {
            'menunggu_verifikasi' => 'warning',
            'lunas'               => 'success',
            'ditolak'             => 'danger',
            default               => 'secondary',
        };
    }

    /** "2026-10-10" -> "Sabtu" */
    public static function namaHari(string $ymd): string
    {
        return self::HARI[(int) date('N', strtotime($ymd)) - 1];
    }

    /** "2026-10-10" -> "10 Oktober 2026" */
    public static function tanggalIndo(string $ymd): string
    {
        $t = strtotime($ymd);

        return date('j', $t) . ' ' . self::BULAN[(int) date('n', $t)] . ' ' . date('Y', $t);
    }

    /** "09:30:00" -> "09:30" */
    public static function jam(?string $time): string
    {
        return $time === null ? '-' : substr($time, 0, 5);
    }

    /** "081234567890" -> "https://wa.me/6281234567890" */
    public static function linkWhatsApp(string $noHp): string
    {
        $digits = preg_replace('/\D/', '', $noHp) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        return 'https://wa.me/' . $digits;
    }
}