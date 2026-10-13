ALTER TABLE layanan
    ADD COLUMN estimasi_hasil_hari INTEGER,
    ADD COLUMN deliverables TEXT,
    ADD COLUMN maks_orang INTEGER,
    ADD CONSTRAINT layanan_estimasi_hasil_check CHECK (estimasi_hasil_hari > 0),
    ADD CONSTRAINT layanan_maks_orang_check CHECK (maks_orang > 0);

COMMENT ON COLUMN layanan.estimasi_hasil_hari IS 'Estimasi hasil foto siap diterima pelanggan, dalam satuan hari (dihitung setelah sesi foto)';
COMMENT ON COLUMN layanan.deliverables IS 'Poin hasil/output layanan, satu baris = satu poin. Dipisah dari deskripsi';
COMMENT ON COLUMN layanan.maks_orang IS 'Batas maksimal jumlah orang dalam satu sesi, boleh kosong (NULL = tidak dibatasi / tidak ditentukan)';

-- Pindahkan isi deskripsi lama ke deliverables supaya tampilan tidak kosong.
-- Setelah itu deskripsi bisa ditulis ulang sebagai kalimat penjelasan biasa.
UPDATE layanan
   SET deliverables = deskripsi
 WHERE deliverables IS NULL
   AND deskripsi IS NOT NULL;