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

        /**
     * Cari berdasarkan ID (membuka visibilitas find() milik base Model)
     */
    public function find(int $id): ?array
    {
        return parent::find($id);
    }

    /**
     * Semua add-on satu layanan (aktif dan nonaktif) untuk halaman pegawai.
     */
    public function allByLayanan(int $layananId): array
    {
        return $this->query(
            "SELECT * FROM addon_layanan
              WHERE layanan_id = ?
              ORDER BY is_active DESC, nama ASC",
            [$layananId]
        );
    }

    /**
     * Tambah add-on, mengembalikan ID baru.
     * Melempar PDOException 23505 kalau nama sudah dipakai di layanan yang sama.
     */
    public function create(array $d): int
    {
        $row = $this->first(
            "INSERT INTO addon_layanan
                (layanan_id, nama, deskripsi, harga, tambahan_durasi_menit, is_active)
             VALUES (?, ?, ?, ?, ?, ?)
             RETURNING id",
            [
                $d['layanan_id'],
                $d['nama'],
                ($d['deskripsi'] ?? '') !== '' ? $d['deskripsi'] : null,
                $d['harga'],
                $d['tambahan_durasi_menit'],
                $d['is_active'] ? 'true' : 'false',
            ]
        );

        return (int) $row['id'];
    }

    /**
     * Perbarui add-on (layanan_id tidak bisa dipindah; updated_at diurus trigger).
     */
    public function update(int $id, array $d): bool
    {
        return $this->execute(
            "UPDATE addon_layanan
                SET nama = ?, deskripsi = ?, harga = ?,
                    tambahan_durasi_menit = ?, is_active = ?
              WHERE id = ?",
            [
                $d['nama'],
                ($d['deskripsi'] ?? '') !== '' ? $d['deskripsi'] : null,
                $d['harga'],
                $d['tambahan_durasi_menit'],
                $d['is_active'] ? 'true' : 'false',
                $id,
            ]
        );
    }

    /**
     * Hapus add-on. Melempar PDOException kalau sudah pernah dipesan
     * (pemesanan_addon.addon_id ON DELETE RESTRICT).
     */
    public function delete(int $id): bool
    {
        return $this->execute("DELETE FROM addon_layanan WHERE id = ?", [$id]);
    }

    public function setActive(int $id, bool $aktif): bool
    {
        return $this->execute(
            "UPDATE addon_layanan SET is_active = ? WHERE id = ?",
            [$aktif ? 'true' : 'false', $id]
        );
    }
}