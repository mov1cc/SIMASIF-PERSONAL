<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Pelanggan;
use App\Models\Pemesanan;
use DateTimeImmutable;
use PDO;
use PDOException;

class PemesananService
{
    /** Jam operasional (sesuaikan dengan mitra) */
    public const JAM_BUKA  = '09:00';
    public const JAM_TUTUP = '17:00';

    /** Dipakai kalau layanan tidak punya estimasi durasi */
    public const DURASI_DEFAULT = 60;

    /** Percobaan ulang kalau terjadi unique_violation (race condition) */
    private const MAX_RETRY = 3;

    private PDO $db;
    private Pemesanan $pemesanan;
    private Pelanggan $pelanggan;
    private KodeUnikService $kodeUnik;

    public function __construct()
    {
        $this->db        = Database::getInstance()->getConnection();
        $this->pemesanan = new Pemesanan();
        $this->pelanggan = new Pelanggan();
        $this->kodeUnik  = new KodeUnikService($this->pemesanan);
    }

    public static function durasiMenit(array $layanan): int
    {
        $durasi = (int) ($layanan['estimasi_durasi_menit'] ?? 0);

        return $durasi > 0 ? $durasi : self::DURASI_DEFAULT;
    }

    /**
     * Validasi aturan jadwal. Mengembalikan pesan error, atau null kalau valid.
     *
     * @param string $tanggal Y-m-d
     * @param string $jam     H:i
     */
    public function validasiJadwal(string $tanggal, string $jam, int $durasi): ?string
    {
        $mulai  = DateTimeImmutable::createFromFormat('!Y-m-d H:i', "{$tanggal} {$jam}");
        $galat  = DateTimeImmutable::getLastErrors();

        if ($mulai === false || !empty($galat['warning_count']) || !empty($galat['error_count'])) {
            return 'Tanggal atau jam tidak valid.';
        }

        if ($mulai <= new DateTimeImmutable('now')) {
            return 'Jadwal harus di waktu yang akan datang.';
        }

        $selesai = $mulai->modify("+{$durasi} minutes");
        $buka    = DateTimeImmutable::createFromFormat('!Y-m-d H:i', "{$tanggal} " . self::JAM_BUKA);
        $tutup   = DateTimeImmutable::createFromFormat('!Y-m-d H:i', "{$tanggal} " . self::JAM_TUTUP);

        if ($mulai < $buka || $selesai > $tutup) {
            return sprintf(
                'Sesi harus berada dalam jam operasional %s - %s (termasuk durasi sesi %d menit).',
                self::JAM_BUKA,
                self::JAM_TUTUP,
                $durasi
            );
        }

        return null;
    }

    /**
     * Buat pemesanan (sekaligus pelanggan kalau belum ada) dalam satu transaksi.
     *
     * @param array{layanan_id:int|string, nama:string, no_hp:string,
     *              tanggal:string, jam:string, catatan:string} $input
     *        no_hp sudah dinormalisasi, jam berformat H:i
     * @param array $layanan baris layanan aktif (dari Layanan::findActiveById)
     *
     * @return array{ok:bool, kode?:string, error?:string}
     *         error: 'bentrok' kalau jadwal sudah terisi
     */
    public function buatBooking(array $input, array $layanan): array
    {
        $durasi  = self::durasiMenit($layanan);
        $mulai   = $input['jam'] . ':00';
        $selesai = (new DateTimeImmutable("{$input['tanggal']} {$mulai}"))
            ->modify("+{$durasi} minutes")
            ->format('H:i:s');

        for ($percobaan = 1; $percobaan <= self::MAX_RETRY; $percobaan++) {
            try {
                $this->db->beginTransaction();

                $this->pemesanan->kunciTanggal($input['tanggal']);

                if ($this->pemesanan->adaBentrok($input['tanggal'], $mulai, $selesai)) {
                    $this->db->rollBack();

                    return ['ok' => false, 'error' => 'bentrok'];
                }

                // Pelanggan dikenali lewat no HP. Kalau sudah ada, nama lama
                // tidak ditimpa (supaya orang lain tidak bisa mengubah data pelanggan).
                $pelanggan   = $this->pelanggan->findByNoHp($input['no_hp']);
                $pelangganId = $pelanggan !== null
                    ? (int) $pelanggan['id']
                    : $this->pelanggan->create([
                        'nama'  => $input['nama'],
                        'no_hp' => $input['no_hp'],
                    ]);

                $kode = $this->kodeUnik->generate();

                $baris = $this->pemesanan->insert([
                    'kode_unik'    => $kode,
                    'pelanggan_id' => $pelangganId,
                    'layanan_id'   => (int) $layanan['id'],
                    'tanggal'      => $input['tanggal'],
                    'jam'          => $mulai,
                    'catatan'      => $input['catatan'] !== '' ? $input['catatan'] : null,
                    'harga'        => (string) $layanan['harga'], // snapshot harga saat pesan
                ]);

                $this->pemesanan->catatWorkflowAwal($baris['id']);

                $this->db->commit();

                return ['ok' => true, 'kode' => $baris['kode_unik']];
            } catch (PDOException $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }

                // 23505 = unique_violation (kode_unik / no_hp tabrakan karena
                // request bersamaan) -> ulangi seluruh transaksi
                if ((string) $e->getCode() === '23505' && $percobaan < self::MAX_RETRY) {
                    continue;
                }

                throw $e;
            }
        }

        throw new \RuntimeException('Gagal menyimpan pemesanan.');
    }
}