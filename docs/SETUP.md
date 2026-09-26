# PANDUAN SETUP - WINDOWS

## Untuk Tim SIMASIF (Ayu, Rayyan, Rivaldo)

Cara pakai panduan ini: setiap bagian punya 3 kemungkinan jalur. Cek dulu status kalian, baru ikuti jalur yang sesuai.

Alur pengambilan keputusan:
- Kalau alat/komponen belum ter-install sama sekali, ikuti "Jalur A: Install dari Awal".
- Kalau alat/komponen sudah ter-install tapi belum di-setup untuk project ini, ikuti "Jalur B: Setup".
- Kalau alat/komponen sudah ter-install dan sudah pernah di-setup, ikuti "Jalur C: Verifikasi". Kalau hasil verifikasi sudah sesuai, lanjut ke bagian berikutnya. Kalau belum sesuai, kembali ke Jalur B untuk memperbaiki setup-nya.

Jangan lewati langkah "cek" walaupun kalian merasa yakin sudah pernah setup sebelumnya. Sering ada detail kecil yang berbeda dari yang dibutuhkan project ini (misalnya versi PHP, nama user database, atau path folder).

---

## BAGIAN 1 - Git dan GitHub

### Cek

Buka Command Prompt atau PowerShell, ketik perintah berikut satu per satu:

```
git --version
git config --global user.name
git config --global user.email
```

Tentukan jalur berdasarkan hasil:
- Kalau `git --version` menampilkan pesan error seperti "not recognized", berarti Git belum ter-install. Lanjut ke Jalur A.
- Kalau versi Git muncul dengan benar tapi `user.name` atau `user.email` tidak menampilkan apa-apa, berarti Git sudah ter-install tapi identitasnya belum di-setup. Lanjut ke Jalur B.
- Kalau semua perintah di atas menampilkan hasil dengan benar, lanjut ke Jalur C untuk verifikasi.

### Jalur A - Install dari Awal

1. Download Git dari https://git-scm.com/download/win
2. Jalankan file installer yang sudah didownload
3. Selama proses instalasi, biarkan semua opsi dalam keadaan default, cukup klik Next secara berurutan sampai muncul tombol Install, lalu klik Install
4. Setelah instalasi selesai, lanjutkan ke Jalur B di bawah ini untuk melakukan setup identitas

### Jalur B - Setup

Jalankan perintah berikut untuk mengatur identitas Git (ganti dengan nama dan email akun GitHub kalian masing-masing):

```
git config --global user.name "Nama Kamu"
git config --global user.email "email_github_kamu@gmail.com"
```

Setelah itu, periksa dua hal berikut terkait akun GitHub:

Pertama, periksa apakah kalian sudah punya akun GitHub dan sudah menerima undangan sebagai collaborator di repository SIMASIF dari Ibrahim.
- Kalau belum punya akun GitHub, daftar terlebih dahulu di https://github.com/signup
- Kalau sudah punya akun tapi belum menerima undangan, periksa email atau buka https://github.com/notifications, lalu klik Accept invitation pada undangan yang muncul

Kedua, periksa apakah kalian sudah memiliki Personal Access Token untuk laptop yang sedang digunakan ini. Token ini diperlukan karena GitHub tidak lagi menerima password akun biasa saat melakukan git clone atau git push dari terminal.
- Kalau belum punya, buat token baru dengan langkah berikut:
  1. Buka https://github.com/settings/tokens
  2. Klik Generate new token, lalu pilih Generate new token (classic)
  3. Isi kolom Note dengan teks seperti "SIMASIF Laptop [Nama Kamu]"
  4. Pilih Expiration sesuai preferensi, misalnya 90 days atau No expiration
  5. Centang kotak scope bernama repo
  6. Klik Generate token di bagian bawah halaman
  7. Salin token yang muncul (formatnya diawali ghp_) dan simpan sementara di Notepad. Token ini hanya ditampilkan satu kali saja, jadi pastikan tersimpan dengan aman sebelum menutup halaman tersebut

### Jalur C - Verifikasi

Cocokkan hasil dari ketiga perintah berikut:

```
git --version
git config --global user.name
git config --global user.email
```

Pastikan nama dan email yang muncul memang benar akun GitHub kalian yang sudah menjadi collaborator di repository SIMASIF. Kalau ternyata berbeda akun atau ada kesalahan, ulangi langkah-langkah di Jalur B.

Kalau semua sudah sesuai, lanjutkan ke Bagian 2.

---

