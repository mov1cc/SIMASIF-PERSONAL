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
}