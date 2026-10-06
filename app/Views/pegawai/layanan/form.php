<?php
use App\Core\Session;
use App\Helpers\CsrfHelper;
use App\Helpers\FormatHelper as F;

/** @var array|null $layanan  null = mode tambah */
/** @var string $action */

$errors = Session::flash('errors') ?? [];
$old    = Session::flash('old') ?? [];

$isEdit = $layanan !== null;
$title  = $isEdit ? 'Edit Layanan' : 'Tambah Layanan';

// Prioritas nilai: input lama (gagal validasi) > data database > kosong
$nilai = fn (string $key) => $old[$key] ?? ($layanan[$key] ?? '');

if (!empty($old)) {
    $aktif = !empty($old['is_active']);
} elseif ($isEdit) {
    $aktif = F::bool($layanan['is_active']);
} else {
    $aktif = true; // default layanan baru = aktif
}

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <h3 class="mb-3"><?= F::e($title) ?></h3>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <form action="<?= F::e($action) ?>" method="POST" novalidate>
                    <?= CsrfHelper::field() ?>

                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= (int) $layanan['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Layanan</label>
                        <input type="text" id="nama" name="nama" maxlength="150"
                               class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>"
                               value="<?= F::e($nilai('nama')) ?>" autofocus>
                        <?php if (isset($errors['nama'])): ?>
                            <div class="invalid-feedback"><?= F::e($errors['nama']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" rows="3"
                                  class="form-control"><?= F::e($nilai('deskripsi')) ?></textarea>
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
                            <label for="estimasi_durasi_menit" class="form-label">Estimasi Durasi (menit)</label>
                            <input type="number" id="estimasi_durasi_menit" name="estimasi_durasi_menit"
                                   min="1" step="1"
                                   class="form-control <?= isset($errors['estimasi_durasi_menit']) ? 'is-invalid' : '' ?>"
                                   value="<?= F::e($nilai('estimasi_durasi_menit')) ?>">
                            <?php if (isset($errors['estimasi_durasi_menit'])): ?>
                                <div class="invalid-feedback"><?= F::e($errors['estimasi_durasi_menit']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="is_active" name="is_active" value="1"
                               <?= $aktif ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            Aktif (tampil di halaman publik)
                        </label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="/pegawai/layanan" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();

require dirname(__DIR__, 2) . '/layouts/pegawai.php';