## BAGIAN 2 - Laragon (PHP, Apache, dan Composer)

### Cek

Buka Terminal Laragon (kalau Laragon sudah ter-install, klik kanan ikon Laragon lalu pilih Terminal). Kalau Laragon belum ter-install sama sekali, buka Command Prompt biasa terlebih dahulu, lalu jalankan:

```
php -v
composer -V
```

Tentukan jalur berdasarkan hasil:
- Kalau kedua perintah menampilkan pesan error "not recognized", atau Laragon memang belum ter-install sama sekali, lanjut ke Jalur A.
- Kalau Laragon sudah ter-install tapi versi PHP yang tampil di bawah 8.1, atau Composer belum tersedia, lanjut ke Jalur B.
- Kalau versi PHP sudah 8.1 ke atas dan Composer sudah tersedia, lanjut ke Jalur C untuk verifikasi lebih detail.

### Jalur A - Install dari Awal

1. Download Laragon versi Full (bukan versi Lite) dari https://laragon.org/download/
2. Jalankan file installer, biarkan lokasi instalasi dalam keadaan default yaitu di C:\laragon
3. Selesaikan proses instalasi sampai selesai
4. Buka aplikasi Laragon, klik tombol Start All
5. Setelah itu, lanjutkan ke Jalur B untuk memastikan versi PHP dan Composer sudah sesuai kebutuhan

### Jalur B - Setup

Kalau versi PHP masih di bawah 8.1, lakukan langkah berikut:
1. Klik kanan ikon Laragon di system tray (pojok kanan bawah layar), lalu pilih PHP
2. Periksa daftar versi PHP yang tersedia. Kalau versi 8.1 ke atas sudah ada di daftar, klik untuk memilih dan mengaktifkannya
3. Kalau versi yang dibutuhkan belum tersedia di daftar, klik PHP, lalu pilih Download more, kemudian pilih versi 8.1 ke atas dan tunggu proses download selesai, setelah itu aktifkan versi tersebut

Kalau Composer belum tersedia, lakukan langkah berikut:
1. Download installer Composer dari https://getcomposer.org/Composer-Setup.exe
2. Jalankan installer tersebut. Saat diminta memilih lokasi PHP, arahkan ke folder PHP milik Laragon, biasanya berlokasi di C:\laragon\bin\php\php-8.x.x\php.exe (sesuaikan angka versi dengan yang sedang aktif)
3. Selesaikan proses instalasi, lalu tutup dan buka kembali terminal, kemudian periksa ulang dengan mengetik composer -V

Setelah PHP dan Composer selesai disetel, restart Laragon dengan klik Stop All kemudian Start All, lalu lanjutkan ke Jalur C untuk memverifikasi ekstensi PHP yang dibutuhkan.

### Jalur C - Verifikasi

Jalankan tiga perintah berikut:

```
php -v
composer -V
php -m | findstr pgsql
```

Periksa tiga hal berikut:
1. Apakah versi PHP yang tampil sudah 8.1 ke atas
2. Apakah versi Composer 2.x muncul dengan benar
3. Apakah perintah ketiga menampilkan dua baris hasil, yaitu pdo_pgsql dan pgsql

Kalau poin ketiga tidak menampilkan hasil apa pun (artinya ekstensi belum aktif), lakukan langkah berikut:
1. Klik kanan ikon Laragon di system tray, pilih PHP, lalu pilih php.ini
2. File php.ini akan terbuka di text editor. Gunakan Ctrl+F untuk mencari baris `;extension=pdo_pgsql` dan baris `;extension=pgsql`
3. Hapus tanda titik koma (;) di depan kedua baris tersebut, lalu simpan file
4. Restart Laragon dengan klik Stop All kemudian Start All
5. Periksa ulang dengan menjalankan `php -m | findstr pgsql`

Kalau ketiga hal di atas sudah sesuai, lanjutkan ke Bagian 3.

---

## BAGIAN 3 - PostgreSQL

### Cek

Jalankan perintah berikut:

```
psql --version
```

Tentukan jalur berdasarkan hasil:
- Kalau muncul pesan error "not recognized", lanjut ke Jalur A.
- Kalau versi PostgreSQL muncul dengan benar tapi kalian belum pernah membuat user dan database khusus untuk project ini, lanjut ke Jalur B.
- Kalau kalian sudah pernah membuat user dan database untuk project ini sebelumnya, lanjut ke Jalur C untuk verifikasi.

