<?php
use App\Core\Session;
use App\Helpers\CsrfHelper;
use App\Helpers\FormatHelper as F;

/** @var array|null $pelanggan  null = mode tambah */
/** @var string $action */

$errors = Session::flash('errors') ?? [];
$old    = Session::flash('old') ?? [];

$isEdit = $pelanggan !== null;
$title  = $isEdit ? 'Edit Pelanggan' : 'Tambah Pelanggan';

// Prioritas nilai: input lama (gagal validasi) > data database > kosong
$nilai = fn (string $key) => $old[$key] ?? ($pelanggan[$key] ?? '');

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-6">

        <h3 class="mb-3"><?= F::e($title) ?></h3>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <form action="<?= F::e($action) ?>" method="POST" novalidate>
                    <?= CsrfHelper::field() ?>

                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= (int) $pelanggan['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Pelanggan</label>
                        <input type="text" id="nama" name="nama" maxlength="100"
                               class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>"
                               value="<?= F::e($nilai('nama')) ?>" autofocus>
                        <?php if (isset($errors['nama'])): ?>
                            <div class="invalid-feedback"><?= F::e($errors['nama']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="no_hp" class="form-label">Nomor HP</label>
                        <input type="tel" id="no_hp" name="no_hp" maxlength="30"
                               inputmode="tel" placeholder="081234567890"
                               class="form-control <?= isset($errors['no_hp']) ? 'is-invalid' : '' ?>"
                               value="<?= F::e($nilai('no_hp')) ?>">
                        <?php if (isset($errors['no_hp'])): ?>
                            <div class="invalid-feedback"><?= F::e($errors['no_hp']) ?></div>
                        <?php endif; ?>
                        <div class="form-text">
                            Dipakai sebagai identitas unik pelanggan. Format +62 / 62
                            otomatis diubah menjadi 08xx.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="/pegawai/pelanggan" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();

require dirname(__DIR__, 2) . '/layouts/pegawai.php';