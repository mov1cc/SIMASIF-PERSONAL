<?php
use App\Helpers\FormatHelper as F;

/** @var array $layanan */

$title = $layanan['nama'];

$baris = preg_split('/\R/u', (string) $layanan['deskripsi']) ?: [];
$poin  = array_values(array_filter(array_map('trim', $baris), fn ($b) => $b !== ''));

ob_start();
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/layanan">Layanan</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= F::e($layanan['nama']) ?></li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">

                <h1 class="h3 fw-bold mb-2"><?= F::e($layanan['nama']) ?></h1>

                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    <span class="fs-3 fw-bold"><?= F::e(F::rupiah($layanan['harga'])) ?></span>
                    <span class="text-muted">
                        <i class="bi bi-clock"></i>
                        Estimasi durasi: <?= F::e(F::durasi($layanan['estimasi_durasi_menit'])) ?>
                    </span>
                </div>

                <h2 class="h6 text-uppercase text-muted mb-3">Yang Anda Dapatkan</h2>

                <?php if (empty($poin)): ?>
                    <p class="text-muted">Belum ada deskripsi untuk layanan ini.</p>
                <?php elseif (count($poin) === 1): ?>
                    <p><?= F::e($poin[0]) ?></p>
                <?php else: ?>
                    <ul class="list-unstyled mb-4">
                        <?php foreach ($poin as $p): ?>
                            <li class="d-flex gap-2 mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                <span><?= F::e($p) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a href="/pemesanan?layanan_id=<?= (int) $layanan['id'] ?>" class="btn btn-dark">
                        <i class="bi bi-calendar-plus"></i> Pesan Layanan Ini
                    </a>
                    <a href="/layanan" class="btn btn-outline-secondary">Kembali ke Daftar</a>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

require dirname(__DIR__, 2) . '/layouts/guest.php';