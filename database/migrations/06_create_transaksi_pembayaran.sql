CREATE TABLE transaksi_pembayaran (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    pemesanan_id INTEGER NOT NULL UNIQUE
        REFERENCES pemesanan(id) ON DELETE CASCADE,
    metode_pembayaran metode_pembayaran NOT NULL,
    jumlah_bayar NUMERIC(12,2) NOT NULL
        CHECK (jumlah_bayar > 0),
    nomor_referensi VARCHAR(100),
    bukti_transfer VARCHAR(255),
    status_pembayaran status_pembayaran NOT NULL
        DEFAULT 'menunggu_verifikasi',
    diverifikasi_oleh INTEGER
        REFERENCES users(id) ON DELETE SET NULL,
    diverifikasi_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_bukti_transfer_wajib_untuk_transfer CHECK (
        (metode_pembayaran = 'transfer'
            AND bukti_transfer IS NOT NULL)
        OR
        (metode_pembayaran = 'langsung')
    ),
    CONSTRAINT chk_verifikasi_pembayaran_transfer CHECK (
        status_pembayaran = 'menunggu_verifikasi'
        OR
        (
            status_pembayaran IN ('lunas','ditolak')
            AND diverifikasi_oleh IS NOT NULL
        )
    )
);


CREATE INDEX idx_transaksi_pembayaran_status
ON transaksi_pembayaran (status_pembayaran);
CREATE INDEX idx_transaksi_pembayaran_verifikasi
ON transaksi_pembayaran (diverifikasi_oleh);

COMMENT ON TABLE transaksi_pembayaran IS 'Data pembayaran, satu pemesanan hanya punya satu transaksi';
COMMENT ON COLUMN transaksi_pembayaran.nomor_referensi IS 'Nomor referensi transaksi dari bank atau catatan internal, opsional, dipakai Owner untuk mencocokkan mutasi rekening secara manual';
COMMENT ON COLUMN transaksi_pembayaran.bukti_transfer IS 'Nama file bukti transfer, file fisik disimpan di storage/bukti-transfer/';