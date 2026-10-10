<?php

namespace App\Models;

use App\Core\Model;

class Pemesanan extends Model
{
    protected string $table = 'pemesanan';

    /**
     * Cek apakah kode unik sudah dipakai.
     */
    public function kodeExists(string $kode): bool
    {
        return $this->first(
            "SELECT 1 AS ada FROM pemesanan WHERE kode_unik = ? LIMIT 1",
            [$kode]
        ) !== null;
    }

    /**
     * Cari pemesanan + data pelanggan & layanan berdasarkan kode unik.
     */
    public function findByKodeUnik(string $kode): ?array
    {
        return $this->first(
            "SELECT p.*,
                    pl.nama  AS pelanggan_nama,
                    pl.no_hp AS pelanggan_no_hp,
                    l.nama   AS layanan_nama,
                    l.estimasi_durasi_menit
               FROM pemesanan p
               JOIN pelanggan pl ON pl.id = p.pelanggan_id
               JOIN layanan   l  ON l.id  = p.layanan_id
              WHERE p.kode_unik = ?
              LIMIT 1",
            [$kode]
        );
    }

        /**
     * Daftar pemesanan untuk pegawai, dengan pencarian & filter.
     *
     * @param array{q?:string, status?:string, dari?:string, sampai?:string} $f
     *        status harus sudah divalidasi (salah satu nilai enum),
     *        dari/sampai berformat Y-m-d atau kosong.
     */
    public function all(array $f = []): array
    {
        $sql = "SELECT p.*,
                       pl.nama  AS pelanggan_nama,
                       pl.no_hp AS pelanggan_no_hp,
                       l.nama   AS layanan_nama,
                       t.status_pembayaran
                  FROM pemesanan p
                  JOIN pelanggan pl ON pl.id = p.pelanggan_id
                  JOIN layanan   l  ON l.id  = p.layanan_id
             LEFT JOIN transaksi_pembayaran t ON t.pemesanan_id = p.id
                 WHERE 1 = 1";
        $params = [];

        if (!empty($f['q'])) {
            $like    = '%' . addcslashes($f['q'], '%_\\') . '%';
            $sql    .= " AND (p.kode_unik ILIKE ? OR pl.nama ILIKE ? OR pl.no_hp LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($f['status'])) {
            $sql    .= " AND p.status = CAST(? AS status_pemesanan)";
            $params[] = $f['status'];
        }

        if (!empty($f['dari'])) {
            $sql    .= " AND p.tanggal_jadwal >= CAST(? AS DATE)";
            $params[] = $f['dari'];
        }

        if (!empty($f['sampai'])) {
            $sql    .= " AND p.tanggal_jadwal <= CAST(? AS DATE)";
            $params[] = $f['sampai'];
        }

        $sql .= " ORDER BY p.tanggal_jadwal DESC, p.jam_jadwal DESC, p.id DESC";

        return $this->query($sql, $params);
    }

    /**
     * Detail satu pemesanan (+ pelanggan, layanan, pegawai penangan, pembayaran).
     */
    public function findDetail(int $id): ?array
    {
        return $this->first(
            "SELECT p.*,
                    pl.nama  AS pelanggan_nama,
                    pl.no_hp AS pelanggan_no_hp,
                    l.nama   AS layanan_nama,
                    l.estimasi_durasi_menit,
                    u.nama   AS pegawai_nama,
                    t.metode_pembayaran,
                    t.jumlah_bayar,
                    t.status_pembayaran,
                    t.diverifikasi_at
               FROM pemesanan p
               JOIN pelanggan pl ON pl.id = p.pelanggan_id
               JOIN layanan   l  ON l.id  = p.layanan_id
          LEFT JOIN users u      ON u.id   = p.pegawai_id
          LEFT JOIN transaksi_pembayaran t ON t.pemesanan_id = p.id
              WHERE p.id = ?
              LIMIT 1",
            [$id]
        );
    }

    /**
     * Riwayat perubahan status (terbaru di atas).
     */
    public function riwayatWorkflow(int $pemesananId): array
    {
        return $this->query(
            "SELECT w.status_sebelumnya, w.status, w.catatan, w.created_at,
                    u.nama AS diubah_oleh_nama
               FROM workflow_layanan w
          LEFT JOIN users u ON u.id = w.diubah_oleh
              WHERE w.pemesanan_id = ?
              ORDER BY w.created_at DESC, w.id DESC",
            [$pemesananId]
        );
    }

