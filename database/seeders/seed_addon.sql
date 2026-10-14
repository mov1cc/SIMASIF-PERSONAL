INSERT INTO addon_layanan 
(
    layanan_id,
    nama,
    deskripsi,
    harga,
    tambahan_durasi_menit
)
VALUES

-- Foto Editing (ID 10)
(
    10,
    'Tambah File Edit',
    'Tambahan file hasil editing foto',
    50000,
    15
),

(
    10,
    'Edit Prioritas',
    'Pengerjaan editing lebih cepat',
    75000,
    0
),

-- Home Service (ID 11)
(
    11,
    'Tambah Jam Sesi',
    'Tambahan waktu pemotretan di lokasi',
    100000,
    60
),

(
    11,
    'Lokasi Tambahan',
    'Tambahan satu lokasi pemotretan',
    150000,
    45
),

-- Pas Foto (ID 13)
(
    13,
    'Cetak Foto Tambahan',
    'Tambahan cetak foto ukuran standar',
    35000,
    0
),

(
    13,
    'Tambah Orang',
    'Tambahan satu orang dalam sesi foto',
    25000,
    10
);