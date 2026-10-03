CREATE TABLE pemesanan (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    kode_unik VARCHAR(50) NOT NULL UNIQUE,
    pelanggan_id INTEGER NOT NULL REFERENCES pelanggan(id) ON DELETE RESTRICT,
    layanan_id INTEGER NOT NULL REFERENCES layanan(id) ON DELETE RESTRICT,
    pegawai_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    tanggal_jadwal DATE NOT NULL,
    jam_jadwal TIME NOT NULL,
    catatan_khusus TEXT,
    status status_pemesanan NOT NULL DEFAULT 'pemesanan_dibuat',
    harga_saat_pesan NUMERIC(12,2) NOT NULL CHECK (harga_saat_pesan >= 0),
    biaya_tambahan NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (biaya_tambahan >= 0),
    diskon NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (diskon >= 0),
    total_tagihan NUMERIC(12,2)
        GENERATED ALWAYS AS (
            harga_saat_pesan + biaya_tambahan - diskon
        ) STORED,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_kode_unik_not_empty CHECK (kode_unik <> ''),
    CONSTRAINT chk_total_tagihan_non_negative CHECK (total_tagihan >= 0)
);

CREATE INDEX idx_pemesanan_jadwal ON pemesanan (tanggal_jadwal, jam_jadwal);
CREATE INDEX idx_pemesanan_status ON pemesanan (status);
CREATE INDEX idx_pemesanan_pelanggan ON pemesanan (pelanggan_id);
CREATE INDEX idx_pemesanan_pegawai ON pemesanan (pegawai_id);

COMMENT ON TABLE pemesanan IS 'Entitas utama yang menghubungkan pelanggan, layanan, dan jadwal';
COMMENT ON COLUMN pemesanan.kode_unik IS 'Dipakai pelanggan untuk akses tanpa login (tracking, upload, download)';
COMMENT ON COLUMN pemesanan.pegawai_id IS 'Pegawai yang menangani pemesanan ini, boleh kosong di awal';
COMMENT ON COLUMN pemesanan.harga_saat_pesan IS 'Salinan harga dari tabel layanan pada saat booking dibuat, supaya perubahan harga layanan di kemudian hari tidak mengubah nilai pemesanan lama. Nilainya diisi oleh kode PHP saat proses booking (Tahap 7), bukan otomatis oleh database';
COMMENT ON COLUMN pemesanan.biaya_tambahan IS 'Biaya tambahan di luar harga dasar layanan, misalnya cetak foto ekstra, defaultnya 0';
COMMENT ON COLUMN pemesanan.diskon IS 'Potongan harga jika ada, defaultnya 0';
COMMENT ON COLUMN pemesanan.total_tagihan IS 'Dihitung otomatis oleh database dari harga_saat_pesan + biaya_tambahan - diskon, tidak bisa diisi manual lewat INSERT atau UPDATE';
COMMENT ON CONSTRAINT chk_total_tagihan_non_negative ON pemesanan IS 'Berjaga-jaga terhadap kasus diskon lebih besar dari harga plus biaya tambahan, yang akan membuat total_tagihan menjadi negatif';