    /**
     * Pemesanan dalam rentang tanggal untuk halaman jadwal.
     * Durasi kosong dianggap 60 menit (sama dengan aturan adaBentrok()).
     *
     * @param string $dari   Y-m-d
     * @param string $sampai Y-m-d
     */
    public function jadwalRentang(string $dari, string $sampai): array
    {
        return $this->query(
            "SELECT p.id, p.kode_unik, p.tanggal_jadwal, p.jam_jadwal, p.status,
                    pl.nama AS pelanggan_nama,
                    l.nama  AS layanan_nama,
                    COALESCE(l.estimasi_durasi_menit, 60) AS durasi_menit,
                    u.nama  AS pegawai_nama
               FROM pemesanan p
               JOIN pelanggan pl ON pl.id = p.pelanggan_id
               JOIN layanan   l  ON l.id  = p.layanan_id
          LEFT JOIN users u      ON u.id   = p.pegawai_id
              WHERE p.tanggal_jadwal BETWEEN CAST(? AS DATE) AND CAST(? AS DATE)
              ORDER BY p.tanggal_jadwal ASC, p.jam_jadwal ASC, p.id ASC",
            [$dari, $sampai]
        );
    }

    /**
     * Kunci transaksi per tanggal supaya dua booking bersamaan di hari
     * yang sama diproses bergantian (mencegah double booking).
     * Lock otomatis lepas saat COMMIT/ROLLBACK.
     */
    public function kunciTanggal(string $tanggal): void
    {
        $this->execute(
            "SELECT pg_advisory_xact_lock(hashtext(?))",
            [$tanggal]
        );
    }

    /**
     * Apakah rentang waktu [mulai, selesai) bertabrakan dengan pemesanan lain
     * di tanggal yang sama?
     *
     * Rentang pemesanan lama = jam_jadwal sampai jam_jadwal + durasi layanannya
     * (durasi kosong dianggap 60 menit). Booking yang berurutan rapat
     * (selesai 10:00, mulai 10:00) TIDAK dianggap bentrok.
     * Status 'dibatalkan' (jika nanti ditambahkan ke enum) diabaikan.
     *
     * @param string $tanggal Y-m-d
     * @param string $mulai   H:i:s
     * @param string $selesai H:i:s
     */
    public function adaBentrok(string $tanggal, string $mulai, string $selesai): bool
    {
        return $this->first(
            "SELECT p.id
               FROM pemesanan p
               JOIN layanan l ON l.id = p.layanan_id
              WHERE p.tanggal_jadwal = CAST(? AS DATE)
                AND p.status::text <> 'dibatalkan'
                AND p.jam_jadwal < CAST(? AS TIME)
                AND (p.jam_jadwal
                     + COALESCE(l.estimasi_durasi_menit, 60) * INTERVAL '1 minute')
                    > CAST(? AS TIME)
              LIMIT 1",
            [$tanggal, $selesai, $mulai]
        ) !== null;
    }

    /**
     * Simpan pemesanan baru. total_tagihan dihitung otomatis oleh database.
     *
     * @param array{kode_unik:string, pelanggan_id:int, layanan_id:int,
     *              tanggal:string, jam:string, catatan:?string, harga:string} $d
     * @return array{id:int, kode_unik:string}
     */
    public function insert(array $d): array
    {
        $row = $this->first(
            "INSERT INTO pemesanan
                (kode_unik, pelanggan_id, layanan_id, tanggal_jadwal,
                 jam_jadwal, catatan_khusus, harga_saat_pesan)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             RETURNING id, kode_unik",
            [
                $d['kode_unik'],
                $d['pelanggan_id'],
                $d['layanan_id'],
                $d['tanggal'],
                $d['jam'],
                $d['catatan'],
                $d['harga'],
            ]
        );

        return [
            'id'        => (int) $row['id'],
            'kode_unik' => $row['kode_unik'],
        ];
    }

    /**
     * Entri pertama riwayat workflow (status_sebelumnya = NULL,
     * diubah_oleh = NULL karena dibuat oleh pelanggan).
     */
    public function catatWorkflowAwal(int $pemesananId): void
    {
        $this->execute(
            "INSERT INTO workflow_layanan
                (pemesanan_id, status_sebelumnya, status, catatan, diubah_oleh)
             VALUES (?, NULL, 'pemesanan_dibuat', ?, NULL)",
            [$pemesananId, 'Pemesanan dibuat oleh pelanggan']
        );
    }
}