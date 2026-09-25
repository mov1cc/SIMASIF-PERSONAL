# PANDUAN SETUP ENVIRONMENT — SIMASIF

Panduan ini untuk semua anggota tim yang baru pertama kali setup project ini di laptop masing-masing. Ikuti berurutan, jangan loncat.

**Tim ini pakai OS campuran:**
- **Ibrahim** → Ubuntu/Linux
- **Ayu, Rayyan, Rivaldo** → Windows

Ikuti bagian sesuai OS kalian masing-masing. Struktur project, `.env`, dan kode PHP-nya **sama persis** untuk semua OS — yang beda cuma cara install software & setup server lokalnya.

---

## 1. Install Software yang Dibutuhkan

### Windows (pakai Laragon — direkomendasikan)

1. Download **Laragon Full** dari [laragon.org/download](https://laragon.org/download/) — sudah termasuk Apache, PHP, dan Composer sekaligus
2. Install seperti biasa (Next-Next-Finish), biarkan lokasi default `C:\laragon`
3. **PostgreSQL tidak termasuk di Laragon**, install terpisah:
   - Download dari [postgresql.org/download/windows](https://www.postgresql.org/download/windows/)
   - Jalankan installer, **catat password superuser (`postgres`)** yang kalian buat saat instalasi — jangan sampai lupa
   - Biarkan port default `5432`
   - Centang **pgAdmin 4** saat instalasi (GUI untuk lihat database)
4. Setelah Laragon terbuka, klik menu **PHP** di sidebar kiri → pastikan versi PHP ≥ 8.1 dipilih
5. Aktifkan extension PostgreSQL di PHP:
   - Klik kanan ikon Laragon di tray → **PHP** → **php.ini**
   - Cari baris berikut, hapus tanda `;` di depannya kalau masih ada:
     ```ini
     extension=pdo_pgsql
     extension=pgsql
     ```
   - Simpan, lalu restart Laragon (klik **Stop All** → **Start All**)

**Cek semua terinstall dengan benar** (buka **Terminal** dari Laragon: klik kanan ikon Laragon → Terminal):
```bash
php -v
composer -V
psql --version
git --version
php -m | findstr pgsql
```
Baris terakhir harus menampilkan `pdo_pgsql` dan `pgsql`.

**Kalau Composer & Git belum ada** (Laragon kadang tidak bundling keduanya):
- Composer: download installer dari [getcomposer.org](https://getcomposer.org/Composer-Setup.exe)
- Git: download dari [git-scm.com/download/win](https://git-scm.com/download/win)

Set identitas Git (buka Git Bash atau terminal Laragon):
```bash
git config --global user.name "Nama Kamu"
git config --global user.email "email@kamu.com"
```

### Ubuntu/Linux

```bash
sudo apt update && sudo apt upgrade -y

# PHP + extension
sudo apt install -y php php-cli php-pgsql php-pdo php-mbstring php-fileinfo php-xml php-curl php-zip

# Apache
sudo apt install -y apache2 libapache2-mod-php

# PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Composer & Git
sudo apt install -y composer git
```

Cek instalasi:
```bash
php -v && composer -V && psql --version && git --version && apache2 -v
php -m | grep pgsql
```

Set identitas Git:
```bash
git config --global user.name "Nama Kamu"
git config --global user.email "email@kamu.com"
```

---

## 2. Setup PostgreSQL

### Windows

1. Buka **pgAdmin 4** (sudah terinstall bareng PostgreSQL)
2. Klik kiri **Servers** → **PostgreSQL** → masukkan password superuser yang tadi dibuat saat instalasi
3. Klik kanan **Login/Group Roles** → **Create** → **Login/Group Role**
   - Tab **General**: nama role → `simasif_user`
   - Tab **Definition**: password → buat password sendiri, catat baik-baik
   - Tab **Privileges**: aktifkan **Can login?**
   - Klik **Save**
4. Klik kanan **Databases** → **Create** → **Database**
   - Database name: `simasif`
   - Owner: pilih `simasif_user`
   - Klik **Save**

**Alternatif lewat Query Tool** (kalau lebih nyaman pakai SQL daripada klik-klik GUI): klik kanan **Databases** → **Query Tool**, lalu jalankan:
```sql
CREATE USER simasif_user WITH PASSWORD 'buat_password_sendiri';
CREATE DATABASE simasif OWNER simasif_user;
GRANT ALL PRIVILEGES ON DATABASE simasif TO simasif_user;
```

**Test koneksi** lewat terminal Laragon:
```bash
psql -U simasif_user -d simasif -h 127.0.0.1 -W
```
Kalau berhasil masuk ke prompt `simasif=>`, ketik `\q` untuk keluar.

### Ubuntu/Linux

```bash
sudo systemctl status postgresql
```
Kalau belum jalan, cek nama cluster:
```bash
pg_lsclusters
sudo systemctl start postgresql@18-main
```

Buat user & database:
```bash
sudo -u postgres psql
```
```sql
CREATE USER simasif_user WITH PASSWORD 'buat_password_sendiri';
CREATE DATABASE simasif OWNER simasif_user;
GRANT ALL PRIVILEGES ON DATABASE simasif TO simasif_user;
\q
```

Test koneksi:
```bash
psql -U simasif_user -d simasif -h 127.0.0.1 -W
```

---

## 3. Clone Repository

**Sama untuk semua OS.** Buka terminal (Windows: terminal Laragon atau Git Bash; Linux: terminal biasa):

```bash
git clone <URL_REPO_GITHUB_KALIAN>
cd simasif
```

**Untuk Windows:** taruh folder hasil clone di `C:\laragon\www\simasif` — ini folder khusus Laragon yang otomatis dikenali sebagai web project.

---

## 4. Install Dependency Composer

**Sama untuk semua OS:**
```bash
composer install
```

Jangan jalankan `composer update` — cukup `composer install` supaya versi package sama persis dengan yang sudah dikunci di `composer.lock`.

---

## 5. Setup File `.env`

**Sama untuk semua OS.** Copy dari template:

**Windows (Command Prompt):**
```cmd
copy .env.example .env
```
**Windows (Git Bash) / Linux:**
```bash
cp .env.example .env
```

Buka `.env` (pakai VS Code), isi:
```env
DB_PASSWORD=<password_yang_kalian_buat_di_langkah_2>
```

**Khusus Windows dengan Laragon**, sesuaikan juga `APP_URL` karena Laragon otomatis membuat virtual host berdasarkan nama folder:
```env
APP_URL=http://simasif.test
```
(Laragon otomatis pakai akhiran `.test`, beda dengan `.local` yang dipakai di setup Ubuntu — ini tidak masalah, cuma beda konvensi)

`MAIL_USERNAME` dan `MAIL_PASSWORD` boleh dikosongkan dulu.

---

## 6. Setup Virtual Host

### Windows (Laragon — jauh lebih simpel)

Kalau project sudah ditaruh di `C:\laragon\www\simasif` (langkah 3), Laragon **otomatis** membuat virtual host tanpa perlu konfigurasi manual apa pun. Cukup:

1. Buka Laragon, klik **Start All**
2. Klik menu **www** di Laragon → klik kanan folder `simasif` → **Open with browser** — atau langsung ketik di browser: `http://simasif.test`

**Penting:** karena struktur project kita `public/` adalah document root (bukan folder root project), perlu sedikit penyesuaian. Klik kanan ikon Laragon → **Apache** → **sites-enabled** → cari file `auto.simasif.test.conf`, buka, ubah baris `DocumentRoot` dan `<Directory>` supaya mengarah ke `public/`:
```apache
DocumentRoot "C:/laragon/www/simasif/public"
<Directory "C:/laragon/www/simasif/public">
```
Simpan, lalu klik **Stop All** → **Start All** di Laragon.

Akses: `http://simasif.test`

### 🐧 Ubuntu/Linux

```bash
sudo nano /etc/apache2/sites-available/simasif.conf
```
Isi (ganti `USERNAME` dan path sesuai lokasi project):
```apache
<VirtualHost *:80>
    ServerName simasif.local
    DocumentRoot /home/USERNAME/path/ke/simasif/public

    <Directory /home/USERNAME/path/ke/simasif/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/simasif-error.log
    CustomLog ${APACHE_LOG_DIR}/simasif-access.log combined
</VirtualHost>
```
```bash
sudo a2ensite simasif.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
echo "127.0.0.1   simasif.local" | sudo tee -a /etc/hosts
```

---

## 7. Perbaiki Permission Folder

### Windows

Umumnya **tidak ada masalah permission** seperti di Linux — Windows tidak punya konsep permission folder home yang ketat seperti itu. Tapi kalau folder `storage/` bermasalah tidak bisa ditulis (jarang terjadi), klik kanan folder `storage` → **Properties** → tab **Security** → pastikan user kalian punya izin **Full Control**.

### Ubuntu/Linux

```bash
chmod o+x /home/USERNAME
chmod o+x /home/USERNAME/path
chmod o+x /home/USERNAME/path/ke
chmod o+x /home/USERNAME/path/ke/simasif
```
```bash
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/
sudo usermod -aG www-data $USER
```
(logout & login ulang setelah baris terakhir)

---

## 8. Testing

**Windows:** buka browser, akses `http://simasif.test`
**Linux:** buka browser, akses `http://simasif.local`

Kalau muncul tulisan dari `public/index.php` saat ini, setup berhasil.

**Kalau muncul 403 Forbidden (Windows):** cek `DocumentRoot` di config Apache Laragon sudah benar mengarah ke `public/`, bukan ke root folder project.

**Kalau muncul 403 Forbidden (Linux):** cek permission folder (langkah 7), pakai `namei -l /home/USERNAME/path/ke/simasif/public` untuk cari folder mana yang menghalangi.

**Kalau muncul error database:** cek `.env` — pastikan `DB_PASSWORD` benar dan PostgreSQL service/aplikasi jalan (Windows: cek di pgAdmin server terhubung; Linux: `sudo systemctl status postgresql`).

---

## Checklist Sebelum Mulai Coding

- [ ] `php -v` menunjukkan PHP ≥8.1
- [ ] `php -m | findstr pgsql` (Windows) / `php -m | grep pgsql` (Linux) menampilkan `pdo_pgsql` & `pgsql`
- [ ] PostgreSQL jalan, bisa `psql -U simasif_user -d simasif -h 127.0.0.1 -W`
- [ ] `composer install` sukses tanpa error
- [ ] `.env` sudah diisi sesuai milik sendiri (terutama `DB_PASSWORD`, dan `APP_URL` sesuai OS)
- [ ] `http://simasif.test` (Windows) atau `http://simasif.local` (Linux) bisa dibuka tanpa error
- [ ] `git status` menunjukkan repo bersih

---

## Alur Kerja Git Sehari-hari (Sama untuk Semua OS)

**Sebelum mulai kerja, tarik perubahan terbaru dulu:**
```bash
git checkout develop
git pull origin develop
```

**Buat branch sendiri untuk fitur yang dikerjakan:**
```bash
git checkout -b feature/nama-fitur-kamu
```

**Setelah selesai kerja:**
```bash
git add .
git commit -m "feat: deskripsi singkat yang dikerjakan"
git push origin feature/nama-fitur-kamu
```

Lalu buat **Pull Request** di GitHub dari branch kalian ke `develop`, minta salah satu teman review sebelum di-merge.

**Jangan pernah:**
- Commit file `.env` (sudah otomatis di-ignore, tapi tetap hati-hati)
- Push langsung ke branch `main`
- Jalankan `composer update` tanpa diskusi tim dulu

---

## Catatan Penting untuk Tim Campuran OS

- **Path folder berbeda** antara Windows (`C:\laragon\www\simasif`) dan Linux (`/home/user/.../simasif`) — ini **tidak masalah** karena path itu cuma di virtual host lokal masing-masing, tidak pernah masuk ke kode yang di-commit.
- **`APP_URL` di `.env` boleh beda** per anggota (`.test` vs `.local`) — karena `.env` memang **tidak di-commit**, jadi masing-masing bebas pakai domain lokal sesuai OS masing-masing tanpa bentrok.
- **Pastikan semua pakai versi PHP yang sama** (minimal sama-sama ≥8.1, idealnya sama persis misal semua PHP 8.2) untuk menghindari bug aneh akibat perbedaan versi.
- **Baris kode jangan pernah hardcode path** (misal `C:\laragon\...` atau `/home/movic/...`) — selalu pakai `__DIR__` atau konstanta dari `Config/app.php` supaya kode portable di semua OS.

---

## Kalau Ada Masalah

Tanyakan ke grup tim dulu sebelum menghabiskan waktu sendirian. Catat solusi yang ditemukan di file ini (edit & commit lagi) supaya anggota lain atau kalau ganti laptop tidak mengulang proses debugging yang sama.