Catatan: kalau perintah psql menampilkan error "not recognized" padahal PostgreSQL sebenarnya sudah ter-install, kemungkinan besar folder instalasinya belum ditambahkan ke Environment Variables (PATH). Cari lokasi folder instalasi PostgreSQL, biasanya di C:\Program Files\PostgreSQL\16\bin, lalu tambahkan ke PATH melalui pengaturan Environment Variables di Windows (cari "Edit environment variables" di Start Menu, lalu pilih Path pada bagian User variables, klik Edit, klik New, tempelkan path folder tadi, lalu klik OK pada semua jendela yang terbuka). Setelah itu, tutup dan buka kembali terminal sebelum mencoba lagi.

### Jalur A - Install dari Awal

1. Download installer dari https://www.postgresql.org/download/windows/, klik Download the installer, pilih versi 16 atau 17
2. Jalankan installer tersebut dan ikuti wizard instalasi
3. Saat diminta membuat password untuk user superuser bernama postgres, buat password tersebut dan catat baik-baik di Notepad
4. Biarkan port dalam keadaan default yaitu 5432, dan locale juga biarkan default
5. Di akhir instalasi akan muncul aplikasi bernama Stack Builder, aplikasi ini boleh ditutup atau dilewati karena tidak dibutuhkan
6. Aplikasi pgAdmin 4 akan otomatis ikut ter-install bersamaan dengan PostgreSQL
7. Setelah instalasi selesai, lanjutkan ke Jalur B untuk membuat user dan database khusus project ini

### Jalur B - Setup

1. Buka aplikasi pgAdmin 4 dari Start Menu
2. Kalau diminta membuat Master Password untuk pgAdmin, buat password baru (password ini berbeda dari password PostgreSQL yang dibuat sebelumnya, ini khusus untuk membuka aplikasi pgAdmin)
3. Pada panel sebelah kiri, klik Servers, lalu klik PostgreSQL. Kalian akan diminta memasukkan password, masukkan password user postgres yang dibuat saat instalasi
4. Setelah terhubung, klik kanan pada Login/Group Roles, pilih Create, lalu pilih Login/Group Role
   - Pada tab General, isi kolom Name dengan simasif_user
   - Pada tab Definition, isi kolom Password dengan password bebas buatan sendiri, catat di Notepad
   - Pada tab Privileges, aktifkan toggle Can login
   - Klik Save
5. Klik kanan pada Databases, pilih Create, lalu pilih Database
   - Isi Database name dengan simasif
   - Pada kolom Owner, pilih simasif_user dari daftar
   - Klik Save

### Jalur C - Verifikasi

Jalankan perintah berikut:

```
psql -U simasif_user -d simasif -h 127.0.0.1 -W
```

Masukkan password milik simasif_user yang dibuat sebelumnya. Kalau berhasil masuk dan muncul prompt bertuliskan simasif=>, berarti sudah berhasil. Ketik `\q` untuk keluar.

Kalau proses ini gagal (misalnya password salah atau user/database tidak ditemukan), kembali ke Jalur B dan periksa kembali di pgAdmin apakah user dan database memang sudah benar-benar dibuat dengan nama yang tepat.

Kalau berhasil terhubung, lanjutkan ke Bagian 4.

---

## BAGIAN 4 - Clone Repository SIMASIF

### Cek

Jalankan perintah berikut:

```
dir C:\laragon\www\simasif
```

Tentukan jalur berdasarkan hasil:
- Kalau muncul pesan "File Not Found" atau folder tersebut memang tidak ada, lanjut ke Jalur A.
- Kalau folder tersebut ada tapi terlihat tidak lengkap, misalnya hanya berisi sebagian file saja, lanjut ke Jalur B.
- Kalau folder tersebut sudah lengkap, lanjut ke Jalur C untuk verifikasi.

### Jalur A - Clone dari Awal

Buka Git Bash, lalu jalankan:

```
cd /c/laragon/www
git clone https://github.com/mov1cc/SIMASIF.git simasif
```

Saat diminta Username, masukkan username GitHub kalian. Saat diminta Password, tempelkan Personal Access Token yang sudah dibuat pada Bagian 1, bukan password akun biasa.

### Jalur B - Perbaiki Clone yang Bermasalah

Kalau folder sudah ada tapi rusak atau tidak lengkap, hapus dulu folder tersebut secara keseluruhan (pastikan tidak ada pekerjaan penting yang belum di-push sebelum menghapus), lalu ulangi langkah pada Jalur A:

```
rm -rf /c/laragon/www/simasif
```

