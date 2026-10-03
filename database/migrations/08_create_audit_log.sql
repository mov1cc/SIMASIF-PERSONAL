CREATE TABLE audit_log (
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
COMMENT ON COLUMN audit_log.created_at IS 'Waktu aktivitas dicatat';