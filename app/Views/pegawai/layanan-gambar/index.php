<?php
use App\Helpers\CsrfHelper;
use App\Helpers\FormatHelper as F;

/** @var array $layanan */
/** @var array $items */
/** @var int $maks */
/** @var int|float $maksMb */

$title  = 'Galeri ' . $layanan['nama'];
$penuh  = count($items) >= $maks;

ob_start();
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/pegawai/layanan">Layanan</a></li>
        <li class="breadcrumb-item"><?= F::e($layanan['nama']) ?></li>
        <li class="breadcrumb-item active" aria-current="page">Galeri</li>
    </ol>
</nav>

<div class="mb-3">
    <h3 class="mb-0">Galeri: <?= F::e($layanan['nama']) ?></h3>
    <p class="text-muted small mb-0">
        Contoh hasil foto yang tampil di halaman publik. Gambar cover jadi gambar utama di kartu layanan.
        Maksimal <?= (int) $maks ?> gambar, JPG/PNG/WebP, <?= F::e((string) $maksMb) ?> MB per gambar.
    </p>
</div>

<!-- Form upload -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <?php if ($penuh): ?>
            <div class="text-muted">
                Batas <?= (int) $maks ?> gambar tercapai. Hapus salah satu untuk menambah gambar baru.
            </div>
        <?php else: ?>
            <form action="/pegawai/layanan/gambar/upload" method="POST"
                  enctype="multipart/form-data" class="row g-2 align-items-end">
                <?= CsrfHelper::field() ?>
                <input type="hidden" name="layanan_id" value="<?= (int) $layanan['id'] ?>">

                <div class="col-md-5">
                    <label for="gambar" class="form-label">Pilih gambar</label>
                    <input type="file" id="gambar" name="gambar" class="form-control"
                           accept="image/jpeg,image/png,image/webp" required>
                </div>

                <div class="col-md-5">
                    <label for="keterangan" class="form-label">
                        Keterangan <span class="text-muted">(opsional)</span>
                    </label>
                    <input type="text" id="keterangan" name="keterangan" maxlength="150"
                           class="form-control" placeholder="Contoh: Sesi wisuda outdoor">
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="bi bi-upload"></i> Unggah
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Daftar gambar -->
<?php if (empty($items)): ?>
    <div class="card shadow-sm">
        <div class="card-body text-center text-muted py-5">
            Belum ada gambar untuk layanan ini.
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($items as $g): ?>
            <?php $cover = F::bool($g['is_cover']); ?>
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <div class="card shadow-sm h-100">
                    <img src="<?= F::e(F::urlGambar($g['path_file'])) ?>"
                         alt="<?= F::e($g['keterangan'] ?? $layanan['nama']) ?>"
                         class="card-img-top" style="height:180px;object-fit:cover" loading="lazy">

                    <div class="card-body">
                        <?php if ($cover): ?>
                            <span class="badge text-bg-success mb-2">Cover</span>
                        <?php endif; ?>

                        <form action="/pegawai/layanan/gambar/update" method="POST" class="row g-2">
                            <?= CsrfHelper::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $g['id'] ?>">

                            <div class="col-8">
                                <input type="text" name="keterangan" maxlength="150"
                                       class="form-control form-control-sm" placeholder="Keterangan"
                                       value="<?= F::e($g['keterangan'] ?? '') ?>">
                            </div>
                            <div class="col-4">
                                <input type="number" name="urutan" min="0" max="999"
                                       class="form-control form-control-sm" title="Urutan tampil"
                                       value="<?= (int) $g['urutan'] ?>">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer bg-white d-flex justify-content-between gap-2">
                        <?php if (!$cover): ?>
                            <form action="/pegawai/layanan/gambar/cover" method="POST" class="m-0">
                                <?= CsrfHelper::field() ?>
                                <input type="hidden" name="id" value="<?= (int) $g['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-star"></i> Jadikan cover
                                </button>
                            </form>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>

                        <form action="/pegawai/layanan/gambar/delete" method="POST" class="m-0"
                              onsubmit="return confirm('Hapus gambar ini?');">
                            <?= CsrfHelper::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $g['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();

require dirname(__DIR__, 2) . '/layouts/pegawai.php';