### Jalur C - Verifikasi

Masuk ke folder project, lalu jalankan:

```
cd /c/laragon/www/simasif
git status
git remote -v
```

Pastikan hasil git remote -v menunjukkan URL repository SIMASIF yang benar, dan git status tidak menampilkan pesan error.

Kalau semua sesuai, lanjutkan ke Bagian 5.

---

## BAGIAN 5 - Composer Install (Dependency Project)

### Cek

Dari dalam folder simasif, jalankan:

```
dir vendor
```

Tentukan jalur berdasarkan hasil:
- Kalau folder vendor tidak ditemukan, lanjut ke Jalur A.
- Kalau folder vendor ada tapi kalian merasa belum lengkap, misalnya baru saja melakukan git pull dan ada perubahan pada file composer.json, lanjut ke Jalur B.
- Kalau folder vendor sudah ada dan diyakini sudah lengkap, lanjut ke Jalur C untuk verifikasi.

### Jalur A - Install dari Awal

```
composer install
```

Tunggu proses ini sampai selesai. Proses ini akan mendownload package PHPMailer, phpdotenv, dan package pendukung lainnya.

### Jalur B - Update Setelah Ada Perubahan

```
composer install
```

Catatan penting: gunakan perintah composer install, bukan composer update. Perintah install akan mengikuti versi package yang sudah dikunci pada file composer.lock, sehingga semua anggota tim memakai versi yang sama persis. Perintah update berpotensi mengubah versi package secara otomatis dan menyebabkan perbedaan antar laptop anggota tim.

### Jalur C - Verifikasi

```
dir vendor\autoload.php
```

Pastikan file tersebut memang ada. Kalau ada, berarti proses instalasi dependency sudah selesai dengan baik.

Lanjutkan ke Bagian 6.

---

## BAGIAN 6 - File .env

### Cek

```
dir .env
```

Tentukan jalur berdasarkan hasil:
- Kalau file tersebut tidak ditemukan, lanjut ke Jalur A.
- Kalau file tersebut sudah ada tapi isinya belum pernah diubah dari template (masih berisi contoh seperti your_password), lanjut ke Jalur B.
- Kalau file tersebut sudah ada dan sudah pernah diisi sebelumnya, lanjut ke Jalur C untuk verifikasi.

### Jalur A - Buat dari Awal

```
cp .env.example .env
```

Setelah file dibuat, lanjutkan ke Jalur B untuk mengisi nilai yang dibutuhkan.

### Jalur B - Setup

Buka file .env menggunakan VS Code atau Notepad, lalu ubah baris berikut:

```
DB_PASSWORD=<password_simasif_user_dari_Bagian_3>
APP_URL=http://simasif.test
```

Biarkan baris lainnya sesuai dengan isi default pada .env.example. Untuk MAIL_USERNAME dan MAIL_PASSWORD, boleh dikosongkan terlebih dahulu karena belum dibutuhkan sampai tahap pengerjaan fitur Forgot Password nanti.

### Jalur C - Verifikasi

Buka file .env, lalu periksa kembali:
- DB_PASSWORD sudah berisi password simasif_user yang benar, bukan lagi placeholder
- APP_URL berisi http://simasif.test
- DB_DATABASE berisi simasif, dan DB_USERNAME berisi simasif_user

Kalau ada yang belum sesuai, kembali ke Jalur B untuk memperbaikinya.

Kalau semua sudah sesuai, lanjutkan ke Bagian 7.

---

## BAGIAN 7 - Virtual Host (simasif.test)

### Cek

Buka browser, lalu akses alamat http://simasif.test

Tentukan jalur berdasarkan hasil:
- Kalau muncul pesan seperti "This site can't be reached" atau error terkait DNS, lanjut ke Jalur A.
- Kalau muncul halaman berisi daftar file dan folder (bukan tampilan aplikasi), atau muncul pesan 403 Forbidden, lanjut ke Jalur B.
- Kalau muncul tulisan yang berasal dari file public/index.php (misalnya tulisan "SIMASIF environment siap"), berarti sudah berhasil, lanjut ke Jalur C untuk verifikasi akhir.

### Jalur A - Setup dari Awal

