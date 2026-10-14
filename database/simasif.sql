--
-- PostgreSQL database dump
--

\restrict UGGhvPM8QUxsTsvcEQdBcS0bJJiDuSbOdrI30GQHhcNw4YQM5Or9ygoyxhn9Ww0

-- Dumped from database version 18.6 (Ubuntu 18.6-0ubuntu0.26.04.1)
-- Dumped by pg_dump version 18.6 (Ubuntu 18.6-0ubuntu0.26.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: jenis_file_fotografi; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.jenis_file_fotografi AS ENUM (
    'referensi',
    'hasil'
);


--
-- Name: metode_pembayaran; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.metode_pembayaran AS ENUM (
    'transfer',
    'langsung'
);


--
-- Name: status_file_fotografi; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.status_file_fotografi AS ENUM (
    'belum_tersedia',
    'tersedia'
);


--
-- Name: status_pembayaran; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.status_pembayaran AS ENUM (
    'menunggu_verifikasi',
    'lunas',
    'ditolak'
);


--
-- Name: status_pemesanan; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.status_pemesanan AS ENUM (
    'pemesanan_dibuat',
    'menunggu_pembayaran',
    'pembayaran_diverifikasi',
    'terjadwal',
    'sesi_foto',
    'proses_editing',
    'hasil_siap',
    'selesai'
);


--
-- Name: user_role; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.user_role AS ENUM (
    'owner',
    'pegawai'
);


--
-- Name: trigger_set_updated_at(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.trigger_set_updated_at() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

BEGIN

    NEW.updated_at = NOW();

    RETURN NEW;

END;

$$;


--
-- Name: FUNCTION trigger_set_updated_at(); Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON FUNCTION public.trigger_set_updated_at() IS 'Mengubah nilai kolom updated_at menjadi waktu saat ini sebelum UPDATE dilakukan.';


--
-- Name: trigger_sync_biaya_tambahan(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.trigger_sync_biaya_tambahan() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: addon_layanan; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.addon_layanan (
    id integer NOT NULL,
    layanan_id integer NOT NULL,
    nama character varying(150) NOT NULL,
    deskripsi text,
    harga numeric(12,2) NOT NULL,
    tambahan_durasi_menit integer DEFAULT 0 NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT addon_layanan_durasi_check CHECK ((tambahan_durasi_menit >= 0)),
    CONSTRAINT addon_layanan_harga_check CHECK ((harga >= (0)::numeric)),
    CONSTRAINT addon_layanan_nama_check CHECK (((nama)::text <> ''::text))
);


--
-- Name: TABLE addon_layanan; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.addon_layanan IS 'Opsi tambahan berbayar milik satu layanan (mis. tambah orang, cetak ekstra)';


--
-- Name: COLUMN addon_layanan.tambahan_durasi_menit; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.addon_layanan.tambahan_durasi_menit IS 'Tambahan durasi sesi jika add-on dipilih, 0 = tidak menambah durasi';


--
-- Name: COLUMN addon_layanan.is_active; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.addon_layanan.is_active IS 'Add-on nonaktif tidak bisa dipilih pelanggan baru';


--
-- Name: addon_layanan_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.addon_layanan ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.addon_layanan_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: audit_log; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.audit_log (
    id integer NOT NULL,
    user_id integer,
    modul character varying(50) NOT NULL,
    aktivitas character varying(150) NOT NULL,
    detail jsonb,
    ip_address character varying(45),
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_audit_aktivitas_not_empty CHECK (((aktivitas)::text <> ''::text)),
    CONSTRAINT chk_audit_modul_not_empty CHECK (((modul)::text <> ''::text))
);


--
-- Name: TABLE audit_log; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.audit_log IS 'Pencatatan aktivitas pengguna internal untuk keamanan dan transparansi';


--
-- Name: COLUMN audit_log.user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.audit_log.user_id IS 'User yang melakukan aktivitas, NULL apabila akun sudah dihapus';


--
-- Name: COLUMN audit_log.modul; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.audit_log.modul IS 'Kategori modul terkait aktivitas, misalnya Pemesanan, Pembayaran, Workflow, Layanan, Pelanggan, atau Auth, dipakai untuk memudahkan filter riwayat di halaman Owner';


--
-- Name: COLUMN audit_log.aktivitas; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.audit_log.aktivitas IS 'Deskripsi singkat aktivitas yang dilakukan pengguna';


--
-- Name: COLUMN audit_log.detail; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.audit_log.detail IS 'Informasi tambahan dalam format JSON, misalnya data sebelum dan sesudah perubahan atau metadata aktivitas';


--
-- Name: COLUMN audit_log.created_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.audit_log.created_at IS 'Waktu aktivitas dicatat';


--
-- Name: audit_log_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.audit_log ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.audit_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: file_fotografi; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.file_fotografi (
    id integer NOT NULL,
    pemesanan_id integer NOT NULL,
    jenis_file public.jenis_file_fotografi NOT NULL,
    nama_file_asli character varying(255) NOT NULL,
    nama_file character varying(255) NOT NULL,
    path_file character varying(255) NOT NULL,
    ukuran_file bigint,
    mime_type character varying(100),
    status_file public.status_file_fotografi DEFAULT 'belum_tersedia'::public.status_file_fotografi NOT NULL,
    diunggah_oleh integer,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_nama_file_asli_not_empty CHECK (((nama_file_asli)::text <> ''::text)),
    CONSTRAINT chk_nama_file_not_empty CHECK (((nama_file)::text <> ''::text)),
    CONSTRAINT chk_path_file_not_empty CHECK (((path_file)::text <> ''::text)),
    CONSTRAINT chk_pengunggah_file CHECK ((((jenis_file = 'referensi'::public.jenis_file_fotografi) AND (diunggah_oleh IS NULL)) OR ((jenis_file = 'hasil'::public.jenis_file_fotografi) AND (diunggah_oleh IS NOT NULL)))),
    CONSTRAINT file_fotografi_ukuran_file_check CHECK ((ukuran_file > 0))
);


--
-- Name: TABLE file_fotografi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.file_fotografi IS 'File referensi dari pelanggan dan hasil dari pegawai';


--
-- Name: COLUMN file_fotografi.nama_file_asli; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.file_fotografi.nama_file_asli IS 'Nama file asli sebagaimana diunggah pengguna, misalnya IMG_12345.jpg, dipakai saat file diunduh kembali supaya pengguna melihat nama yang familiar';


--
-- Name: COLUMN file_fotografi.nama_file; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.file_fotografi.nama_file IS 'Nama file sebagaimana disimpan di server, biasanya sudah diacak untuk mencegah tabrakan nama dan path traversal, misalnya A92KLS83.jpg';


--
-- Name: COLUMN file_fotografi.path_file; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.file_fotografi.path_file IS 'Path relatif di storage/upload-pelanggan/ atau storage/hasil-foto/, tidak bisa diakses langsung lewat URL';


--
-- Name: COLUMN file_fotografi.ukuran_file; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.file_fotografi.ukuran_file IS 'Ukuran file dalam satuan byte, dipakai untuk validasi dan ditampilkan ke pengguna';


--
-- Name: COLUMN file_fotografi.mime_type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.file_fotografi.mime_type IS 'Tipe MIME file, misalnya image/jpeg, image/png, atau application/zip, dipakai saat proses download supaya header Content-Type benar';


--
-- Name: COLUMN file_fotografi.diunggah_oleh; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.file_fotografi.diunggah_oleh IS 'Kosong (NULL) kalau file diunggah pelanggan, berisi id pegawai kalau jenis_file = hasil';


--
-- Name: file_fotografi_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.file_fotografi ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.file_fotografi_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: layanan; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.layanan (
    id integer NOT NULL,
    nama character varying(150) NOT NULL,
    deskripsi text,
    harga numeric(12,2) NOT NULL,
    estimasi_durasi_menit integer,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    estimasi_hasil_hari integer,
    deliverables text,
    maks_orang integer,
    CONSTRAINT layanan_durasi_check CHECK ((estimasi_durasi_menit > 0)),
    CONSTRAINT layanan_estimasi_hasil_check CHECK ((estimasi_hasil_hari > 0)),
    CONSTRAINT layanan_harga_check CHECK ((harga >= (0)::numeric)),
    CONSTRAINT layanan_maks_orang_check CHECK ((maks_orang > 0)),
    CONSTRAINT layanan_nama_check CHECK (((nama)::text <> ''::text))
);


--
-- Name: TABLE layanan; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.layanan IS 'Paket/layanan fotografi yang ditawarkan Studio Flamboyan';


--
-- Name: COLUMN layanan.nama; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.layanan.nama IS 'Nama paket fotografi yang ditawarkan kepada pelanggan';


--
-- Name: COLUMN layanan.deskripsi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.layanan.deskripsi IS 'Informasi detail paket fotografi';


--
-- Name: COLUMN layanan.harga; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.layanan.harga IS 'Harga paket fotografi dalam Rupiah';


--
-- Name: COLUMN layanan.estimasi_durasi_menit; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.layanan.estimasi_durasi_menit IS 'Perkiraan lama sesi fotografi dalam satuan menit';


--
-- Name: COLUMN layanan.is_active; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.layanan.is_active IS 'Layanan nonaktif tidak ditampilkan di halaman publik';


--
-- Name: COLUMN layanan.estimasi_hasil_hari; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.layanan.estimasi_hasil_hari IS 'Estimasi hasil foto siap diterima pelanggan, dalam satuan hari (dihitung setelah sesi foto)';


--
-- Name: COLUMN layanan.deliverables; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.layanan.deliverables IS 'Poin hasil/output layanan, satu baris = satu poin. Dipisah dari deskripsi';


--
-- Name: COLUMN layanan.maks_orang; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.layanan.maks_orang IS 'Batas maksimal jumlah orang dalam satu sesi, boleh kosong (NULL = tidak dibatasi / tidak ditentukan)';


--
-- Name: layanan_gambar; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.layanan_gambar (
    id integer NOT NULL,
    layanan_id integer NOT NULL,
    nama_file_asli character varying(255) NOT NULL,
    nama_file character varying(255) NOT NULL,
    path_file character varying(255) NOT NULL,
    ukuran_file bigint,
    mime_type character varying(100),
    keterangan character varying(150),
    urutan integer DEFAULT 0 NOT NULL,
    is_cover boolean DEFAULT false NOT NULL,
    diunggah_oleh integer,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_layanan_gambar_nama_asli_not_empty CHECK (((nama_file_asli)::text <> ''::text)),
    CONSTRAINT chk_layanan_gambar_nama_not_empty CHECK (((nama_file)::text <> ''::text)),
    CONSTRAINT chk_layanan_gambar_path_not_empty CHECK (((path_file)::text <> ''::text)),
    CONSTRAINT layanan_gambar_ukuran_file_check CHECK ((ukuran_file > 0))
);


--
-- Name: TABLE layanan_gambar; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.layanan_gambar IS 'Galeri/contoh foto untuk satu layanan, ditampilkan di halaman publik';


--
-- Name: COLUMN layanan_gambar.path_file; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.layanan_gambar.path_file IS 'Path relatif gambar. Berbeda dengan file pelanggan, gambar layanan boleh publik (mis. public/assets/uploads/layanan/)';


--
-- Name: COLUMN layanan_gambar.urutan; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.layanan_gambar.urutan IS 'Urutan tampil, angka kecil tampil lebih dulu';


--
-- Name: COLUMN layanan_gambar.is_cover; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.layanan_gambar.is_cover IS 'Gambar utama yang tampil di kartu layanan';


--
-- Name: layanan_gambar_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.layanan_gambar ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.layanan_gambar_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: layanan_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.layanan ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.layanan_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: pelanggan; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pelanggan (
    id integer NOT NULL,
    nama character varying(100) NOT NULL,
    no_hp character varying(20) NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: TABLE pelanggan; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.pelanggan IS 'Data pelanggan yang melakukan pemesanan, diidentifikasi lewat nomor HP unik';


--
-- Name: pelanggan_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.pelanggan ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.pelanggan_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: pemesanan; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pemesanan (
    id integer NOT NULL,
    kode_unik character varying(50) NOT NULL,
    pelanggan_id integer NOT NULL,
    layanan_id integer NOT NULL,
    pegawai_id integer,
    tanggal_jadwal date NOT NULL,
    jam_jadwal time without time zone NOT NULL,
    catatan_khusus text,
    status public.status_pemesanan DEFAULT 'pemesanan_dibuat'::public.status_pemesanan NOT NULL,
    harga_saat_pesan numeric(12,2) NOT NULL,
    biaya_tambahan numeric(12,2) DEFAULT 0 NOT NULL,
    diskon numeric(12,2) DEFAULT 0 NOT NULL,
    total_tagihan numeric(12,2) GENERATED ALWAYS AS (((harga_saat_pesan + biaya_tambahan) - diskon)) STORED,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_kode_unik_not_empty CHECK (((kode_unik)::text <> ''::text)),
    CONSTRAINT chk_total_tagihan_non_negative CHECK ((total_tagihan >= (0)::numeric)),
    CONSTRAINT pemesanan_biaya_tambahan_check CHECK ((biaya_tambahan >= (0)::numeric)),
    CONSTRAINT pemesanan_diskon_check CHECK ((diskon >= (0)::numeric)),
    CONSTRAINT pemesanan_harga_saat_pesan_check CHECK ((harga_saat_pesan >= (0)::numeric))
);


--
-- Name: TABLE pemesanan; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.pemesanan IS 'Entitas utama yang menghubungkan pelanggan, layanan, dan jadwal';


--
-- Name: COLUMN pemesanan.kode_unik; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.pemesanan.kode_unik IS 'Dipakai pelanggan untuk akses tanpa login (tracking, upload, download)';


--
-- Name: COLUMN pemesanan.pegawai_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.pemesanan.pegawai_id IS 'Pegawai yang menangani pemesanan ini, boleh kosong di awal';


--
-- Name: COLUMN pemesanan.harga_saat_pesan; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.pemesanan.harga_saat_pesan IS 'Salinan harga dari tabel layanan pada saat booking dibuat, supaya perubahan harga layanan di kemudian hari tidak mengubah nilai pemesanan lama. Nilainya diisi oleh kode PHP saat proses booking (Tahap 7), bukan otomatis oleh database';


--
-- Name: COLUMN pemesanan.biaya_tambahan; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.pemesanan.biaya_tambahan IS 'Biaya tambahan di luar harga dasar layanan, misalnya cetak foto ekstra, defaultnya 0';


--
-- Name: COLUMN pemesanan.diskon; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.pemesanan.diskon IS 'Potongan harga jika ada, defaultnya 0';


--
-- Name: COLUMN pemesanan.total_tagihan; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.pemesanan.total_tagihan IS 'Dihitung otomatis oleh database dari harga_saat_pesan + biaya_tambahan - diskon, tidak bisa diisi manual lewat INSERT atau UPDATE';


--
-- Name: CONSTRAINT chk_total_tagihan_non_negative ON pemesanan; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON CONSTRAINT chk_total_tagihan_non_negative ON public.pemesanan IS 'Berjaga-jaga terhadap kasus diskon lebih besar dari harga plus biaya tambahan, yang akan membuat total_tagihan menjadi negatif';


--
-- Name: pemesanan_addon; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pemesanan_addon (
    id integer NOT NULL,
    pemesanan_id integer NOT NULL,
    addon_id integer NOT NULL,
    nama_saat_pesan character varying(150) NOT NULL,
    harga_saat_pesan numeric(12,2) NOT NULL,
    jumlah integer DEFAULT 1 NOT NULL,
    subtotal numeric(12,2) GENERATED ALWAYS AS ((harga_saat_pesan * (jumlah)::numeric)) STORED,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT pemesanan_addon_harga_saat_pesan_check CHECK ((harga_saat_pesan >= (0)::numeric)),
    CONSTRAINT pemesanan_addon_jumlah_check CHECK ((jumlah > 0))
);


--
-- Name: TABLE pemesanan_addon; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.pemesanan_addon IS 'Add-on yang dipilih pada suatu pemesanan (snapshot nama dan harga saat dipesan)';


--
-- Name: COLUMN pemesanan_addon.harga_saat_pesan; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.pemesanan_addon.harga_saat_pesan IS 'Salinan harga add-on saat booking, diisi kode PHP, supaya perubahan harga tidak mengubah pemesanan lama';


--
-- Name: COLUMN pemesanan_addon.subtotal; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.pemesanan_addon.subtotal IS 'Dihitung otomatis: harga_saat_pesan x jumlah';


--
-- Name: pemesanan_addon_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.pemesanan_addon ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.pemesanan_addon_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: pemesanan_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.pemesanan ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.pemesanan_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: transaksi_pembayaran; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.transaksi_pembayaran (
    id integer NOT NULL,
    pemesanan_id integer NOT NULL,
    metode_pembayaran public.metode_pembayaran NOT NULL,
    jumlah_bayar numeric(12,2) NOT NULL,
    nomor_referensi character varying(100),
    bukti_transfer character varying(255),
    status_pembayaran public.status_pembayaran DEFAULT 'menunggu_verifikasi'::public.status_pembayaran NOT NULL,
    diverifikasi_oleh integer,
    diverifikasi_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_bukti_transfer_wajib_untuk_transfer CHECK ((((metode_pembayaran = 'transfer'::public.metode_pembayaran) AND (bukti_transfer IS NOT NULL)) OR (metode_pembayaran = 'langsung'::public.metode_pembayaran))),
    CONSTRAINT chk_verifikasi_pembayaran_transfer CHECK (((status_pembayaran = 'menunggu_verifikasi'::public.status_pembayaran) OR ((status_pembayaran = ANY (ARRAY['lunas'::public.status_pembayaran, 'ditolak'::public.status_pembayaran])) AND (diverifikasi_oleh IS NOT NULL)))),
    CONSTRAINT transaksi_pembayaran_jumlah_bayar_check CHECK ((jumlah_bayar > (0)::numeric))
);


--
-- Name: TABLE transaksi_pembayaran; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.transaksi_pembayaran IS 'Data pembayaran, satu pemesanan hanya punya satu transaksi';


--
-- Name: COLUMN transaksi_pembayaran.nomor_referensi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.transaksi_pembayaran.nomor_referensi IS 'Nomor referensi transaksi dari bank atau catatan internal, opsional, dipakai Owner untuk mencocokkan mutasi rekening secara manual';


--
-- Name: COLUMN transaksi_pembayaran.bukti_transfer; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.transaksi_pembayaran.bukti_transfer IS 'Nama file bukti transfer, file fisik disimpan di storage/bukti-transfer/';


--
-- Name: transaksi_pembayaran_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.transaksi_pembayaran ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.transaksi_pembayaran_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id integer NOT NULL,
    nama character varying(100) NOT NULL,
    email character varying(100) NOT NULL,
    password character varying(255) NOT NULL,
    no_telepon character varying(20),
    role public.user_role DEFAULT 'pegawai'::public.user_role NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    last_login_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    reset_token character varying(255),
    reset_token_expires_at timestamp without time zone,
    CONSTRAINT users_email_check CHECK (((email)::text <> ''::text))
);


--
-- Name: TABLE users; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.users IS 'Akun internal Owner dan Pegawai Studio Flamboyan';


--
-- Name: COLUMN users.password; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.password IS 'Disimpan dalam bentuk hash bcrypt, tidak pernah plaintext';


--
-- Name: COLUMN users.no_telepon; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.no_telepon IS 'Informasi kontak';


--
-- Name: COLUMN users.last_login_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.last_login_at IS 'Wkatu login terakhir, diperbarui oleh AuthService setiap kali login berhasil, dipakai Owner untuk memantau aktivitas pegawai';


--
-- Name: COLUMN users.reset_token; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.reset_token IS 'Token sementara untuk fitur lupa password, dipakai di tahap 13';


--
-- Name: COLUMN users.reset_token_expires_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.reset_token_expires_at IS 'Batas waktu berlaku reset_token, dipakai di Tahap 13';


--
-- Name: CONSTRAINT users_email_check ON users; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON CONSTRAINT users_email_check ON public.users IS 'Mencegah string kosong lolos sebagai email, UNIQUE saja tidak menjamin ini karena string kosong dianggap satu nilai valid yang berbeda dari NULL';


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.users ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: v_detail_pembayaran; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.v_detail_pembayaran AS
 SELECT t.id AS transaksi_id,
    t.pemesanan_id,
    p.kode_unik,
    p.status AS status_pemesanan,
    pl.nama AS pelanggan_nama,
    pl.no_hp AS pelanggan_no_hp,
    l.nama AS layanan_nama,
    p.total_tagihan,
    t.metode_pembayaran,
    t.jumlah_bayar,
    (t.jumlah_bayar - p.total_tagihan) AS selisih_bayar,
    t.nomor_referensi,
    t.bukti_transfer,
    t.status_pembayaran,
    t.diverifikasi_oleh,
    v.nama AS diverifikator_nama,
    t.diverifikasi_at,
    t.created_at,
    t.updated_at
   FROM ((((public.transaksi_pembayaran t
     JOIN public.pemesanan p ON ((p.id = t.pemesanan_id)))
     JOIN public.pelanggan pl ON ((pl.id = p.pelanggan_id)))
     JOIN public.layanan l ON ((l.id = p.layanan_id)))
     LEFT JOIN public.users v ON ((v.id = t.diverifikasi_oleh)));


--
-- Name: VIEW v_detail_pembayaran; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON VIEW public.v_detail_pembayaran IS 'Detail transaksi pembayaran beserta pemesanan, pelanggan, dan verifikator. Dipakai halaman verifikasi transaksi pegawai dan laporan pendapatan.';


--
-- Name: v_detail_pemesanan; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.v_detail_pemesanan AS
 SELECT p.id AS pemesanan_id,
    p.kode_unik,
    p.status,
    p.tanggal_jadwal,
    p.jam_jadwal,
    (p.jam_jadwal + ((((COALESCE(l.estimasi_durasi_menit, 60) + COALESCE(ad.tambahan_durasi, (0)::bigint)))::integer)::double precision * '00:01:00'::interval)) AS jam_selesai,
    p.catatan_khusus,
    p.harga_saat_pesan,
    p.biaya_tambahan,
    p.diskon,
    p.total_tagihan,
    pl.id AS pelanggan_id,
    pl.nama AS pelanggan_nama,
    pl.no_hp AS pelanggan_no_hp,
    l.id AS layanan_id,
    l.nama AS layanan_nama,
    l.estimasi_durasi_menit,
    u.id AS pegawai_id,
    u.nama AS pegawai_nama,
    t.id AS transaksi_id,
    t.metode_pembayaran,
    t.jumlah_bayar,
    t.status_pembayaran,
    t.diverifikasi_at,
    p.created_at,
    p.updated_at,
    ((COALESCE(l.estimasi_durasi_menit, 60) + COALESCE(ad.tambahan_durasi, (0)::bigint)))::integer AS durasi_total_menit,
    (COALESCE(ad.jumlah_addon, (0)::bigint))::integer AS jumlah_addon
   FROM (((((public.pemesanan p
     JOIN public.pelanggan pl ON ((pl.id = p.pelanggan_id)))
     JOIN public.layanan l ON ((l.id = p.layanan_id)))
     LEFT JOIN public.users u ON ((u.id = p.pegawai_id)))
     LEFT JOIN public.transaksi_pembayaran t ON ((t.pemesanan_id = p.id)))
     LEFT JOIN ( SELECT pa.pemesanan_id,
            sum((a.tambahan_durasi_menit * pa.jumlah)) AS tambahan_durasi,
            count(*) AS jumlah_addon
           FROM (public.pemesanan_addon pa
             JOIN public.addon_layanan a ON ((a.id = pa.addon_id)))
          GROUP BY pa.pemesanan_id) ad ON ((ad.pemesanan_id = p.id)));


--
-- Name: VIEW v_detail_pemesanan; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON VIEW public.v_detail_pemesanan IS 'Detail lengkap pemesanan (pelanggan, layanan, pegawai, ringkasan pembayaran). Dipakai halaman daftar/detail pemesanan pegawai dan tracking.';


--
-- Name: workflow_layanan; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workflow_layanan (
    id integer NOT NULL,
    pemesanan_id integer NOT NULL,
    status_sebelumnya public.status_pemesanan,
    status public.status_pemesanan NOT NULL,
    catatan text,
    diubah_oleh integer,
    created_at timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: TABLE workflow_layanan; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.workflow_layanan IS 'Riwayat setiap perubahan status pemesanan, bersifat log historis';


--
-- Name: COLUMN workflow_layanan.status_sebelumnya; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.workflow_layanan.status_sebelumnya IS 'Status sebelum perubahan ini terjadi, kosong (NULL) untuk entri pertama karena belum ada status sebelumnya';


--
-- Name: COLUMN workflow_layanan.status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.workflow_layanan.status IS 'Status baru setelah perubahan dilakukan';


--
-- Name: COLUMN workflow_layanan.catatan; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.workflow_layanan.catatan IS 'Catatan tambahan terkait perubahan status, bersifat opsional';


--
-- Name: COLUMN workflow_layanan.diubah_oleh; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.workflow_layanan.diubah_oleh IS 'User (Owner atau Pegawai) yang melakukan perubahan status';


--
-- Name: COLUMN workflow_layanan.created_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.workflow_layanan.created_at IS 'Waktu perubahan status dicatat ke dalam sistem';


--
-- Name: v_riwayat_workflow; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.v_riwayat_workflow AS
 SELECT w.id AS workflow_id,
    w.pemesanan_id,
    p.kode_unik,
    pl.nama AS pelanggan_nama,
    row_number() OVER (PARTITION BY w.pemesanan_id ORDER BY w.created_at, w.id) AS urutan_ke,
    w.status_sebelumnya,
    w.status,
    w.catatan,
    w.diubah_oleh,
    u.nama AS diubah_oleh_nama,
    u.role AS diubah_oleh_role,
    w.created_at
   FROM (((public.workflow_layanan w
     JOIN public.pemesanan p ON ((p.id = w.pemesanan_id)))
     JOIN public.pelanggan pl ON ((pl.id = p.pelanggan_id)))
     LEFT JOIN public.users u ON ((u.id = w.diubah_oleh)));


--
-- Name: VIEW v_riwayat_workflow; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON VIEW public.v_riwayat_workflow IS 'Riwayat perubahan status pemesanan. diubah_oleh NULL = dibuat pelanggan/sistem. Dipakai detail pemesanan, tracking pelanggan, dan audit.';


--
-- Name: workflow_layanan_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.workflow_layanan ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.workflow_layanan_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Data for Name: addon_layanan; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.addon_layanan (id, layanan_id, nama, deskripsi, harga, tambahan_durasi_menit, is_active, created_at, updated_at) FROM stdin;
1	10	Tambah File Edit	Tambahan file hasil editing foto	50000.00	15	t	2026-10-14 10:06:53.651732	2026-10-14 10:06:53.651732
2	10	Edit Prioritas	Pengerjaan editing lebih cepat	75000.00	0	t	2026-10-14 10:06:53.651732	2026-10-14 10:06:53.651732
3	11	Tambah Jam Sesi	Tambahan waktu pemotretan di lokasi	100000.00	60	t	2026-10-14 10:06:53.651732	2026-10-14 10:06:53.651732
4	11	Lokasi Tambahan	Tambahan satu lokasi pemotretan	150000.00	45	t	2026-10-14 10:06:53.651732	2026-10-14 10:06:53.651732
5	13	Cetak Foto Tambahan	Tambahan cetak foto ukuran standar	35000.00	0	t	2026-10-14 10:06:53.651732	2026-10-14 10:06:53.651732
6	13	Tambah Orang	Tambahan satu orang dalam sesi foto	25000.00	10	t	2026-10-14 10:06:53.651732	2026-10-14 10:06:53.651732
\.


--
-- Data for Name: audit_log; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.audit_log (id, user_id, modul, aktivitas, detail, ip_address, created_at) FROM stdin;
\.


--
-- Data for Name: file_fotografi; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.file_fotografi (id, pemesanan_id, jenis_file, nama_file_asli, nama_file, path_file, ukuran_file, mime_type, status_file, diunggah_oleh, created_at) FROM stdin;
\.


--
-- Data for Name: layanan; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.layanan (id, nama, deskripsi, harga, estimasi_durasi_menit, is_active, created_at, updated_at, estimasi_hasil_hari, deliverables, maks_orang) FROM stdin;
11	Home Service	1 hasil foto\r\n1 soft file	80000.00	90	t	2026-10-08 08:27:54.858132	2026-10-13 21:38:02.272248	\N	1 hasil foto\r\n1 soft file	\N
13	Pas Foto	1 Hard File\r\n2 Soft File	35000.00	30	f	2026-10-09 15:34:42.28414	2026-10-13 21:38:02.272248	\N	1 Hard File\r\n2 Soft File	\N
10	Foto Editing	1 hasil Editing	30000.00	120	t	2026-10-07 17:42:37.453916	2026-10-13 21:38:02.272248	\N	1 hasil Editing	\N
\.


--
-- Data for Name: layanan_gambar; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.layanan_gambar (id, layanan_id, nama_file_asli, nama_file, path_file, ukuran_file, mime_type, keterangan, urutan, is_cover, diunggah_oleh, created_at) FROM stdin;
\.


--
-- Data for Name: pelanggan; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.pelanggan (id, nama, no_hp, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: pemesanan; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.pemesanan (id, kode_unik, pelanggan_id, layanan_id, pegawai_id, tanggal_jadwal, jam_jadwal, catatan_khusus, status, harga_saat_pesan, biaya_tambahan, diskon, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: pemesanan_addon; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.pemesanan_addon (id, pemesanan_id, addon_id, nama_saat_pesan, harga_saat_pesan, jumlah, created_at) FROM stdin;
\.


--
-- Data for Name: transaksi_pembayaran; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.transaksi_pembayaran (id, pemesanan_id, metode_pembayaran, jumlah_bayar, nomor_referensi, bukti_transfer, status_pembayaran, diverifikasi_oleh, diverifikasi_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, nama, email, password, no_telepon, role, is_active, last_login_at, created_at, updated_at, reset_token, reset_token_expires_at) FROM stdin;
1	Sitta Laila Safitri	owner@simasif.local	$2y$12$ig/pYxguLEsZwi4AonHFvOBfMFpLHObSuvPI1XALNKSE6GJ2crMt6	\N	owner	t	2026-10-09 08:52:41.685441	2026-09-30 11:10:29.905059	2026-10-09 08:52:41.685441	\N	\N
2	Pegawai Demo	pegawai@simasif.local	$2y$12$ig/pYxguLEsZwi4AonHFvOBfMFpLHObSuvPI1XALNKSE6GJ2crMt6	\N	pegawai	t	2026-10-13 21:47:33.087976	2026-09-30 11:10:29.905059	2026-10-13 21:47:33.087976	\N	\N
\.


--
-- Data for Name: workflow_layanan; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.workflow_layanan (id, pemesanan_id, status_sebelumnya, status, catatan, diubah_oleh, created_at) FROM stdin;
\.


--
-- Name: addon_layanan_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.addon_layanan_id_seq', 6, true);


--
-- Name: audit_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.audit_log_id_seq', 1, false);


--
-- Name: file_fotografi_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.file_fotografi_id_seq', 1, false);


--
-- Name: layanan_gambar_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.layanan_gambar_id_seq', 1, false);


--
-- Name: layanan_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.layanan_id_seq', 13, true);


--
-- Name: pelanggan_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.pelanggan_id_seq', 3, true);


--
-- Name: pemesanan_addon_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.pemesanan_addon_id_seq', 1, false);


--
-- Name: pemesanan_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.pemesanan_id_seq', 1, false);


--
-- Name: transaksi_pembayaran_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.transaksi_pembayaran_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 2, true);


--
-- Name: workflow_layanan_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.workflow_layanan_id_seq', 1, false);


--
-- Name: addon_layanan addon_layanan_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.addon_layanan
    ADD CONSTRAINT addon_layanan_pkey PRIMARY KEY (id);


--
-- Name: audit_log audit_log_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_log
    ADD CONSTRAINT audit_log_pkey PRIMARY KEY (id);


--
-- Name: file_fotografi file_fotografi_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.file_fotografi
    ADD CONSTRAINT file_fotografi_pkey PRIMARY KEY (id);


--
-- Name: layanan_gambar layanan_gambar_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.layanan_gambar
    ADD CONSTRAINT layanan_gambar_pkey PRIMARY KEY (id);


--
-- Name: layanan layanan_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.layanan
    ADD CONSTRAINT layanan_pkey PRIMARY KEY (id);


--
-- Name: pelanggan pelanggan_no_hp_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pelanggan
    ADD CONSTRAINT pelanggan_no_hp_key UNIQUE (no_hp);


--
-- Name: pelanggan pelanggan_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pelanggan
    ADD CONSTRAINT pelanggan_pkey PRIMARY KEY (id);


--
-- Name: pemesanan_addon pemesanan_addon_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pemesanan_addon
    ADD CONSTRAINT pemesanan_addon_pkey PRIMARY KEY (id);


--
-- Name: pemesanan pemesanan_kode_unik_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pemesanan
    ADD CONSTRAINT pemesanan_kode_unik_key UNIQUE (kode_unik);


--
-- Name: pemesanan pemesanan_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pemesanan
    ADD CONSTRAINT pemesanan_pkey PRIMARY KEY (id);


--
-- Name: transaksi_pembayaran transaksi_pembayaran_pemesanan_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transaksi_pembayaran
    ADD CONSTRAINT transaksi_pembayaran_pemesanan_id_key UNIQUE (pemesanan_id);


--
-- Name: transaksi_pembayaran transaksi_pembayaran_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transaksi_pembayaran
    ADD CONSTRAINT transaksi_pembayaran_pkey PRIMARY KEY (id);


--
-- Name: addon_layanan uq_addon_layanan_nama; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.addon_layanan
    ADD CONSTRAINT uq_addon_layanan_nama UNIQUE (layanan_id, nama);


--
-- Name: pemesanan_addon uq_pemesanan_addon; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pemesanan_addon
    ADD CONSTRAINT uq_pemesanan_addon UNIQUE (pemesanan_id, addon_id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: workflow_layanan workflow_layanan_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflow_layanan
    ADD CONSTRAINT workflow_layanan_pkey PRIMARY KEY (id);


--
-- Name: idx_addon_layanan_layanan; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_addon_layanan_layanan ON public.addon_layanan USING btree (layanan_id);


--
-- Name: idx_audit_created; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_audit_created ON public.audit_log USING btree (created_at);


--
-- Name: idx_audit_modul; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_audit_modul ON public.audit_log USING btree (modul);


--
-- Name: idx_audit_user; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_audit_user ON public.audit_log USING btree (user_id);


--
-- Name: idx_file_jenis; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_file_jenis ON public.file_fotografi USING btree (jenis_file);


--
-- Name: idx_file_pemesanan; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_file_pemesanan ON public.file_fotografi USING btree (pemesanan_id);


--
-- Name: idx_file_pengunggah; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_file_pengunggah ON public.file_fotografi USING btree (diunggah_oleh);


--
-- Name: idx_layanan_gambar_layanan; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_layanan_gambar_layanan ON public.layanan_gambar USING btree (layanan_id, urutan);


--
-- Name: idx_pemesanan_addon_addon; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pemesanan_addon_addon ON public.pemesanan_addon USING btree (addon_id);


--
-- Name: idx_pemesanan_jadwal; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pemesanan_jadwal ON public.pemesanan USING btree (tanggal_jadwal, jam_jadwal);


--
-- Name: idx_pemesanan_pegawai; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pemesanan_pegawai ON public.pemesanan USING btree (pegawai_id);


--
-- Name: idx_pemesanan_pelanggan; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pemesanan_pelanggan ON public.pemesanan USING btree (pelanggan_id);


--
-- Name: idx_pemesanan_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pemesanan_status ON public.pemesanan USING btree (status);


--
-- Name: idx_transaksi_pembayaran_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_transaksi_pembayaran_status ON public.transaksi_pembayaran USING btree (status_pembayaran);


--
-- Name: idx_transaksi_pembayaran_verifikasi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_transaksi_pembayaran_verifikasi ON public.transaksi_pembayaran USING btree (diverifikasi_oleh);


--
-- Name: idx_workflow_created_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_workflow_created_at ON public.workflow_layanan USING btree (created_at);


--
-- Name: idx_workflow_diubah_oleh; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_workflow_diubah_oleh ON public.workflow_layanan USING btree (diubah_oleh);


--
-- Name: idx_workflow_pemesanan; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_workflow_pemesanan ON public.workflow_layanan USING btree (pemesanan_id);


--
-- Name: uq_layanan_gambar_cover; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_layanan_gambar_cover ON public.layanan_gambar USING btree (layanan_id) WHERE is_cover;


--
-- Name: addon_layanan set_updated_at_addon_layanan; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER set_updated_at_addon_layanan BEFORE UPDATE ON public.addon_layanan FOR EACH ROW EXECUTE FUNCTION public.trigger_set_updated_at();


--
-- Name: layanan set_updated_at_layanan; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER set_updated_at_layanan BEFORE UPDATE ON public.layanan FOR EACH ROW EXECUTE FUNCTION public.trigger_set_updated_at();


--
-- Name: pelanggan set_updated_at_pelanggan; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER set_updated_at_pelanggan BEFORE UPDATE ON public.pelanggan FOR EACH ROW EXECUTE FUNCTION public.trigger_set_updated_at();


--
-- Name: pemesanan set_updated_at_pemesanan; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER set_updated_at_pemesanan BEFORE UPDATE ON public.pemesanan FOR EACH ROW EXECUTE FUNCTION public.trigger_set_updated_at();


--
-- Name: transaksi_pembayaran set_updated_at_transaksi_pembayaran; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER set_updated_at_transaksi_pembayaran BEFORE UPDATE ON public.transaksi_pembayaran FOR EACH ROW EXECUTE FUNCTION public.trigger_set_updated_at();


--
-- Name: users set_updated_at_users; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER set_updated_at_users BEFORE UPDATE ON public.users FOR EACH ROW EXECUTE FUNCTION public.trigger_set_updated_at();


--
-- Name: pemesanan_addon sync_biaya_tambahan; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER sync_biaya_tambahan AFTER INSERT OR DELETE OR UPDATE ON public.pemesanan_addon FOR EACH ROW EXECUTE FUNCTION public.trigger_sync_biaya_tambahan();


--
-- Name: addon_layanan addon_layanan_layanan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.addon_layanan
    ADD CONSTRAINT addon_layanan_layanan_id_fkey FOREIGN KEY (layanan_id) REFERENCES public.layanan(id) ON DELETE CASCADE;


--
-- Name: audit_log audit_log_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_log
    ADD CONSTRAINT audit_log_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: file_fotografi file_fotografi_diunggah_oleh_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.file_fotografi
    ADD CONSTRAINT file_fotografi_diunggah_oleh_fkey FOREIGN KEY (diunggah_oleh) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: file_fotografi file_fotografi_pemesanan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.file_fotografi
    ADD CONSTRAINT file_fotografi_pemesanan_id_fkey FOREIGN KEY (pemesanan_id) REFERENCES public.pemesanan(id) ON DELETE CASCADE;


--
-- Name: layanan_gambar layanan_gambar_diunggah_oleh_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.layanan_gambar
    ADD CONSTRAINT layanan_gambar_diunggah_oleh_fkey FOREIGN KEY (diunggah_oleh) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: layanan_gambar layanan_gambar_layanan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.layanan_gambar
    ADD CONSTRAINT layanan_gambar_layanan_id_fkey FOREIGN KEY (layanan_id) REFERENCES public.layanan(id) ON DELETE CASCADE;


--
-- Name: pemesanan_addon pemesanan_addon_addon_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pemesanan_addon
    ADD CONSTRAINT pemesanan_addon_addon_id_fkey FOREIGN KEY (addon_id) REFERENCES public.addon_layanan(id) ON DELETE RESTRICT;


--
-- Name: pemesanan_addon pemesanan_addon_pemesanan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pemesanan_addon
    ADD CONSTRAINT pemesanan_addon_pemesanan_id_fkey FOREIGN KEY (pemesanan_id) REFERENCES public.pemesanan(id) ON DELETE CASCADE;


--
-- Name: pemesanan pemesanan_layanan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pemesanan
    ADD CONSTRAINT pemesanan_layanan_id_fkey FOREIGN KEY (layanan_id) REFERENCES public.layanan(id) ON DELETE RESTRICT;


--
-- Name: pemesanan pemesanan_pegawai_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pemesanan
    ADD CONSTRAINT pemesanan_pegawai_id_fkey FOREIGN KEY (pegawai_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: pemesanan pemesanan_pelanggan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pemesanan
    ADD CONSTRAINT pemesanan_pelanggan_id_fkey FOREIGN KEY (pelanggan_id) REFERENCES public.pelanggan(id) ON DELETE RESTRICT;


--
-- Name: transaksi_pembayaran transaksi_pembayaran_diverifikasi_oleh_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transaksi_pembayaran
    ADD CONSTRAINT transaksi_pembayaran_diverifikasi_oleh_fkey FOREIGN KEY (diverifikasi_oleh) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: transaksi_pembayaran transaksi_pembayaran_pemesanan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transaksi_pembayaran
    ADD CONSTRAINT transaksi_pembayaran_pemesanan_id_fkey FOREIGN KEY (pemesanan_id) REFERENCES public.pemesanan(id) ON DELETE CASCADE;


--
-- Name: workflow_layanan workflow_layanan_diubah_oleh_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflow_layanan
    ADD CONSTRAINT workflow_layanan_diubah_oleh_fkey FOREIGN KEY (diubah_oleh) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: workflow_layanan workflow_layanan_pemesanan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflow_layanan
    ADD CONSTRAINT workflow_layanan_pemesanan_id_fkey FOREIGN KEY (pemesanan_id) REFERENCES public.pemesanan(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict UGGhvPM8QUxsTsvcEQdBcS0bJJiDuSbOdrI30GQHhcNw4YQM5Or9ygoyxhn9Ww0

