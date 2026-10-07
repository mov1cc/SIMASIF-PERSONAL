<?php
use App\Helpers\CsrfHelper;
use App\Helpers\FormatHelper as F;

/** @var array $items */
/** @var array{top:?array, terendah:?array, total_omzet:float} $stat */

$title   = 'Katalog Layanan';
$styles  = [
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap',
    '/assets/css/layanan.css',
];
$scripts = ['/assets/js/modules/layanan.js'];

$total    = count($items);
$aktif    = count(array_filter($items, fn ($r) => F::bool($r['is_active'])));
$nonaktif = $total - $aktif;

$top        = $stat['top'];
$terendah   = $stat['terendah'];
$totalOmzet = $stat['total_omzet'];

$badgeTotal = $total === 0
    ? 'Belum ada layanan'
    : ($nonaktif === 0 ? 'Semua Terpublish' : $nonaktif . ' Nonaktif');

/** Persentase omzet layanan terhadap total omzet studio bulan ini */
$kontribusi = function (?array $row) use ($totalOmzet): string {
    if ($row === null || $totalOmzet <= 0) {
        return '0%';
    }

    return number_format($row['omzet'] / $totalOmzet * 100, 1, ',', '.') . '%';
};

/** Satu baris deskripsi = satu poin deliverables */
$parsePoin = function (?string $deskripsi): array {
    $baris = preg_split('/\R/u', (string) $deskripsi) ?: [];

    return array_values(array_filter(array_map('trim', $baris), fn ($b) => $b !== ''));
};

$kartuStat = [
    [
        'row'       => $top,
        'badge'     => 'Top 1 Bulan Ini',
        'class'     => 'sf-badge-green',
        'badgeIcon' => 'bi-graph-up-arrow',
        'icon'      => 'bi-award',
    ],
    [
        'row'       => $terendah,
        'badge'     => 'Terendah Bulan Ini',
        'class'     => 'sf-badge-red',
        'badgeIcon' => 'bi-graph-down-arrow',
        'icon'      => 'bi-bag',
    ],
];

ob_start();
?>

