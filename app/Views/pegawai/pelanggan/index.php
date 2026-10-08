<?php
use App\Helpers\CsrfHelper;
use App\Helpers\FormatHelper as F;

/** @var array $items */
/** @var string $q */

$title = 'Data Pelanggan';

ob_start();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="mb-0">Data Pelanggan</h3>
        <p class="text-muted small mb-0">
            Pelanggan otomatis tercatat saat booking, atau bisa ditambahkan manual.
        </p>
    </div>
    <a href="/pegawai/pelanggan/create" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Tambah Pelanggan
    </a>
</div>

<form method="GET" action="/pegawai/pelanggan" class="row g-2 mb-3">
    <div class="col-md-6 col-lg-4">
        <input type="search" name="q" class="form-control"
               placeholder="Cari nama atau nomor HP..."
               value="<?= F::e($q) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary">Cari</button>
        <?php if ($q !== ''): ?>
            <a href="/pegawai/pelanggan" class="btn btn-link">Reset</a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:60px">#</th>
                    <th>Nama</th>
                    <th>No HP</th>
                    <th class="text-center">Pemesanan</th>
                    <th>Terdaftar</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <?= $q !== ''
                                ? 'Tidak ada pelanggan yang cocok dengan pencarian.'
                                : 'Belum ada data pelanggan.' ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($items as $i => $row): ?>
                    <?php $punyaPemesanan = (int) $row['total_pemesanan'] > 0; ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td class="fw-semibold"><?= F::e($row['nama']) ?></td>
                        <td><?= F::e($row['no_hp']) ?></td>
                        <td class="text-center">
                            <span class="badge text-bg-<?= $punyaPemesanan ? 'primary' : 'secondary' ?>">
                                <?= (int) $row['total_pemesanan'] ?>
                            </span>
                        </td>
                        <td class="text-muted small">
                            <?= F::e(date('d/m/Y', strtotime($row['created_at']))) ?>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="/pegawai/pelanggan/edit?id=<?= (int) $row['id'] ?>"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>

                                <form action="/pegawai/pelanggan/delete" method="POST" class="m-0"
                                      onsubmit="return confirm('Hapus pelanggan ini?');">
                                    <?= CsrfHelper::field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            <?= $punyaPemesanan ? 'disabled title="Sudah punya pemesanan"' : '' ?>>
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
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