CREATE TABLE layanan_gambar (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    layanan_id INTEGER NOT NULL REFERENCES layanan(id) ON DELETE CASCADE,
    nama_file_asli VARCHAR(255) NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    path_file VARCHAR(255) NOT NULL,
    ukuran_file BIGINT CHECK (ukuran_file > 0),
    mime_type VARCHAR(100),
    keterangan VARCHAR(150),
    urutan INTEGER NOT NULL DEFAULT 0,
    is_cover BOOLEAN NOT NULL DEFAULT FALSE,
    diunggah_oleh INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_layanan_gambar_nama_asli_not_empty CHECK (nama_file_asli <> ''),
    CONSTRAINT chk_layanan_gambar_nama_not_empty CHECK (nama_file <> ''),
    CONSTRAINT chk_layanan_gambar_path_not_empty CHECK (path_file <> '')
);

CREATE INDEX idx_layanan_gambar_layanan ON layanan_gambar (layanan_id, urutan);

-- Maksimal satu gambar cover per layanan
CREATE UNIQUE INDEX uq_layanan_gambar_cover
    ON layanan_gambar (layanan_id) WHERE is_cover;

COMMENT ON TABLE layanan_gambar IS 'Galeri/contoh foto untuk satu layanan, ditampilkan di halaman publik';
COMMENT ON COLUMN layanan_gambar.path_file IS 'Path relatif gambar. Berbeda dengan file pelanggan, gambar layanan boleh publik (mis. public/assets/uploads/layanan/)';
COMMENT ON COLUMN layanan_gambar.urutan IS 'Urutan tampil, angka kecil tampil lebih dulu';
COMMENT ON COLUMN layanan_gambar.is_cover IS 'Gambar utama yang tampil di kartu layanan';