UPDATE layanan SET
    deskripsi = 'Sesi foto wisuda dengan satu lokasi pilihan.',
    deliverables = E'1 lokasi indoor/outdoor\n10 file hasil edit terbaik',
    estimasi_hasil_hari = 3, maks_orang = 3
WHERE nama = 'Paket Foto Wisuda';

UPDATE layanan SET
    deskripsi = 'Sesi foto keluarga di studio.',
    deliverables = E'15 file hasil edit\n1 cetak ukuran 10R',
    estimasi_hasil_hari = 4, maks_orang = 6
WHERE nama = 'Paket Foto Keluarga';

UPDATE layanan SET
    deskripsi = 'Sesi foto produk untuk katalog atau marketplace.',
    deliverables = E'20 file hasil edit',
    estimasi_hasil_hari = 3, maks_orang = NULL
WHERE nama = 'Paket Foto Produk';

UPDATE layanan SET
    deskripsi = 'Sesi foto prewedding dengan dua lokasi.',
    deliverables = E'2 lokasi\n30 file hasil edit\nAlbum cetak',
    estimasi_hasil_hari = 14, maks_orang = 2
WHERE nama = 'Paket Prewedding';

UPDATE layanan SET
    deskripsi = 'Sesi foto formal untuk dokumen atau CV.',
    deliverables = E'5 file hasil edit',
    estimasi_hasil_hari = 1, maks_orang = 1
WHERE nama = 'Paket Foto Formal';