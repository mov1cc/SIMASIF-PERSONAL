CREATE TABLE layanan (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    harga NUMERIC(12,2) NOT NULL,
    estimasi_durasi_menit INTEGER,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT layanan_nama_check CHECK (nama <> ''),
    CONSTRAINT layanan_harga_check CHECK (harga >= 0),
    CONSTRAINT layanan_durasi_check CHECK (estimasi_durasi_menit > 0)
);

COMMENT ON TABLE layanan IS 'Paket/layanan fotografi yang ditawarkan Studio Flamboyan';
COMMENT ON COLUMN layanan.is_active IS 'Layanan nonaktif tidak ditampilkan di halaman publik';
COMMENT ON COLUMN layanan.harga IS 'Harga paket fotografi dalam Rupiah';
COMMENT ON COLUMN layanan.estimasi_durasi_menit IS 'Perkiraan lama sesi fotografi dalam satuan menit';
COMMENT ON COLUMN layanan.deskripsi IS 'Informasi detail paket fotografi';
COMMENT ON COLUMN layanan.nama IS 'Nama paket fotografi yang ditawarkan kepada pelanggan';