# ERD SIMASIF (11 tabel)

```mermaid
erDiagram
    users ||--o{ pemesanan : "menangani (pegawai_id)"
    users ||--o{ workflow_layanan : "mengubah status (diubah_oleh)"
    users ||--o{ transaksi_pembayaran : "memverifikasi (diverifikasi_oleh)"
    users ||--o{ file_fotografi : "mengunggah hasil (diunggah_oleh)"
    users ||--o{ layanan_gambar : "mengunggah (diunggah_oleh)"
    users ||--o{ audit_log : "mencatat aktivitas"

    pelanggan ||--o{ pemesanan : "memesan"
    layanan ||--o{ pemesanan : "dipesan"
    layanan ||--o{ layanan_gambar : "punya galeri"
    layanan ||--o{ addon_layanan : "punya add-on"

    pemesanan ||--o{ workflow_layanan : "riwayat status"
    pemesanan ||--o| transaksi_pembayaran : "dibayar"
    pemesanan ||--o{ file_fotografi : "punya file"
    pemesanan ||--o{ pemesanan_addon : "memilih add-on"
    addon_layanan ||--o{ pemesanan_addon : "dipilih"

    users {
        int id PK
        varchar nama
        varchar email UK
        varchar password
        varchar no_telepon
        user_role role
        boolean is_active
        timestamp last_login_at
        varchar reset_token
        timestamp reset_token_expires_at
    }
    pelanggan {
        int id PK
        varchar nama
        varchar no_hp UK
    }
    layanan {
        int id PK
        varchar nama
        text deskripsi
        text deliverables
        numeric harga
        int estimasi_durasi_menit
        int estimasi_hasil_hari
        int maks_orang "nullable"
        boolean is_active
    }
    layanan_gambar {
        int id PK
        int layanan_id FK
        varchar nama_file
        varchar path_file
        varchar keterangan
        int urutan
        boolean is_cover "unik per layanan"
        int diunggah_oleh FK
    }
    addon_layanan {
        int id PK
        int layanan_id FK
        varchar nama "unik per layanan"
        numeric harga
        int tambahan_durasi_menit
        boolean is_active
    }
    pemesanan {
        int id PK
        varchar kode_unik UK
        int pelanggan_id FK
        int layanan_id FK
        int pegawai_id FK
        date tanggal_jadwal
        time jam_jadwal
        text catatan_khusus
        status_pemesanan status
        numeric harga_saat_pesan
        numeric biaya_tambahan "diisi trigger dari add-on"
        numeric diskon
        numeric total_tagihan "generated"
    }
    pemesanan_addon {
        int id PK
        int pemesanan_id FK
        int addon_id FK
        varchar nama_saat_pesan
        numeric harga_saat_pesan
        int jumlah
        numeric subtotal "generated"
    }
    workflow_layanan {
        int id PK
        int pemesanan_id FK
        status_pemesanan status_sebelumnya
        status_pemesanan status
        text catatan
        int diubah_oleh FK
    }
    transaksi_pembayaran {
        int id PK
        int pemesanan_id FK, UK
        metode_pembayaran metode_pembayaran
        numeric jumlah_bayar
        varchar bukti_transfer
        status_pembayaran status_pembayaran
        int diverifikasi_oleh FK
        timestamp diverifikasi_at
    }
    file_fotografi {
        int id PK
        int pemesanan_id FK
        jenis_file_fotografi jenis_file
        varchar nama_file_asli
        varchar nama_file
        varchar path_file
        status_file_fotografi status_file
        int diunggah_oleh FK
    }
    audit_log {
        int id PK
        int user_id FK
        varchar modul
        varchar aktivitas
        jsonb detail
        varchar ip_address
    }
```

Catatan relasi:

- `pemesanan_addon.addon_id` → `ON DELETE RESTRICT`: add-on yang sudah pernah dipesan tidak bisa dihapus, hanya dinonaktifkan.
- `layanan_gambar` dan `addon_layanan` → `ON DELETE CASCADE` dari `layanan`.
- `pemesanan.biaya_tambahan` otomatis = total `pemesanan_addon.subtotal` (trigger `sync_biaya_tambahan`).