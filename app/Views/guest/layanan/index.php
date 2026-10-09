<?php
use App\Helpers\FormatHelper as F;

/** @var array $items */

$title = 'Layanan Fotografi';

/** Satu baris deskripsi = satu poin deliverables */
$parsePoin = function (?string $deskripsi): array {
    $baris = preg_split('/\R/u', (string) $deskripsi) ?: [];

    return array_values(array_filter(array_map('trim', $baris), fn ($b) => $b !== ''));
};

ob_start();
?>

<div class="text-center mb-5">
    <h1 class="fw-bold">Layanan Studio Flamboyan</h1>
    <p class="text-muted mb-0">
        Pilih paket fotografi yang sesuai kebutuhan Anda, lalu lakukan pemesanan tanpa perlu membuat akun.
    </p>
</div>

<?php if (empty($items)): ?>
    <div class="card shadow-sm">
        <div class="card-body text-center text-muted py-5">
            Belum ada layanan yang tersedia saat ini.
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($items as $row): ?>
            <?php
            $poin   = $parsePoin($row['deskripsi']);
            $tampil = array_slice($poin, 0, 3);
            $sisa   = count($poin) - count($tampil);
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">

                        <h5 class="card-title fw-semibold mb-1"><?= F::e($row['nama']) ?></h5>

                        <div class="fs-4 fw-bold mb-2">
                            <?= F::e(F::rupiah($row['harga'])) ?>
                        </div>

                        <div class="text-muted small mb-3">
                            <i class="bi bi-clock"></i>
                            <?= F::e(F::durasi($row['estimasi_durasi_menit'])) ?>
                        </div>

                        <?php if (!empty($tampil)): ?>
                            <ul class="list-unstyled small mb-3">
                                <?php foreach ($tampil as $p): ?>
                                    <li class="d-flex gap-2 mb-1">
                                        <i class="bi bi-check-circle text-success"></i>
                                        <span><?= F::e($p) ?></span>
                                    </li>
                                <?php endforeach; ?>
                                <?php if ($sisa > 0): ?>
                                    <li class="text-muted">+<?= $sisa ?> poin lainnya</li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>

                        <div class="mt-auto d-flex gap-2">
                            <a href="/layanan/detail?id=<?= (int) $row['id'] ?>"
                               class="btn btn-outline-dark flex-fill">Lihat Detail</a>
                            <a href="/pemesanan?layanan_id=<?= (int) $row['id'] ?>"
                               class="btn btn-dark flex-fill">Pesan</a>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();

require dirname(__DIR__, 2) . '/layouts/guest.php';