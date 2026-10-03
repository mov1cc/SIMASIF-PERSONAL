CREATE OR REPLACE FUNCTION trigger_set_updated_at()

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
EXECUTE FUNCTION trigger_set_updated_at();