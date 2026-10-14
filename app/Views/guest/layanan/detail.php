<?php
use App\Helpers\FormatHelper as F;

/** @var array $layanan */
/** @var array $addons */

$title = $layanan['nama'];
$poin  = F::poin($layanan['deliverables'] ?? null);

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

                <div class="fs-3 fw-bold mb-2"><?= F::e(F::rupiah($layanan['harga'])) ?></div>

                <div class="d-flex flex-wrap gap-3 text-muted mb-4">
                    <span><i class="bi bi-clock"></i>
                        Durasi sesi: <?= F::e(F::durasi($layanan['estimasi_durasi_menit'])) ?></span>
                    <span><i class="bi bi-hourglass-split"></i>
                        Hasil siap: <?= F::e(F::hari($layanan['estimasi_hasil_hari'])) ?></span>
                    <?php if (!empty($layanan['maks_orang'])): ?>
                        <span><i class="bi bi-people"></i>
                            <?= F::e(F::maksOrang($layanan['maks_orang'])) ?></span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($layanan['deskripsi'])): ?>
                    <p class="mb-4"><?= nl2br(F::e($layanan['deskripsi'])) ?></p>
                <?php endif; ?>

                <h2 class="h6 text-uppercase text-muted mb-3">Yang Anda Dapatkan</h2>

                <?php if (empty($poin)): ?>
                    <p class="text-muted">Belum ada rincian hasil untuk layanan ini.</p>
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

                <?php if (!empty($addons)): ?>
                    <h2 class="h6 text-uppercase text-muted mb-3">Add-on Tersedia</h2>
                    <ul class="list-group mb-4">
                        <?php foreach ($addons as $a): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold"><?= F::e($a['nama']) ?></div>
                                    <?php if (!empty($a['deskripsi'])): ?>
                                        <div class="small text-muted"><?= F::e($a['deskripsi']) ?></div>
                                    <?php endif; ?>
                                    <?php if ((int) $a['tambahan_durasi_menit'] > 0): ?>
                                        <div class="small text-muted">
                                            +<?= (int) $a['tambahan_durasi_menit'] ?> menit sesi
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="text-nowrap fw-semibold">+ <?= F::e(F::rupiah($a['harga'])) ?></span>
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