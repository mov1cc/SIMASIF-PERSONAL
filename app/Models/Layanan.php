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
     * Ubah status aktif/nonaktif saja (dipakai switch di kartu layanan)
     */
    public function setActive(int $id, bool $aktif): bool
    {
        return $this->execute(
            "UPDATE layanan SET is_active = ? WHERE id = ?",
            [$aktif ? 'true' : 'false', $id]
        );
    }

    /**
     * Statistik bulan berjalan untuk kartu di halaman katalog.
     *
     * - top       : layanan aktif dengan sesi dipesan terbanyak (null kalau belum ada pemesanan)
     * - terendah  : layanan aktif dengan sesi dipesan paling sedikit (null kalau tidak ada pembanding)
     * - total_omzet: total tagihan pemesanan berstatus pembayaran 'lunas' bulan ini
     *
     * @return array{top:?array, terendah:?array, total_omzet:float}
     */
    public function statistikBulanIni(): array
    {
        $rows = $this->query(
            "SELECT l.id, l.nama,
                    COUNT(p.id) AS sesi,
                    COALESCE(SUM(
                        CASE WHEN t.status_pembayaran = 'lunas'
                             THEN p.total_tagihan ELSE 0 END
                    ), 0) AS omzet
               FROM layanan l
               LEFT JOIN pemesanan p
                      ON p.layanan_id = l.id
                     AND p.created_at >= date_trunc('month', NOW())
                     AND p.created_at <  date_trunc('month', NOW()) + INTERVAL '1 month'
               LEFT JOIN transaksi_pembayaran t ON t.pemesanan_id = p.id
              WHERE l.is_active = TRUE
              GROUP BY l.id, l.nama
              ORDER BY sesi DESC, omzet DESC, l.nama ASC"
        );

        $totalRow = $this->first(
            "SELECT COALESCE(SUM(p.total_tagihan), 0) AS total
               FROM pemesanan p
               JOIN transaksi_pembayaran t ON t.pemesanan_id = p.id
              WHERE t.status_pembayaran = 'lunas'
                AND p.created_at >= date_trunc('month', NOW())
                AND p.created_at <  date_trunc('month', NOW()) + INTERVAL '1 month'"
        );

        $rows = array_map(fn (array $r) => [
            'id'    => (int) $r['id'],
            'nama'  => $r['nama'],
            'sesi'  => (int) $r['sesi'],
            'omzet' => (float) $r['omzet'],
        ], $rows);

        $top      = null;
        $terendah = null;

        if (!empty($rows) && $rows[0]['sesi'] > 0) {
            $top = $rows[0];

            $terakhir = end($rows);
            if ($terakhir['id'] !== $top['id']) {
                $terendah = $terakhir;
            }
        }

        return [
            'top'         => $top,
            'terendah'    => $terendah,
            'total_omzet' => (float) ($totalRow['total'] ?? 0),
        ];
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