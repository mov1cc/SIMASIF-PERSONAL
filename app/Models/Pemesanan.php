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
 * Dipakai halaman konfirmasi (dan tracking di Tahap 9).
 */
public function findByKodeUnik(string $kode): ?array
{
    return $this->first(
        "SELECT *, pemesanan_id AS id
           FROM v_detail_pemesanan
          WHERE kode_unik = ?
          LIMIT 1",
        [$kode]
    );
}

/**
 * Daftar pemesanan untuk pegawai, dengan pencarian & filter.
 *
 * @param array{q?:string, status?:string, dari?:string, sampai?:string} $f
 */
public function all(array $f = []): array
{
    $sql = "SELECT v.pemesanan_id AS id, v.kode_unik, v.status,
                   v.tanggal_jadwal, v.jam_jadwal, v.total_tagihan,
                   v.pelanggan_nama, v.pelanggan_no_hp, v.layanan_nama,
                   v.status_pembayaran, v.created_at
              FROM v_detail_pemesanan v
             WHERE 1 = 1";
    $params = [];

    if (!empty($f['q'])) {
        $like     = '%' . addcslashes($f['q'], '%_\\') . '%';
        $sql     .= " AND (v.kode_unik ILIKE ? OR v.pelanggan_nama ILIKE ? OR v.pelanggan_no_hp LIKE ?)";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if (!empty($f['status'])) {
        $sql     .= " AND v.status = CAST(? AS status_pemesanan)";
        $params[] = $f['status'];
    }

    if (!empty($f['dari'])) {
        $sql     .= " AND v.tanggal_jadwal >= CAST(? AS DATE)";
        $params[] = $f['dari'];
    }

    if (!empty($f['sampai'])) {
        $sql     .= " AND v.tanggal_jadwal <= CAST(? AS DATE)";
        $params[] = $f['sampai'];
    }

    $sql .= " ORDER BY v.tanggal_jadwal DESC, v.jam_jadwal DESC, v.pemesanan_id DESC";

    return $this->query($sql, $params);
}

/**
 * Detail satu pemesanan (+ pelanggan, layanan, pegawai penangan, pembayaran).
 */
public function findDetail(int $id): ?array
{
    return $this->first(
        "SELECT *, pemesanan_id AS id
           FROM v_detail_pemesanan
          WHERE pemesanan_id = ?
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
        "SELECT status_sebelumnya, status, catatan, created_at, diubah_oleh_nama
           FROM v_riwayat_workflow
          WHERE pemesanan_id = ?
          ORDER BY urutan_ke DESC",
        [$pemesananId]
    );
}

/**
 * Pemesanan dalam rentang tanggal untuk halaman jadwal.
 * Durasi kosong dianggap 60 menit (sama dengan aturan adaBentrok()).
 */
        public function jadwalRentang(string $dari, string $sampai): array
    {
        return $this->query(
            "SELECT pemesanan_id AS id, kode_unik, tanggal_jadwal, jam_jadwal, status,
                    pelanggan_nama, layanan_nama, pegawai_nama,
                    durasi_total_menit AS durasi_menit
               FROM v_detail_pemesanan
              WHERE tanggal_jadwal BETWEEN CAST(? AS DATE) AND CAST(? AS DATE)
              ORDER BY tanggal_jadwal ASC, jam_jadwal ASC, pemesanan_id ASC",
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
                     + (COALESCE(l.estimasi_durasi_menit, 60)
                        + COALESCE((
                            SELECT SUM(a.tambahan_durasi_menit * pa.jumlah)
                              FROM pemesanan_addon pa
                              JOIN addon_layanan a ON a.id = pa.addon_id
                             WHERE pa.pemesanan_id = p.id
                          ), 0)
                       )::int * INTERVAL '1 minute')
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

        /**
     * Simpan satu add-on pada pemesanan (snapshot nama & harga).
     * biaya_tambahan pemesanan diperbarui otomatis oleh trigger database.
     */
    public function insertAddon(int $pemesananId, array $addon, int $jumlah = 1): void
    {
        $this->execute(
            "INSERT INTO pemesanan_addon
                (pemesanan_id, addon_id, nama_saat_pesan, harga_saat_pesan, jumlah)
             VALUES (?, ?, ?, ?, ?)",
            [
                $pemesananId,
                (int) $addon['id'],
                $addon['nama'],
                (string) $addon['harga'],
                $jumlah,
            ]
        );
    }

    /**
     * Add-on yang dipilih pada satu pemesanan.
     */
    public function addonDipesan(int $pemesananId): array
    {
        return $this->query(
            "SELECT addon_id, nama_saat_pesan, harga_saat_pesan, jumlah, subtotal
               FROM pemesanan_addon
              WHERE pemesanan_id = ?
              ORDER BY id ASC",
            [$pemesananId]
        );
    }
}