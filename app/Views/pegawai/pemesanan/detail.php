<?php
use App\Helpers\FormatHelper as F;

/** @var array $p */
/** @var array $riwayat */

$title = 'Detail ' . $p['kode_unik'];

ob_start();
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/pegawai/pemesanan">Pemesanan</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= F::e($p['kode_unik']) ?></li>
    </ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="mb-1 font-monospace"><?= F::e($p['kode_unik']) ?></h3>
        <span class="badge text-bg-<?= F::e(F::statusPemesananBadge($p['status'])) ?>">
            <?= F::e(F::statusPemesanan($p['status'])) ?>
        </span>
        <span class="badge text-bg-<?= F::e(F::statusPembayaranBadge($p['status_pembayaran'])) ?>">
            Pembayaran: <?= F::e(F::statusPembayaran($p['status_pembayaran'])) ?>
        </span>
    </div>
    <div class="d-flex gap-2">
        <a href="/pegawai/jadwal?tanggal=<?= F::e($p['tanggal_jadwal']) ?>"
           class="btn btn-outline-secondary">
            <i class="bi bi-calendar3"></i> Lihat di Jadwal
        </a>
        <a href="<?= F::e(F::linkWhatsApp($p['pelanggan_no_hp'])) ?>" target="_blank" rel="noopener"
           class="btn btn-success">
            <i class="bi bi-whatsapp"></i> Hubungi via WhatsApp
        </a>
    </div>
</div>

<div class="row g-3">

    <div class="col-lg-7">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Informasi Pemesanan</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted fw-normal">Pelanggan</dt>
                    <dd class="col-sm-8"><?= F::e($p['pelanggan_nama']) ?></dd>

                    <dt class="col-sm-4 text-muted fw-normal">Nomor HP</dt>
                    <dd class="col-sm-8"><?= F::e($p['pelanggan_no_hp']) ?></dd>

                    <dt class="col-sm-4 text-muted fw-normal">Layanan</dt>
                    <dd class="col-sm-8"><?= F::e($p['layanan_nama']) ?></dd>

                    <dt class="col-sm-4 text-muted fw-normal">Jadwal</dt>
                    <dd class="col-sm-8">
                        <?= F::e(F::namaHari($p['tanggal_jadwal'])) ?>,
                        <?= F::e(F::tanggalIndo($p['tanggal_jadwal'])) ?>,
                        <?= F::e(F::jam($p['jam_jadwal'])) ?> WIB
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Estimasi durasi</dt>
                    <dd class="col-sm-8"><?= F::e(F::durasi($p['estimasi_durasi_menit'])) ?></dd>

                    <dt class="col-sm-4 text-muted fw-normal">Pegawai penangan</dt>
                    <dd class="col-sm-8"><?= F::e($p['pegawai_nama'] ?? 'Belum ditentukan') ?></dd>

                    <dt class="col-sm-4 text-muted fw-normal">Catatan khusus</dt>
                    <dd class="col-sm-8">
                        <?= !empty($p['catatan_khusus'])
                            ? nl2br(F::e($p['catatan_khusus']))
                            : '<span class="text-muted">-</span>' ?>
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Dibuat pada</dt>
                    <dd class="col-sm-8 mb-0">
                        <?= F::e(date('d/m/Y H:i', strtotime($p['created_at']))) ?>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Rincian Tagihan</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted fw-normal">Harga layanan</dt>
                    <dd class="col-sm-8"><?= F::e(F::rupiah($p['harga_saat_pesan'])) ?></dd>

                    <dt class="col-sm-4 text-muted fw-normal">Biaya tambahan</dt>
                    <dd class="col-sm-8"><?= F::e(F::rupiah($p['biaya_tambahan'])) ?></dd>

                    <dt class="col-sm-4 text-muted fw-normal">Diskon</dt>
                    <dd class="col-sm-8"><?= F::e(F::rupiah($p['diskon'])) ?></dd>

                    <dt class="col-sm-4 fw-semibold">Total tagihan</dt>
                    <dd class="col-sm-8 fw-bold mb-0"><?= F::e(F::rupiah($p['total_tagihan'])) ?></dd>
                </dl>

                <?php if ($p['metode_pembayaran'] !== null): ?>
                    <hr>
                    <div class="small text-muted">
                        Metode: <?= $p['metode_pembayaran'] === 'transfer' ? 'Transfer bank' : 'Bayar langsung di studio' ?>
                        · Dibayar: <?= F::e(F::rupiah($p['jumlah_bayar'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Riwayat Status</div>
            <div class="card-body">
                <?php if (empty($riwayat)): ?>
                    <p class="text-muted mb-0">Belum ada riwayat.</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($riwayat as $r): ?>
                            <li class="border-start border-2 ps-3 pb-3">
                                <div class="fw-semibold">
                                    <?= F::e(F::statusPemesanan($r['status'])) ?>
                                </div>
                                <div class="small text-muted">
                                    <?= F::e(date('d/m/Y H:i', strtotime($r['created_at']))) ?>
                                    · <?= F::e($r['diubah_oleh_nama'] ?? 'Pelanggan / sistem') ?>
                                </div>
                                <?php if (!empty($r['catatan'])): ?>
                                    <div class="small"><?= F::e($r['catatan']) ?></div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();

require dirname(__DIR__, 2) . '/layouts/pegawai.php';