<div class="layanan-page" data-layanan-root>

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="sf-crumb mono">Operasional Tim / <strong>Katalog Layanan</strong></div>
            <h1 class="sf-title">Manajemen Katalog Layanan Foto Studio</h1>
            <p class="sf-sub">
                Kelola daftar layanan pemotretan, penyesuaian harga, deskripsi layanan,
                serta sinkronisasi katalog publik pelanggan.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="/layanan" target="_blank" rel="noopener" class="sf-btn sf-btn-light">
                <i class="bi bi-eye"></i> Preview Halaman Publik
            </a>
            <a href="/pegawai/layanan/create" class="sf-btn sf-btn-dark">
                <i class="bi bi-plus-circle"></i> Buat Layanan Baru
            </a>
        </div>
    </div>

    <!-- Kartu statistik -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="sf-panel sf-stat">
                <div class="sf-stat-body">
                    <div class="sf-stat-top">
                        <span class="sf-icon"><i class="bi bi-collection"></i></span>
                        <span class="sf-badge mono">
                            <span class="sf-dot <?= $nonaktif > 0 || $total === 0 ? 'sf-dot-off' : '' ?>"></span>
                            <?= F::e($badgeTotal) ?>
                        </span>
                    </div>
                    <div class="sf-stat-value"><?= $aktif ?> Layanan</div>
                    <div class="sf-stat-label">Total Layanan Aktif</div>
                </div>
                <div class="sf-stat-foot mono">
                    <span>Keseluruhan<br>Layanan</span>
                    <strong><?= $total ?> Layanan<br>Aktif/Non-Aktif</strong>
                </div>
            </div>
        </div>

        <?php foreach ($kartuStat as $k): ?>
            <div class="col-lg-4">
                <div class="sf-panel sf-stat">
                    <div class="sf-stat-body">
                        <div class="sf-stat-top">
                            <span class="sf-icon"><i class="bi <?= $k['icon'] ?>"></i></span>
                            <span class="sf-badge <?= $k['class'] ?> mono">
                                <i class="bi <?= $k['badgeIcon'] ?>"></i> <?= F::e($k['badge']) ?>
                            </span>
                        </div>
                        <?php if ($k['row'] !== null): ?>
                            <div class="sf-stat-value"><?= F::e($k['row']['nama']) ?></div>
                            <div class="sf-stat-label"><?= (int) $k['row']['sesi'] ?> Sesi Dipesan</div>
                        <?php else: ?>
                            <div class="sf-stat-value">-</div>
                            <div class="sf-stat-label">Belum ada pemesanan bulan ini</div>
                        <?php endif; ?>
                    </div>
                    <div class="sf-stat-foot mono">
                        <span>Kontribusi Omzet</span>
                        <strong><?= F::e($kontribusi($k['row'])) ?> Total Studio</strong>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Toolbar: cari, filter, tampilan -->
    <div class="sf-panel sf-toolbar mb-4">
        <div class="sf-search">
            <i class="bi bi-search"></i>
            <input type="search" id="layananSearch" placeholder="Cari nama layanan..." autocomplete="off">
        </div>

        <div class="sf-pills mono">
            <button type="button" class="sf-pill active" data-filter="all">Semua (<?= $total ?>)</button>
            <button type="button" class="sf-pill" data-filter="aktif">Aktif (<?= $aktif ?>)</button>
            <button type="button" class="sf-pill" data-filter="nonaktif">Nonaktif (<?= $nonaktif ?>)</button>
        </div>

        <div class="sf-viewtoggle">
            <button type="button" class="active" data-view="grid" aria-label="Tampilan grid">
                <i class="bi bi-grid"></i>
            </button>
            <button type="button" data-view="list" aria-label="Tampilan daftar">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>

    <!-- Judul daftar -->
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div>
            <h2 class="h5 fw-semibold mb-1">Daftar Layanan Studio</h2>
            <p class="text-muted small mb-0">
                Atur harga, durasi sesi, dan poin hasil layanan yang ditawarkan ke pelanggan.
            </p>
        </div>
        <span class="sf-badge mono" id="layananCounter">
            Menampilkan <?= $total ?> dari <?= $total ?> Layanan
        </span>
    </div>

    <!-- Daftar kartu -->
    <div class="row g-4" id="layananGrid">
        <?php foreach ($items as $row): ?>
            <?php
            $isActive = F::bool($row['is_active']);
            $poin     = $parsePoin($row['deskripsi']);
            $tampil   = array_slice($poin, 0, 6);
            $sisa     = count($poin) - count($tampil);
            ?>
            <div class="col-lg-6"
                 data-layanan-card
                 data-nama="<?= F::e(mb_strtolower($row['nama'])) ?>"
                 data-status="<?= $isActive ? 'aktif' : 'nonaktif' ?>">

                <article class="sf-panel sf-card <?= $isActive ? '' : 'is-off' ?>">

                    <div class="sf-card-head">
                        <div>
                            <span class="sf-badge mono">
                                <span class="sf-dot <?= $isActive ? '' : 'sf-dot-off' ?>"></span>
                                <?= $isActive ? 'Aktif di Portal' : 'Nonaktif' ?>
                            </span>
                            <h3 class="sf-card-name"><?= F::e($row['nama']) ?></h3>
                        </div>
                        <div class="sf-card-price"><?= F::e(F::rupiah($row['harga'])) ?></div>
                    </div>

                    <div class="sf-meta">
                        <span class="text-muted">Estimasi durasi sesi</span>
                        <span><i class="bi bi-clock"></i> <?= F::e(F::durasi($row['estimasi_durasi_menit'])) ?></span>
                    </div>

                    <div class="sf-section-label mono">Output &amp; Deliverables Layanan:</div>

                    <?php if (empty($tampil)): ?>
                        <p class="sf-deliver-empty">Belum ada deskripsi layanan.</p>
                    <?php else: ?>
                        <ul class="sf-deliver">
                            <?php foreach ($tampil as $p): ?>
                                <li><i class="bi bi-check-circle"></i><span><?= F::e($p) ?></span></li>
                            <?php endforeach; ?>
                            <?php if ($sisa > 0): ?>
                                <li class="text-muted"><i class="bi bi-three-dots"></i><span>+<?= $sisa ?> poin lainnya</span></li>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>

                    <div class="sf-publish">
                        <span>Tampilkan di Form Booking Publik</span>
                        <form action="/pegawai/layanan/toggle" method="POST" class="sf-switch m-0">
                            <?= CsrfHelper::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                            <div class="form-check form-switch m-0 p-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       name="is_active" value="1"
                                       aria-label="Tampilkan <?= F::e($row['nama']) ?> di form booking publik"
                                       <?= $isActive ? 'checked' : '' ?>
                                       onchange="this.form.submit()">
                            </div>
                        </form>
                    </div>

                    <div class="sf-card-actions">
                        <form action="/pegawai/layanan/delete" method="POST" class="m-0"
                              onsubmit="return confirm('Hapus layanan ini?');">
                            <?= CsrfHelper::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                            <button type="submit" class="sf-btn sf-btn-ghost">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </form>
                        <a href="/pegawai/layanan/edit?id=<?= (int) $row['id'] ?>" class="sf-btn sf-btn-dark">
                            <i class="bi bi-sliders"></i> Edit Detail &amp; Harga
                        </a>
                    </div>

                </article>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="layananEmpty" class="sf-panel p-4 text-center text-muted mono <?= $total > 0 ? 'd-none' : '' ?>">
        Tidak ada layanan yang ditemukan.
    </div>

</div>

<?php
$content = ob_get_clean();

require dirname(__DIR__, 2) . '/layouts/pegawai.php';