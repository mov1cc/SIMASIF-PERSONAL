CREATE TABLE pemesanan_addon (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    pemesanan_id INTEGER NOT NULL REFERENCES pemesanan(id) ON DELETE CASCADE,
    addon_id INTEGER NOT NULL REFERENCES addon_layanan(id) ON DELETE RESTRICT,
    nama_saat_pesan VARCHAR(150) NOT NULL,
    harga_saat_pesan NUMERIC(12,2) NOT NULL CHECK (harga_saat_pesan >= 0),
    jumlah INTEGER NOT NULL DEFAULT 1 CHECK (jumlah > 0),
    subtotal NUMERIC(12,2)
        GENERATED ALWAYS AS (harga_saat_pesan * jumlah) STORED,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT uq_pemesanan_addon UNIQUE (pemesanan_id, addon_id)
);

CREATE INDEX idx_pemesanan_addon_addon ON pemesanan_addon (addon_id);

COMMENT ON TABLE pemesanan_addon IS 'Add-on yang dipilih pada suatu pemesanan (snapshot nama dan harga saat dipesan)';
COMMENT ON COLUMN pemesanan_addon.harga_saat_pesan IS 'Salinan harga add-on saat booking, diisi kode PHP, supaya perubahan harga tidak mengubah pemesanan lama';
COMMENT ON COLUMN pemesanan_addon.subtotal IS 'Dihitung otomatis: harga_saat_pesan x jumlah';

-- Jaga pemesanan.biaya_tambahan = total semua add-on, sehingga
-- total_tagihan (generated) otomatis ikut benar.
CREATE OR REPLACE FUNCTION trigger_sync_biaya_tambahan()
RETURNS TRIGGER AS $$
DECLARE
    v_pemesanan_id INTEGER;
BEGIN
    IF TG_OP = 'DELETE' THEN
        v_pemesanan_id := OLD.pemesanan_id;
    ELSE
        v_pemesanan_id := NEW.pemesanan_id;
    END IF;

    UPDATE pemesanan
       SET biaya_tambahan = (
            SELECT COALESCE(SUM(subtotal), 0)
              FROM pemesanan_addon
             WHERE pemesanan_id = v_pemesanan_id
       )
     WHERE id = v_pemesanan_id;

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER sync_biaya_tambahan
AFTER INSERT OR UPDATE OR DELETE ON pemesanan_addon
FOR EACH ROW
EXECUTE FUNCTION trigger_sync_biaya_tambahan();