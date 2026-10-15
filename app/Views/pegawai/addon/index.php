<?php
use App\Core\Session;
use App\Helpers\CsrfHelper;
use App\Helpers\FormatHelper as F;

/** @var array $layanan */
/** @var array $items */
/** @var array|null $edit  null = mode tambah */

$errors = Session::flash('errors') ?? [];
$old    = Session::flash('old') ?? [];

$isEdit = $edit !== null;
$title  = 'Add-on ' . $layanan['nama'];

// Prioritas nilai: input lama (gagal validasi) > data database > kosong
$nilai = fn (string $key) => $old[$key] ?? ($edit[$key] ?? '');

if (!empty($old)) {
    $aktif = !empty($old['is_active']);
} elseif ($isEdit) {
    $aktif = F::bool($edit['is_active']);
} else {
    $aktif = true;
}

$durasi = $nilai('tambahan_durasi_menit');
$durasi = $durasi === '' ? 0 : (int) $durasi;

ob_start();
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/pegawai/layanan">Layanan</a></li>
        <li class="breadcrumb-item"><?= F::e($layanan['nama']) ?></li>
        <li class="breadcrumb-item active" aria-current="page">Add-on</li>
    </ol>
</nav>

<div class="mb-3">
    <h3 class="mb-0">Add-on: <?= F::e($layanan['nama']) ?></h3>
    <p class="text-muted small mb-0">
        Opsi tambahan berbayar yang bisa dipilih pelanggan saat memesan layanan ini.
        Add-on dengan tambahan durasi memperpanjang slot jadwal sesi.
    </p>
</div>

<div class="row g-3">

    <!-- Daftar add-on -->
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">
                Daftar Add-on (<?= count($items) ?>)
            </div>

            <?php if (empty($items)): ?>
                <div class="card-body text-center text-muted py-5">
                    Belum ada add-on untuk layanan ini.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th class="text-end">Harga</th>
                                <th class="text-center">+Durasi</th>
                                <th class="text-center">Aktif</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $a): ?>
                                <?php $on = F::bool($a['is_active']); ?>
                                <tr class="<?= $on ? '' : 'text-muted' ?>">
                                    <td>
                                        <div class="fw-semibold"><?= F::e($a['nama']) ?></div>
                                        <?php if (!empty($a['deskripsi'])): ?>
                                            <div class="small text-muted"><?= F::e($a['deskripsi']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end text-nowrap"><?= F::e(F::rupiah($a['harga'])) ?></td>
                                    <td class="text-center text-nowrap">
                                        <?= (int) $a['tambahan_durasi_menit'] > 0
                                            ? '+' . (int) $a['tambahan_durasi_menit'] . ' mnt'
                                            : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <form action="/pegawai/layanan/addon/toggle" method="POST" class="m-0">
                                            <?= CsrfHelper::field() ?>
                                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                            <div class="form-check form-switch d-inline-block m-0">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                       name="is_active" value="1"
                                                       aria-label="Aktifkan <?= F::e($a['nama']) ?>"
                                                       <?= $on ? 'checked' : '' ?>
                                                       onchange="this.form.submit()">
                                            </div>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="/pegawai/layanan/addon/edit?id=<?= (int) $a['id'] ?>"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="/pegawai/layanan/addon/delete" method="POST" class="m-0"
                                                  onsubmit="return confirm('Hapus add-on ini?');">
                                                <?= CsrfHelper::field() ?>
                                                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Form tambah / edit -->
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <?= $isEdit ? 'Edit Add-on' : 'Tambah Add-on' ?>
            </div>
            <div class="card-body">

                <form action="<?= $isEdit ? '/pegawai/layanan/addon/update' : '/pegawai/layanan/addon/store' ?>"
                      method="POST" novalidate>
                    <?= CsrfHelper::field() ?>

                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
                    <?php else: ?>
                        <input type="hidden" name="layanan_id" value="<?= (int) $layanan['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Add-on</label>
                        <input type="text" id="nama" name="nama" maxlength="150"
                               class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>"
                               value="<?= F::e($nilai('nama')) ?>">
                        <?php if (isset($errors['nama'])): ?>
                            <div class="invalid-feedback"><?= F::e($errors['nama']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">
                            Deskripsi <span class="text-muted">(opsional)</span>
                        </label>
                        <textarea id="deskripsi" name="deskripsi" rows="2" maxlength="1000"
                                  class="form-control <?= isset($errors['deskripsi']) ? 'is-invalid' : '' ?>"><?= F::e($nilai('deskripsi')) ?></textarea>
                        <?php if (isset($errors['deskripsi'])): ?>
                            <div class="invalid-feedback"><?= F::e($errors['deskripsi']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="harga" class="form-label">Harga (Rp)</label>
                            <input type="number" id="harga" name="harga" min="0" step="any"
                                   class="form-control <?= isset($errors['harga']) ? 'is-invalid' : '' ?>"
                                   value="<?= F::e($nilai('harga') !== '' ? (float) $nilai('harga') : '') ?>">
                            <?php if (isset($errors['harga'])): ?>
                                <div class="invalid-feedback"><?= F::e($errors['harga']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tambahan_durasi_menit" class="form-label">Tambahan durasi (menit)</label>
                            <input type="number" id="tambahan_durasi_menit" name="tambahan_durasi_menit"
                                   min="0" step="1"
                                   class="form-control <?= isset($errors['tambahan_durasi_menit']) ? 'is-invalid' : '' ?>"
                                   value="<?= $durasi ?>">
                            <?php if (isset($errors['tambahan_durasi_menit'])): ?>
                                <div class="invalid-feedback"><?= F::e($errors['tambahan_durasi_menit']) ?></div>
                            <?php endif; ?>
                            <div class="form-text">0 = tidak menambah durasi sesi.</div>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="is_active" name="is_active" value="1"
                               <?= $aktif ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            Aktif (bisa dipilih pelanggan)
                        </label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <?php if ($isEdit): ?>
                            <a href="/pegawai/layanan/addon?layanan_id=<?= (int) $layanan['id'] ?>"
                               class="btn btn-outline-secondary">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();

require dirname(__DIR__, 2) . '/layouts/pegawai.php';