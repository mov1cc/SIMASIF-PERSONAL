<?php
use App\Helpers\FormatHelper as F;

/** @var array $items */
/** @var array{q:string,status:string,dari:string,sampai:string} $filter */

$title = 'Data Pemesanan';

$adaFilter = $filter['q'] !== '' || $filter['status'] !== ''
    || $filter['dari'] !== '' || $filter['sampai'] !== '';

ob_start();
?>

<div class="mb-3">
    <h3 class="mb-0">Data Pemesanan</h3>
    <p class="text-muted small mb-0">
        Semua pemesanan yang masuk dari pelanggan. Total: <?= count($items) ?> pemesanan.
    </p>
</div>

<form method="GET" action="/pegawai/pemesanan" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="search" name="q" class="form-control"
               placeholder="Cari kode, nama, atau nomor HP..."
               value="<?= F::e($filter['q']) ?>">
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Semua status</option>
            <?php foreach (F::daftarStatusPemesanan() as $kode => $label): ?>
                <option value="<?= F::e($kode) ?>" <?= $filter['status'] === $kode ? 'selected' : '' ?>>
                    <?= F::e($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-6 col-md-2">
        <input type="date" name="dari" class="form-control" title="Jadwal dari"
               value="<?= F::e($filter['dari']) ?>">
    </div>
    <div class="col-6 col-md-2">
        <input type="date" name="sampai" class="form-control" title="Jadwal sampai"
               value="<?= F::e($filter['sampai']) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary">Filter</button>
        <?php if ($adaFilter): ?>
            <a href="/pegawai/pemesanan" class="btn btn-link">Reset</a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Pelanggan</th>
                    <th>Layanan</th>
                    <th>Jadwal</th>
                    <th>Status</th>
                    <th>Pembayaran</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <?= $adaFilter
                                ? 'Tidak ada pemesanan yang cocok dengan filter.'
                                : 'Belum ada pemesanan masuk.' ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($items as $row): ?>
                    <tr>
                        <td class="font-monospace fw-semibold"><?= F::e($row['kode_unik']) ?></td>
                        <td>
                            <div><?= F::e($row['pelanggan_nama']) ?></div>
                            <div class="text-muted small"><?= F::e($row['pelanggan_no_hp']) ?></div>
                        </td>
                        <td><?= F::e($row['layanan_nama']) ?></td>
                        <td class="text-nowrap">
                            <div><?= F::e(date('d/m/Y', strtotime($row['tanggal_jadwal']))) ?></div>
                            <div class="text-muted small"><?= F::e(F::jam($row['jam_jadwal'])) ?> WIB</div>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= F::e(F::statusPemesananBadge($row['status'])) ?>">
                                <?= F::e(F::statusPemesanan($row['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= F::e(F::statusPembayaranBadge($row['status_pembayaran'])) ?>">
                                <?= F::e(F::statusPembayaran($row['status_pembayaran'])) ?>
                            </span>
                        </td>
                        <td class="text-end text-nowrap"><?= F::e(F::rupiah($row['total_tagihan'])) ?></td>
                        <td class="text-end">
                            <a href="/pegawai/pemesanan/detail?id=<?= (int) $row['id'] ?>"
                               class="btn btn-sm btn-outline-dark">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();

require dirname(__DIR__, 2) . '/layouts/pegawai.php';