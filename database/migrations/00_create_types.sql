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
);