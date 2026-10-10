<?php

namespace App\Services;

use App\Models\Pemesanan;

class KodeUnikService
{
    private const PREFIX   = 'SF-';
    private const LENGTH   = 6;
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    private const MAX_TRY  = 10;

    public function __construct(private Pemesanan $pemesanan)
    {
    }

    /**
     * Buat kode unik yang belum dipakai di database.
     */
    public function generate(): string
    {
        for ($i = 0; $i < self::MAX_TRY; $i++) {
            $kode = self::PREFIX . $this->randomPart();

            if (!$this->pemesanan->kodeExists($kode)) {
                return $kode;
            }
        }

        throw new \RuntimeException('Gagal membuat kode unik pemesanan.');
    }

    /**
     * Rapikan input kode dari pengguna: trim + huruf besar.
     */
    public static function normalize(string $kode): string
    {
        return strtoupper(trim($kode));
    }

    /**
     * Cek format saja (belum cek ke database).
     */
    public static function isValidFormat(string $kode): bool
    {
        $pola = '/^' . preg_quote(self::PREFIX, '/')
            . '[' . self::ALPHABET . ']{' . self::LENGTH . '}$/';

        return preg_match($pola, $kode) === 1;
    }

    private function randomPart(): string
    {
        $max  = strlen(self::ALPHABET) - 1;
        $hasil = '';

        for ($i = 0; $i < self::LENGTH; $i++) {
            $hasil .= self::ALPHABET[random_int(0, $max)];
        }

        return $hasil;
    }
}