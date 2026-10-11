CREATE OR REPLACE VIEW v_detail_pemesanan AS
SELECT
    p.id                                   AS pemesanan_id,
    p.kode_unik,
    p.status,
    p.tanggal_jadwal,
    p.jam_jadwal,
    (p.jam_jadwal
        + COALESCE(l.estimasi_durasi_menit, 60) * INTERVAL '1 minute'
    )::time                                AS jam_selesai,
    p.catatan_khusus,

    p.harga_saat_pesan,
    p.biaya_tambahan,
    p.diskon,
    p.total_tagihan,

    pl.id                                  AS pelanggan_id,
    pl.nama                                AS pelanggan_nama,
    pl.no_hp                               AS pelanggan_no_hp,

    l.id                                   AS layanan_id,
    l.nama                                 AS layanan_nama,
    l.estimasi_durasi_menit,

    u.id                                   AS pegawai_id,
    u.nama                                 AS pegawai_nama,

    t.id                                   AS transaksi_id,
    t.metode_pembayaran,
    t.jumlah_bayar,
    t.status_pembayaran,
    t.diverifikasi_at,

    p.created_at,
    p.updated_at
FROM pemesanan p
JOIN pelanggan pl                 ON pl.id = p.pelanggan_id
JOIN layanan   l                  ON l.id  = p.layanan_id
LEFT JOIN users u                 ON u.id  = p.pegawai_id
LEFT JOIN transaksi_pembayaran t  ON t.pemesanan_id = p.id;

COMMENT ON VIEW v_detail_pemesanan IS
'Detail lengkap pemesanan (pelanggan, layanan, pegawai, ringkasan pembayaran). Dipakai halaman daftar/detail pemesanan pegawai dan tracking.';



CREATE OR REPLACE VIEW v_detail_pembayaran AS
SELECT
    t.id                                   AS transaksi_id,
    t.pemesanan_id,
    p.kode_unik,
    p.status                               AS status_pemesanan,

    pl.nama                                AS pelanggan_nama,
    pl.no_hp                               AS pelanggan_no_hp,
    l.nama                                 AS layanan_nama,

    p.total_tagihan,
    t.metode_pembayaran,
    t.jumlah_bayar,
    (t.jumlah_bayar - p.total_tagihan)     AS selisih_bayar,  -- 0 = pas, <0 kurang, >0 lebih
    t.nomor_referensi,
    t.bukti_transfer,

    t.status_pembayaran,
    t.diverifikasi_oleh,
    v.nama                                 AS diverifikator_nama,
    t.diverifikasi_at,

    t.created_at,
    t.updated_at
FROM transaksi_pembayaran t
JOIN pemesanan p       ON p.id  = t.pemesanan_id
JOIN pelanggan pl      ON pl.id = p.pelanggan_id
JOIN layanan   l       ON l.id  = p.layanan_id
LEFT JOIN users v      ON v.id  = t.diverifikasi_oleh;

COMMENT ON VIEW v_detail_pembayaran IS
'Detail transaksi pembayaran beserta pemesanan, pelanggan, dan verifikator. Dipakai halaman verifikasi transaksi pegawai dan laporan pendapatan.';



CREATE OR REPLACE VIEW v_riwayat_workflow AS
SELECT
    w.id                                   AS workflow_id,
    w.pemesanan_id,
    p.kode_unik,
    pl.nama                                AS pelanggan_nama,

    ROW_NUMBER() OVER (
        PARTITION BY w.pemesanan_id
        ORDER BY w.created_at, w.id
    )                                      AS urutan_ke,

    w.status_sebelumnya,
    w.status,
    w.catatan,

    w.diubah_oleh,
    u.nama                                 AS diubah_oleh_nama,
    u.role                                 AS diubah_oleh_role,

    w.created_at
FROM workflow_layanan w
JOIN pemesanan p       ON p.id  = w.pemesanan_id
JOIN pelanggan pl      ON pl.id = p.pelanggan_id
LEFT JOIN users u      ON u.id  = w.diubah_oleh;

COMMENT ON VIEW v_riwayat_workflow IS
'Riwayat perubahan status pemesanan. diubah_oleh NULL = dibuat pelanggan/sistem. Dipakai detail pemesanan, tracking pelanggan, dan audit.';