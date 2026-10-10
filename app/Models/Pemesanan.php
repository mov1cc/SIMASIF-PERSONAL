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
     * Semua pemesanan untuk sisi pegawai (dikembangkan lagi di Tahap 8).
     */
    public function all(): array
    {
        return $this->query(
            "SELECT p.*,
                    pl.nama AS pelanggan_nama,
                    l.nama  AS layanan_nama
               FROM pemesanan p
               JOIN pelanggan pl ON pl.id = p.pelanggan_id
               JOIN layanan   l  ON l.id  = p.layanan_id
              ORDER BY p.tanggal_jadwal DESC, p.jam_jadwal DESC, p.id DESC"
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