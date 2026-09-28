CREATE TABLE file_fotografi (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    pemesanan_id INTEGER NOT NULL
        REFERENCES pemesanan(id) ON DELETE CASCADE,
    jenis_file jenis_file_fotografi NOT NULL,
    nama_file_asli VARCHAR(255) NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    path_file VARCHAR(255) NOT NULL,
    ukuran_file BIGINT CHECK (ukuran_file > 0),
    mime_type VARCHAR(100),
    status_file status_file_fotografi NOT NULL DEFAULT 'belum_tersedia',
    diunggah_oleh INTEGER
        REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_nama_file_asli_not_empty
        CHECK (nama_file_asli <> ''),
    CONSTRAINT chk_nama_file_not_empty
        CHECK (nama_file <> ''),
    CONSTRAINT chk_path_file_not_empty
        CHECK (path_file <> ''),
    CONSTRAINT chk_pengunggah_file CHECK (
        (jenis_file = 'referensi'
            AND diunggah_oleh IS NULL)
        OR
        (jenis_file = 'hasil'
            AND diunggah_oleh IS NOT NULL)
    )
);

CREATE INDEX idx_file_pemesanan ON file_fotografi (pemesanan_id);
CREATE INDEX idx_file_jenis ON file_fotografi (jenis_file);
CREATE INDEX idx_file_pengunggah ON file_fotografi (diunggah_oleh);

COMMENT ON TABLE file_fotografi IS 'File referensi dari pelanggan dan hasil dari pegawai';
COMMENT ON COLUMN file_fotografi.nama_file_asli IS 'Nama file asli sebagaimana diunggah pengguna, misalnya IMG_12345.jpg, dipakai saat file diunduh kembali supaya pengguna melihat nama yang familiar';
COMMENT ON COLUMN file_fotografi.nama_file IS 'Nama file sebagaimana disimpan di server, biasanya sudah diacak untuk mencegah tabrakan nama dan path traversal, misalnya A92KLS83.jpg';
COMMENT ON COLUMN file_fotografi.path_file IS 'Path relatif di storage/upload-pelanggan/ atau storage/hasil-foto/, tidak bisa diakses langsung lewat URL';
COMMENT ON COLUMN file_fotografi.ukuran_file IS 'Ukuran file dalam satuan byte, dipakai untuk validasi dan ditampilkan ke pengguna';
COMMENT ON COLUMN file_fotografi.mime_type IS 'Tipe MIME file, misalnya image/jpeg, image/png, atau application/zip, dipakai saat proses download supaya header Content-Type benar';
COMMENT ON COLUMN file_fotografi.diunggah_oleh IS 'Kosong (NULL) kalau file diunggah pelanggan, berisi id pegawai kalau jenis_file = hasil';