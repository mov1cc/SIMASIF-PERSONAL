<?php

namespace App\Models;

use App\Core\Model;
use App\Helpers\FormatHelper;

class LayananGambar extends Model
{
    protected string $table = 'layanan_gambar';

    /** Lokasi relatif terhadap folder public/ (gambar layanan boleh diakses publik) */
    public const DIR_RELATIF = 'assets/uploads/layanan';

    public function find(int $id): ?array
    {
        return parent::find($id);
    }

    /**
     * Semua gambar satu layanan, urut tampil.
     */
    public function allByLayanan(int $layananId): array
    {
        return $this->query(
            "SELECT * FROM layanan_gambar
              WHERE layanan_id = ?
              ORDER BY urutan ASC, id ASC",
            [$layananId]
        );
    }

    public function countByLayanan(int $layananId): int
    {
        $row = $this->first(
            "SELECT COUNT(*) AS n FROM layanan_gambar WHERE layanan_id = ?",
            [$layananId]
        );

        return (int) ($row['n'] ?? 0);
    }

    /**
     * Simpan metadata gambar. Urutan otomatis = urutan terbesar + 1.
     *
     * @param array{layanan_id:int, nama_file_asli:string, nama_file:string,
     *              path_file:string, ukuran_file:int, mime_type:string,
     *              keterangan:?string, is_cover:bool, diunggah_oleh:?int} $d
     */
    public function create(array $d): int
    {
        $row = $this->first(
            "INSERT INTO layanan_gambar
                (layanan_id, nama_file_asli, nama_file, path_file, ukuran_file,
                 mime_type, keterangan, urutan, is_cover, diunggah_oleh)
             VALUES (?, ?, ?, ?, ?, ?, ?,
                     COALESCE((SELECT MAX(urutan) + 1 FROM layanan_gambar WHERE layanan_id = ?), 0),
                     ?, ?)
             RETURNING id",
            [
                $d['layanan_id'],
                $d['nama_file_asli'],
                $d['nama_file'],
                $d['path_file'],
                $d['ukuran_file'],
                $d['mime_type'],
                $d['keterangan'],
                $d['layanan_id'],
                $d['is_cover'] ? 'true' : 'false',
                $d['diunggah_oleh'],
            ]
        );

        return (int) $row['id'];
    }

    public function updateMeta(int $id, ?string $keterangan, int $urutan): bool
    {
        return $this->execute(
            "UPDATE layanan_gambar SET keterangan = ?, urutan = ? WHERE id = ?",
            [$keterangan, $urutan, $id]
        );
    }

    /**
     * Jadikan satu gambar sebagai cover (cover lama otomatis dilepas).
     * Dalam satu transaksi supaya index unik "satu cover per layanan" tidak terlanggar.
     */
    public function setCover(int $layananId, int $id): void
    {
        $this->db->beginTransaction();

        try {
            $this->execute(
                "UPDATE layanan_gambar SET is_cover = FALSE WHERE layanan_id = ?",
                [$layananId]
            );
            $this->execute(
                "UPDATE layanan_gambar SET is_cover = TRUE WHERE id = ? AND layanan_id = ?",
                [$id, $layananId]
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Hapus baris gambar. Kalau yang dihapus adalah cover, gambar pertama
     * yang tersisa otomatis menjadi cover.
     *
     * @return array|null baris yang dihapus (untuk menghapus file fisiknya)
     */
    public function hapus(int $id): ?array
    {
        $row = $this->find($id);

        if ($row === null) {
            return null;
        }

        $this->db->beginTransaction();

        try {
            $this->execute("DELETE FROM layanan_gambar WHERE id = ?", [$id]);

            if (FormatHelper::bool($row['is_cover'])) {
                $this->execute(
                    "UPDATE layanan_gambar SET is_cover = TRUE
                      WHERE id = (SELECT id FROM layanan_gambar
                                   WHERE layanan_id = ?
                                   ORDER BY urutan ASC, id ASC
                                   LIMIT 1)",
                    [(int) $row['layanan_id']]
                );
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }

        return $row;
    }
}