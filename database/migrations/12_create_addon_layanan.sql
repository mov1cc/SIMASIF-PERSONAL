CREATE TABLE addon_layanan (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    layanan_id INTEGER NOT NULL REFERENCES layanan(id) ON DELETE CASCADE,
    nama VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    harga NUMERIC(12,2) NOT NULL,
    tambahan_durasi_menit INTEGER NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT addon_layanan_nama_check CHECK (nama <> ''),
    CONSTRAINT addon_layanan_harga_check CHECK (harga >= 0),
    CONSTRAINT addon_layanan_durasi_check CHECK (tambahan_durasi_menit >= 0),
    CONSTRAINT uq_addon_layanan_nama UNIQUE (layanan_id, nama)
);

CREATE INDEX idx_addon_layanan_layanan ON addon_layanan (layanan_id);

CREATE TRIGGER set_updated_at_addon_layanan
BEFORE UPDATE ON addon_layanan
FOR EACH ROW
EXECUTE FUNCTION trigger_set_updated_at();

COMMENT ON TABLE addon_layanan IS 'Opsi tambahan berbayar milik satu layanan (mis. tambah orang, cetak ekstra)';
COMMENT ON COLUMN addon_layanan.tambahan_durasi_menit IS 'Tambahan durasi sesi jika add-on dipilih, 0 = tidak menambah durasi';
COMMENT ON COLUMN addon_layanan.is_active IS 'Add-on nonaktif tidak bisa dipilih pelanggan baru';