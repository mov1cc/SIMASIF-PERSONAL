<?php

namespace App\Models;

use App\Core\Model;

class AddonLayanan extends Model
{
    protected string $table = 'addon_layanan';

    /**
     * Semua add-on aktif milik layanan aktif (untuk form booking publik).
     */
    public function allActive(): array
    {
        return $this->query(
            "SELECT a.*
               FROM addon_layanan a
               JOIN layanan l ON l.id = a.layanan_id
              WHERE a.is_active = TRUE AND l.is_active = TRUE
              ORDER BY a.layanan_id ASC, a.nama ASC"
        );
    }

    /**
     * Add-on aktif untuk satu layanan (halaman detail publik).
     */
    public function findActiveByLayanan(int $layananId): array
    {
        return $this->query(
            "SELECT * FROM addon_layanan
              WHERE layanan_id = ? AND is_active = TRUE
              ORDER BY nama ASC",
            [$layananId]
        );
    }

    /**
     * Ambil add-on aktif berdasarkan daftar ID, hanya yang milik layanan tsb.
     * Jumlah baris hasil < jumlah ID berarti ada ID yang tidak valid.
     *
     * @param int[] $ids
     */
    public function findActiveByIds(array $ids, int $layananId): array
    {
        if (empty($ids)) {
            return [];
        }

        $placeholder = implode(',', array_fill(0, count($ids), '?'));

        return $this->query(
            "SELECT * FROM addon_layanan
              WHERE is_active = TRUE
                AND layanan_id = ?
                AND id IN ($placeholder)
              ORDER BY id ASC",
            [$layananId, ...array_values($ids)]
        );
    }
}