<?php
use App\Core\Session;
use App\Helpers\CsrfHelper;
use App\Helpers\FormatHelper as F;
use App\Services\PemesananService;

/** @var array $items */
/** @var int $selected */

$errors = Session::flash('errors') ?? [];
$old    = Session::flash('old') ?? [];

$title   = 'Pesan Layanan';
$scripts = ['/assets/js/modules/booking.js'];

$nilai   = fn (string $key) => $old[$key] ?? '';
$dipilih = (string) ($old['layanan_id'] ?? ($selected > 0 ? $selected : ''));

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <h1 class="h3 fw-bold mb-1">Pesan Layanan</h1>
        <p class="text-muted mb-4">
            Isi data berikut. Setelah berhasil, Anda akan mendapat kode unik untuk memantau pesanan
            tanpa perlu membuat akun.
        </p>

        <?php if (empty($items)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    Belum ada layanan yang tersedia untuk dipesan.
                </div>
            </div>
        <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body p-4">

                <form action="/pemesanan/store" method="POST" novalidate data-booking-form>
                    <?= CsrfHelper::field() ?>

                    <!-- Layanan -->
                    <div class="mb-3">
                        <label for="layanan_id" class="form-label">Layanan</label>
                        <select id="layanan_id" name="layanan_id"
                                class="form-select <?= isset($errors['layanan_id']) ? 'is-invalid' : '' ?>">
                            <option value="">-- Pilih layanan --</option>
                            <?php foreach ($items as $row): ?>
                                <option value="<?= (int) $row['id'] ?>"
                                        data-harga="<?= F::e($row['harga']) ?>"
                                        data-durasi="<?= F::e($row['estimasi_durasi_menit'] ?? '') ?>"
                                        <?= (string) $row['id'] === $dipilih ? 'selected' : '' ?>>
                                    <?= F::e($row['nama']) ?> — <?= F::e(F::rupiah($row['harga'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['layanan_id'])): ?>
                            <div class="invalid-feedback"><?= F::e($errors['layanan_id']) ?></div>
                        <?php endif; ?>
                        <div class="form-text" id="ringkasanLayanan"></div>
                    </div>

                    <!-- Data diri -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" id="nama" name="nama" maxlength="100"
                                   class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>"
                                   value="<?= F::e($nilai('nama')) ?>">
                            <?php if (isset($errors['nama'])): ?>
                                <div class="invalid-feedback"><?= F::e($errors['nama']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="no_hp" class="form-label">Nomor HP / WhatsApp</label>
                            <input type="tel" id="no_hp" name="no_hp" maxlength="30"
                                   inputmode="tel" placeholder="081234567890"
                                   class="form-control <?= isset($errors['no_hp']) ? 'is-invalid' : '' ?>"
                                   value="<?= F::e($nilai('no_hp')) ?>">
                            <?php if (isset($errors['no_hp'])): ?>
                                <div class="invalid-feedback"><?= F::e($errors['no_hp']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Jadwal -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" id="tanggal" name="tanggal"
                                   min="<?= F::e(date('Y-m-d')) ?>"
                                   class="form-control <?= isset($errors['tanggal']) ? 'is-invalid' : '' ?>"
                                   value="<?= F::e($nilai('tanggal')) ?>">
                            <?php if (isset($errors['tanggal'])): ?>
                                <div class="invalid-feedback"><?= F::e($errors['tanggal']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="jam" class="form-label">Jam mulai</label>
                            <input type="time" id="jam" name="jam" step="900"
                                   min="<?= F::e(PemesananService::JAM_BUKA) ?>"
                                   max="<?= F::e(PemesananService::JAM_TUTUP) ?>"
                                   class="form-control <?= isset($errors['jam']) ? 'is-invalid' : '' ?>"
                                   value="<?= F::e($nilai('jam')) ?>">
                            <?php if (isset($errors['jam'])): ?>
                                <div class="invalid-feedback"><?= F::e($errors['jam']) ?></div>
                            <?php endif; ?>
                            <div class="form-text">
                                Jam operasional <?= F::e(PemesananService::JAM_BUKA) ?>
                                – <?= F::e(PemesananService::JAM_TUTUP) ?>.
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-4">
                        <label for="catatan_khusus" class="form-label">
                            Catatan / permintaan khusus <span class="text-muted">(opsional)</span>
                        </label>
                        <textarea id="catatan_khusus" name="catatan_khusus" rows="3" maxlength="1000"
                                  class="form-control <?= isset($errors['catatan']) ? 'is-invalid' : '' ?>"
                                  placeholder="Contoh: ukuran cetak 10R, kebutuhan editing khusus"><?= F::e($nilai('catatan')) ?></textarea>
                        <?php if (isset($errors['catatan'])): ?>
                            <div class="invalid-feedback"><?= F::e($errors['catatan']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-calendar-check"></i> Buat Pemesanan
                        </button>
                        <a href="/layanan" class="btn btn-outline-secondary">Kembali</a>
                    </div>
                </form>

            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php
$content = ob_get_clean();

require dirname(__DIR__, 2) . '/layouts/guest.php';