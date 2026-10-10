<?php
use App\Helpers\FormatHelper as F;

/** @var string $tanggal */
/** @var string $hariIni */
/** @var string $sebelum */
/** @var string $sesudah */
/** @var array  $minggu */
/** @var array  $sesi */
/** @var bool   $adaBentrok */

$title = 'Jadwal Operasional';

ob_start();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="mb-0">Jadwal Operasional</h3>
        <p class="text-muted small mb-0">
            <?= F::e(F::namaHari($tanggal)) ?>, <?= F::e(F::tanggalIndo($tanggal)) ?>
            <?= $tanggal === $hariIni ? '(hari ini)' : '' ?>
        </p>
    </div>

    <form method="GET" action="/pegawai/jadwal" class="d-flex flex-wrap gap-2 align-items-center">
        <a href="/pegawai/jadwal?tanggal=<?= F::e($sebelum) ?>" class="btn btn-outline-secondary" aria-label="Hari sebelumnya">
            <i class="bi bi-chevron-left"></i>
        </a>
        <input type="date" name="tanggal" class="form-control" style="width:auto"
               value="<?= F::e($tanggal) ?>" onchange="this.form.submit()">
        <a href="/pegawai/jadwal?tanggal=<?= F::e($sesudah) ?>" class="btn btn-outline-secondary" aria-label="Hari berikutnya">
            <i class="bi bi-chevron-right"></i>
        </a>
        <a href="/pegawai/jadwal" class="btn btn-dark">Hari ini</a>
    </form>
</div>

<!-- Strip minggu -->
<div class="row g-2 mb-4">
    <?php foreach ($minggu as $h): ?>
        <?php $aktif = $h['tanggal'] === $tanggal; ?>
        <div class="col">
            <a href="/pegawai/jadwal?tanggal=<?= F::e($h['tanggal']) ?>"
               class="card text-decoration-none text-center h-100 <?= $aktif ? 'text-bg-dark' : '' ?>">
                <div class="card-body py-2">
                    <div class="small <?= $aktif ? '' : 'text-muted' ?>">
                        <?= F::e(mb_substr(F::namaHari($h['tanggal']), 0, 3)) ?>
                    </div>
                    <div class="fs-5 fw-bold"><?= F::e(date('j', strtotime($h['tanggal']))) ?></div>
                    <span class="badge text-bg-<?= $h['jumlah'] > 0 ? ($aktif ? 'light' : 'primary') : 'secondary' ?>">
                        <?= (int) $h['jumlah'] ?> sesi
                    </span>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($adaBentrok): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        Ada sesi yang jamnya saling beririsan pada hari ini (ditandai merah). Silakan hubungi pelanggan
        untuk mengatur ulang jadwal.
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">
        Sesi pada <?= F::e(F::tanggalIndo($tanggal)) ?> (<?= count($sesi) ?>)
    </div>

    <?php if (empty($sesi)): ?>
        <div class="card-body text-center text-muted py-5">Tidak ada sesi pada tanggal ini.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Waktu</th>
                        <th>Kode</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Pegawai</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sesi as $s): ?>
                        <tr class="<?= $s['bentrok'] ? 'table-danger' : '' ?>">
                            <td class="text-nowrap fw-semibold">
                                <?= F::e(F::jam($s['jam_jadwal'])) ?> – <?= F::e($s['selesai_teks']) ?>
                                <?php if ($s['bentrok']): ?>
                                    <i class="bi bi-exclamation-triangle-fill text-danger" title="Jadwal bentrok"></i>
                                <?php endif; ?>
                            </td>
                            <td class="font-monospace"><?= F::e($s['kode_unik']) ?></td>
                            <td><?= F::e($s['pelanggan_nama']) ?></td>
                            <td><?= F::e($s['layanan_nama']) ?></td>
                            <td><?= F::e($s['pegawai_nama'] ?? '-') ?></td>
                            <td>
                                <span class="badge text-bg-<?= F::e(F::statusPemesananBadge($s['status'])) ?>">
                                    <?= F::e(F::statusPemesanan($s['status'])) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="/pegawai/pemesanan/detail?id=<?= (int) $s['id'] ?>"
                                   class="btn btn-sm btn-outline-dark">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();

require dirname(__DIR__, 2) . '/layouts/pegawai.php';