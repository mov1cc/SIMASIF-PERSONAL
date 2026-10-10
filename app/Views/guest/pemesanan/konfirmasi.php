<?php
use App\Helpers\FormatHelper as F;

/** @var array $pemesanan */

$title   = 'Pemesanan Berhasil';
$scripts = ['/assets/js/modules/booking.js'];

$tanggal = date('d/m/Y', strtotime($pemesanan['tanggal_jadwal']));
$jam     = substr((string) $pemesanan['jam_jadwal'], 0, 5);

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-6">

        <div class="card shadow-sm">
            <div class="card-body p-4 text-center">

                <i class="bi bi-check-circle text-success fs-1"></i>
                <h1 class="h4 fw-bold mt-2">Pemesanan Berhasil</h1>
                <p class="text-muted">
                    Simpan kode unik ini. Kode dipakai untuk memantau status, membayar,
                    mengunggah file, dan mengunduh hasil foto.
                </p>

                <div class="border rounded-3 py-3 my-3 bg-light">
                    <div class="small text-muted">Kode Pemesanan</div>
                    <div class="display-6 fw-bold font-monospace" id="kodeUnik">
                        <?= F::e($pemesanan['kode_unik']) ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-dark mt-2"
                            data-copy-target="#kodeUnik">
                        <i class="bi bi-clipboard"></i> <span>Salin kode</span>
                    </button>
                </div>

                <dl class="row text-start small mb-0">
                    <dt class="col-5 text-muted fw-normal">Nama</dt>
                    <dd class="col-7"><?= F::e($pemesanan['pelanggan_nama']) ?></dd>

                    <dt class="col-5 text-muted fw-normal">Layanan</dt>
                    <dd class="col-7"><?= F::e($pemesanan['layanan_nama']) ?></dd>

                    <dt class="col-5 text-muted fw-normal">Jadwal</dt>
                    <dd class="col-7"><?= F::e($tanggal) ?>, <?= F::e($jam) ?> WIB</dd>

                    <dt class="col-5 text-muted fw-normal">Estimasi durasi</dt>
                    <dd class="col-7"><?= F::e(F::durasi($pemesanan['estimasi_durasi_menit'])) ?></dd>

                    <dt class="col-5 text-muted fw-normal">Total tagihan</dt>
                    <dd class="col-7 fw-semibold"><?= F::e(F::rupiah($pemesanan['total_tagihan'])) ?></dd>

                    <?php if (!empty($pemesanan['catatan_khusus'])): ?>
                        <dt class="col-5 text-muted fw-normal">Catatan</dt>
                        <dd class="col-7"><?= F::e($pemesanan['catatan_khusus']) ?></dd>
                    <?php endif; ?>
                </dl>

            </div>
        </div>

        <div class="text-center mt-3">
            <a href="/layanan" class="btn btn-outline-secondary">Kembali ke Layanan</a>
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();

require dirname(__DIR__, 2) . '/layouts/guest.php';