CREATE OR REPLACE VIEW v_detail_pemesanan AS
SELECT
    p.id                                   AS pemesanan_id,
    p.kode_unik,
    p.status,
    p.tanggal_jadwal,
    p.jam_jadwal,
    (p.jam_jadwal
        + (COALESCE(l.estimasi_durasi_menit, 60) + COALESCE(ad.tambahan_durasi, 0))::int
          * INTERVAL '1 minute'
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
    p.updated_at,

    -- kolom baru (harus di akhir)
    (COALESCE(l.estimasi_durasi_menit, 60)
        + COALESCE(ad.tambahan_durasi, 0))::int  AS durasi_total_menit,
    COALESCE(ad.jumlah_addon, 0)::int            AS jumlah_addon
FROM pemesanan p
JOIN pelanggan pl                 ON pl.id = p.pelanggan_id
JOIN layanan   l                  ON l.id  = p.layanan_id
LEFT JOIN users u                 ON u.id  = p.pegawai_id
LEFT JOIN transaksi_pembayaran t  ON t.pemesanan_id = p.id
LEFT JOIN (
    SELECT pa.pemesanan_id,
           SUM(a.tambahan_durasi_menit * pa.jumlah) AS tambahan_durasi,
           COUNT(*)                                 AS jumlah_addon
      FROM pemesanan_addon pa
      JOIN addon_layanan a ON a.id = pa.addon_id
     GROUP BY pa.pemesanan_id
) ad ON ad.pemesanan_id = p.id;