Laragon secara otomatis akan mendeteksi folder yang ada di dalam www dan membuatkan virtual host dengan nama sesuai nama folder tersebut ditambah akhiran .test. Untuk memastikan ini berjalan:
1. Pastikan Laragon dalam keadaan Start All
2. Klik kanan ikon Laragon, lalu periksa menu Www directory, pastikan menunjuk ke folder C:\laragon\www
3. Restart Laragon dengan klik Stop All kemudian Start All
4. Coba akses kembali http://simasif.test. Kalau masih belum berhasil, kemungkinan besar memang perlu perbaikan pada bagian DocumentRoot, lanjutkan ke Jalur B

### Jalur B - Perbaiki DocumentRoot

Virtual host otomatis yang dibuat Laragon secara default akan mengarah ke folder utama project, padahal yang dibutuhkan adalah folder public di dalamnya. Perbaiki dengan langkah berikut:

1. Klik kanan ikon Laragon, pilih Apache, lalu pilih sites-enabled
2. Akan terbuka folder berisi file konfigurasi. Cari file bernama auto.simasif.test.conf, lalu buka menggunakan Notepad atau VS Code
3. Ubah baris DocumentRoot dan baris Directory, tambahkan kata /public di bagian akhir path, sehingga menjadi seperti berikut:

```
DocumentRoot "C:/laragon/www/simasif/public"
<Directory "C:/laragon/www/simasif/public">
    AllowOverride All
    Require all granted
</Directory>
```

4. Simpan file tersebut
5. Restart Laragon dengan klik Stop All kemudian Start All

### Jalur C - Verifikasi

Muat ulang (refresh) halaman http://simasif.test di browser, pastikan yang tampil benar-benar konten dari file public/index.php, bukan daftar file dan folder.

Catatan: terkadang setelah Laragon di-restart, file auto.simasif.test.conf akan ter-generate ulang secara otomatis dan pengaturan DocumentRoot kembali seperti semula (kehilangan tambahan /public). Kalau tiba-tiba muncul kembali halaman daftar file, ulangi langkah pada Jalur B.

Kalau tampilan sudah benar, berarti environment kalian sudah siap sepenuhnya.

---

## RINGKASAN STATUS

Salin tabel berikut, isi kolom status sesuai kondisi masing-masing, lalu kirimkan ke grup tim sebagai laporan progres setup:

| Bagian | Status (Sudah/Belum) | Catatan |
|---|---|---|
| 1. Git dan GitHub | | |
| 2. Laragon (PHP, Apache, Composer) | | |
| 3. PostgreSQL (user dan database) | | |
| 4. Clone Repository | | |
| 5. Composer Install | | |
| 6. File .env | | |
| 7. Virtual Host simasif.test | | |

Kalau semua baris sudah berstatus Sudah, laporkan ke tim bahwa kalian sudah siap untuk mulai mengerjakan Tahap 1.

---

## ALUR KERJA GIT SEHARI-HARI (Setelah Semua Setup Selesai)

Sebelum mulai bekerja setiap harinya, jalankan:

```
git checkout develop
git pull origin develop
```

Buat branch sendiri untuk fitur yang akan dikerjakan:

```
git checkout -b feature/nama-fitur-kamu
```

Setelah selesai mengerjakan sesuatu:

```
git add .
git commit -m "feat: deskripsi singkat pekerjaan yang dilakukan"
git push origin feature/nama-fitur-kamu
```

Setelah itu, buat Pull Request di GitHub dari branch kalian ke branch develop, lalu minta salah satu anggota tim untuk melakukan review sebelum digabungkan.

Hal-hal yang tidak boleh dilakukan:
- Jangan pernah melakukan commit terhadap file .env, walaupun file ini sudah otomatis diabaikan oleh .gitignore, tetap periksa dengan git status sebelum melakukan commit
- Jangan melakukan push langsung ke branch main
- Jangan menjalankan composer update tanpa berdiskusi terlebih dahulu dengan tim, karena dapat mengubah versi package untuk semua orang dan berpotensi menyebabkan bug yang tidak terduga

---

## Kalau Masih Ada Kendala

Jangan memendam masalah sendirian lebih dari 15 sampai 20 menit. Segera tanyakan ke grup tim dengan menyertakan informasi berikut:
1. Bagian nomor berapa pada panduan ini yang bermasalah
2. Screenshot pesan error secara lengkap
3. Perintah apa saja yang sudah dijalankan sebelum error tersebut muncul

Kemungkinan besar salah satu anggota tim lain sudah pernah mengalami masalah yang sama. Kalau menemukan solusi baru yang belum tercantum dalam panduan ini, mohon perbarui juga file ini dengan cara mengedit, melakukan commit, dan push perubahan tersebut, supaya anggota lain tidak perlu mengulang proses debugging yang sama.