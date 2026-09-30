CREATE TYPE user_role AS ENUM (
    'owner',
    'pegawai'
);


CREATE TYPE status_pemesanan AS ENUM (
    'pemesanan_dibuat',
    'menunggu_pembayaran',
    'pembayaran_diverifikasi',
    'terjadwal',
    'sesi_foto',
    'proses_editing',
    'hasil_siap',
    'selesai'
);


CREATE TYPE status_pembayaran AS ENUM (
    'menunggu_verifikasi',
    'lunas',
    'ditolak'
);


CREATE TYPE metode_pembayaran AS ENUM (
    'transfer',
    'langsung'
);


CREATE TYPE jenis_file_fotografi AS ENUM (
    'referensi',
    'hasil'
);


CREATE TYPE status_file_fotografi AS ENUM (
    'belum_tersedia',
    'tersedia'
);CREATE TABLE users (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_telepon VARCHAR(20),
    role user_role NOT NULL DEFAULT 'pegawai',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    last_login_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    reset_token VARCHAR(255),
    reset_token_expires_at TIMESTAMP,
    CONSTRAINT users_email_check CHECK (email <> '')
);

COMMENT ON TABLE users IS 'Akun internal Owner dan Pegawai Studio Flamboyan';
COMMENT ON COLUMN users.password IS 'Disimpan dalam bentuk hash bcrypt, tidak pernah plaintext';
COMMENT ON COLUMN users.no_telepon IS 'Informasi kontak';
COMMENT ON COLUMN users.last_login_at IS 'Wkatu login terakhir, diperbarui oleh AuthService setiap kali login berhasil, dipakai Owner untuk memantau aktivitas pegawai';
COMMENT ON COLUMN users.reset_token IS 'Token sementara untuk fitur lupa password, dipakai di tahap 13';
COMMENT ON COLUMN users.reset_token_expires_at IS 'Batas waktu berlaku reset_token, dipakai di Tahap 13';
COMMENT ON CONSTRAINT users_email_check ON users IS 'Mencegah string kosong lolos sebagai email, UNIQUE saja tidak menjamin ini karena string kosong dianggap satu nilai valid yang berbeda dari NULL';
CREATE TABLE pelanggan (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE pelanggan IS 'Data pelanggan yang melakukan pemesanan, diidentifikasi lewat nomor HP unik';CREATE TABLE layanan (
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
COMMENT ON COLUMN layanan.nama IS 'Nama paket fotografi yang ditawarkan kepada pelanggan';CREATE TABLE pemesanan (
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
COMMENT ON CONSTRAINT chk_total_tagihan_non_negative ON pemesanan IS 'Berjaga-jaga terhadap kasus diskon lebih besar dari harga plus biaya tambahan, yang akan membuat total_tagihan menjadi negatif';CREATE TABLE workflow_layanan (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    pemesanan_id INTEGER NOT NULL REFERENCES pemesanan(id) ON DELETE CASCADE,
    status_sebelumnya status_pemesanan,
    status status_pemesanan NOT NULL,
    catatan TEXT,
    diubah_oleh INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_workflow_pemesanan
ON workflow_layanan (pemesanan_id);
CREATE INDEX idx_workflow_created_at
ON workflow_layanan (created_at);
CREATE INDEX idx_workflow_diubah_oleh
ON workflow_layanan (diubah_oleh);

COMMENT ON TABLE workflow_layanan IS
'Riwayat setiap perubahan status pemesanan, bersifat log historis';
COMMENT ON COLUMN workflow_layanan.status_sebelumnya IS
'Status sebelum perubahan ini terjadi, kosong (NULL) untuk entri pertama karena belum ada status sebelumnya';
COMMENT ON COLUMN workflow_layanan.status IS
'Status baru setelah perubahan dilakukan';
COMMENT ON COLUMN workflow_layanan.catatan IS
'Catatan tambahan terkait perubahan status, bersifat opsional';
COMMENT ON COLUMN workflow_layanan.diubah_oleh IS
'User (Owner atau Pegawai) yang melakukan perubahan status';
COMMENT ON COLUMN workflow_layanan.created_at IS
'Waktu perubahan status dicatat ke dalam sistem';CREATE TABLE transaksi_pembayaran (
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
COMMENT ON COLUMN transaksi_pembayaran.bukti_transfer IS 'Nama file bukti transfer, file fisik disimpan di storage/bukti-transfer/';CREATE TABLE file_fotografi (
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
COMMENT ON COLUMN file_fotografi.diunggah_oleh IS 'Kosong (NULL) kalau file diunggah pelanggan, berisi id pegawai kalau jenis_file = hasil';CREATE TABLE audit_log (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id INTEGER
        REFERENCES users(id) ON DELETE SET NULL,
    modul VARCHAR(50) NOT NULL,
    aktivitas VARCHAR(150) NOT NULL,
    detail JSONB,
    ip_address VARCHAR(45),
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_audit_modul_not_empty
        CHECK (modul <> ''),
    CONSTRAINT chk_audit_aktivitas_not_empty
        CHECK (aktivitas <> '')
);

CREATE INDEX idx_audit_user ON audit_log (user_id);
CREATE INDEX idx_audit_created ON audit_log (created_at);
CREATE INDEX idx_audit_modul ON audit_log (modul);

COMMENT ON TABLE audit_log IS 'Pencatatan aktivitas pengguna internal untuk keamanan dan transparansi';
COMMENT ON COLUMN audit_log.user_id IS 'User yang melakukan aktivitas, NULL apabila akun sudah dihapus';
COMMENT ON COLUMN audit_log.modul IS 'Kategori modul terkait aktivitas, misalnya Pemesanan, Pembayaran, Workflow, Layanan, Pelanggan, atau Auth, dipakai untuk memudahkan filter riwayat di halaman Owner';
COMMENT ON COLUMN audit_log.aktivitas IS 'Deskripsi singkat aktivitas yang dilakukan pengguna';
COMMENT ON COLUMN audit_log.detail IS 'Informasi tambahan dalam format JSON, misalnya data sebelum dan sesudah perubahan atau metadata aktivitas';
COMMENT ON COLUMN audit_log.created_at IS 'Waktu aktivitas dicatat';CREATE OR REPLACE FUNCTION trigger_set_updated_at()

RETURNS TRIGGER AS $$

BEGIN

    NEW.updated_at = NOW();

    RETURN NEW;

END;

$$ LANGUAGE plpgsql;


COMMENT ON FUNCTION trigger_set_updated_at() IS
'Mengubah nilai kolom updated_at menjadi waktu saat ini sebelum UPDATE dilakukan.';


CREATE TRIGGER set_updated_at_users
BEFORE UPDATE ON users
FOR EACH ROW
EXECUTE FUNCTION trigger_set_updated_at();


CREATE TRIGGER set_updated_at_pelanggan
BEFORE UPDATE ON pelanggan
FOR EACH ROW
EXECUTE FUNCTION trigger_set_updated_at();


CREATE TRIGGER set_updated_at_layanan
BEFORE UPDATE ON layanan
FOR EACH ROW
EXECUTE FUNCTION trigger_set_updated_at();


CREATE TRIGGER set_updated_at_pemesanan
BEFORE UPDATE ON pemesanan
FOR EACH ROW
EXECUTE FUNCTION trigger_set_updated_at();


CREATE TRIGGER set_updated_at_transaksi_pembayaran
BEFORE UPDATE ON transaksi_pembayaran
FOR EACH ROW
EXECUTE FUNCTION trigger_set_updated_at();INSERT INTO users (nama, email, password, role, is_active)
VALUES
(
    'Sitta Laila Safitri',
    'owner@simasif.local',
    '$2y$10$ZYEeDivntmdU5KBb73mtvuLV9Wqmh/n.OoRcbZ3PHQRgguVxBwhxi',
    'owner',
    TRUE
),
(
    'Pegawai Demo',
    'pegawai@simasif.local',
    '$2y$10$fo54Bh34yln4dq9CPeMQwOK6Kqx3jzbvMJW50Gzg0XbU.eTGgBzO6',
    'pegawai',
    TRUE
);INSERT INTO layanan (nama, deskripsi, harga, estimasi_durasi_menit, is_active) VALUES
('Paket Foto Wisuda', 'Sesi foto wisuda dengan 1 lokasi indoor/outdoor, termasuk 10 hasil edit terbaik', 250000, 60, TRUE),
('Paket Foto Keluarga', 'Sesi foto keluarga di studio, termasuk 15 hasil edit dan 1 cetak ukuran 10R', 350000, 90, TRUE),
('Paket Foto Produk', 'Sesi foto produk untuk katalog/marketplace, termasuk 20 hasil edit', 200000, 45, TRUE),
('Paket Prewedding', 'Sesi foto prewedding 2 lokasi, termasuk 30 hasil edit dan album cetak', 1500000, 180, TRUE),
('Paket Foto Formal', 'Sesi foto formal untuk keperluan dokumen/CV, termasuk 5 hasil edit', 100000, 30, TRUE),
('Paket Foto Kelulusan Lama', 'Paket lama yang sudah tidak dijual', 175000, 45, FALSE);