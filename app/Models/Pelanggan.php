<?php

namespace App\Models;

use App\Core\Model;

class Pelanggan extends Model
{
    protected string $table = 'pelanggan';

    /**
     * Normalisasi nomor HP ke format lokal Indonesia.
     *   "+62 812-3456-789" -> "08123456789"
     *   "6281234567890"    -> "081234567890"
     *   "0812 3456 789"    -> "08123456789"
     */
    public static function normalizeNoHp(string $noHp): string
    {
        $digits = preg_replace('/\D/', '', $noHp) ?? '';

        if (str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        }

        return $digits;
    }

    /**
     * Semua pelanggan + jumlah pemesanannya.
     * $q (opsional) mencari di nama atau no HP.
     */
    public function all(?string $q = null): array
    {
        $sql = "SELECT p.*, COUNT(m.id) AS total_pemesanan
                  FROM pelanggan p
                  LEFT JOIN pemesanan m ON m.pelanggan_id = p.id";
        $params = [];

        if ($q !== null && $q !== '') {
            $like   = '%' . addcslashes($q, '%_\\') . '%';
            $sql   .= " WHERE p.nama ILIKE ? OR p.no_hp LIKE ?";
            $params = [$like, $like];
        }

        $sql .= " GROUP BY p.id
                  ORDER BY p.created_at DESC, p.id DESC";

        return $this->query($sql, $params);
    }

    /**
     * Cari berdasarkan ID (membuka visibilitas find() milik base Model)
     */
    public function find(int $id): ?array
    {
        return parent::find($id);
    }

    /**
     * Cari berdasarkan no HP (sudah ternormalisasi).
     * $exceptId dipakai saat edit supaya data milik sendiri tidak dianggap duplikat.
     */
    public function findByNoHp(string $noHp, ?int $exceptId = null): ?array
    {
        if ($exceptId === null) {
            return $this->first(
                "SELECT * FROM pelanggan WHERE no_hp = ? LIMIT 1",
                [$noHp]
            );
        }

        return $this->first(
            "SELECT * FROM pelanggan WHERE no_hp = ? AND id <> ? LIMIT 1",
            [$noHp, $exceptId]
        );
    }

    /**
     * Tambah pelanggan, mengembalikan ID baru
     *
     * @param array{nama:string, no_hp:string} $data
     */
    public function create(array $data): int
    {
        $row = $this->first(
            "INSERT INTO pelanggan (nama, no_hp)
             VALUES (?, ?)
             RETURNING id",
            [$data['nama'], $data['no_hp']]
        );

        return (int) $row['id'];
    }

    /**
     * Perbarui pelanggan (updated_at diurus trigger database)
     */
    public function update(int $id, array $data): bool
    {
        return $this->execute(
            "UPDATE pelanggan SET nama = ?, no_hp = ? WHERE id = ?",
            [$data['nama'], $data['no_hp'], $id]
        );
    }

    /**
     * Hapus pelanggan.
     * Melempar PDOException kalau sudah dipakai pemesanan (FK RESTRICT).
     */
    public function delete(int $id): bool
    {
        return $this->execute(
            "DELETE FROM pelanggan WHERE id = ?",
            [$id]
        );
    }
}