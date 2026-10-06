<?php
use App\Helpers\CsrfHelper;
use App\Helpers\FormatHelper as F;

$title = 'Data Layanan';

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0">Data Layanan</h3>
        <p class="text-muted small mb-0">Paket fotografi yang ditawarkan Studio Flamboyan.</p>
    </div>
    <a href="/pegawai/layanan/create" class="btn btn-primary">+ Tambah Layanan</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:50px">No</th>
                    <th>Nama</th>
                    <th>Deskripsi</th>
                    <th class="text-end">Harga</th>
                    <th>Durasi</th>
                    <th>Status</th>
                    <th style="width:170px">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Belum ada data layanan.
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($items as $i => $row): ?>
                <?php $aktif = F::bool($row['is_active']); ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td class="fw-semibold"><?= F::e($row['nama']) ?></td>
                    <td class="text-muted small">
                        <?= F::e(mb_strimwidth((string) $row['deskripsi'], 0, 80, '...')) ?>
                    </td>
                    <td class="text-end"><?= F::e(F::rupiah($row['harga'])) ?></td>
                    <td><?= F::e(F::durasi($row['estimasi_durasi_menit'])) ?></td>
                    <td>
                        <?php if ($aktif): ?>
                            <span class="badge text-bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="/pegawai/layanan/edit?id=<?= (int) $row['id'] ?>"
                               class="btn btn-sm btn-outline-primary">Edit</a>

                            <form action="/pegawai/layanan/delete" method="POST" class="m-0"
                                  onsubmit="return confirm('Hapus layanan ini?');">
                                <?= CsrfHelper::field() ?>
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
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