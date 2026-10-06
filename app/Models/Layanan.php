<?php

namespace App\Models;

use App\Core\Model;

class Layanan extends Model
{
    protected string $table = 'layanan';

    /**
     * Semua layanan (untuk halaman pegawai): aktif dulu, lalu urut nama
     */
    public function all(): array
    {
        return $this->query(
            "SELECT * FROM layanan ORDER BY is_active DESC, nama ASC"
        );
    }

    /**
     * Hanya layanan aktif (dipakai halaman publik di Tahap 6)
     */
    public function findActive(): array
    {
        return $this->query(
            "SELECT * FROM layanan WHERE is_active = TRUE ORDER BY nama ASC"
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
     * Tambah layanan baru, mengembalikan ID baru
     *
     * @param array{nama:string, deskripsi:string, harga:string, estimasi_durasi_menit:string, is_active:bool} $data
     */
    public function create(array $data): int
    {
        $row = $this->first(
            "INSERT INTO layanan
                (nama, deskripsi, harga, estimasi_durasi_menit, is_active)
             VALUES (?, ?, ?, ?, ?)
             RETURNING id",
            $this->toParams($data)
        );

        return (int) $row['id'];
    }

    /**
     * Perbarui layanan (updated_at diurus trigger database)
     */
    public function update(int $id, array $data): bool
    {
        return $this->execute(
            "UPDATE layanan
                SET nama = ?,
                    deskripsi = ?,
                    harga = ?,
                    estimasi_durasi_menit = ?,
                    is_active = ?
              WHERE id = ?",
            [...$this->toParams($data), $id]
        );
    }

    /**
     * Hapus layanan.
     * Akan melempar PDOException (SQLSTATE 23503) kalau layanan
     * sudah dipakai pemesanan (FK ON DELETE RESTRICT).
     */
    public function delete(int $id): bool
    {
        return $this->execute(
            "DELETE FROM layanan WHERE id = ?",
            [$id]
        );
    }

    /**
     * Ubah input form menjadi urutan parameter query
     */
    private function toParams(array $data): array
    {
        return [
            $data['nama'],
            $data['deskripsi'] !== '' ? $data['deskripsi'] : null,
            $data['harga'],
            $data['estimasi_durasi_menit'] !== ''
                ? $data['estimasi_durasi_menit']
                : null,
            $data['is_active'] ? 'true' : 'false',
        ];
    }
}