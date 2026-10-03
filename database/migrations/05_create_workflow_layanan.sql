CREATE TABLE workflow_layanan (
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
'Waktu perubahan status dicatat ke dalam sistem';