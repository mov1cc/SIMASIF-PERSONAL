<?php
use App\Core\Session;
use App\Helpers\CsrfHelper;
use App\Helpers\FormatHelper as F;

/** @var array $pemesanan */
/** @var array $addons */

$title = 'Konfirmasi Pemesanan';

$tanggal = date('d/m/Y', strtotime($pemesanan['tanggal_jadwal']));
$jam     = substr((string) $pemesanan['jam_jadwal'], 0, 5);

ob_start();
?>

<div class="row justify-content-center">

    <div class="col-lg-6">

        <div class="card shadow-sm">

            <div class="card-body p-4">


                <h1 class="h4 fw-bold mb-3">
                    Konfirmasi Pemesanan
                </h1>


                <p class="text-muted">
                    Periksa kembali detail pesanan Anda sebelum melanjutkan.
                </p>



                <dl class="row small mb-4">


                    <dt class="col-5 text-muted fw-normal">
                        Nama
                    </dt>

                    <dd class="col-7">
                        <?= F::e($pemesanan['pelanggan_nama']) ?>
                    </dd>



                    <dt class="col-5 text-muted fw-normal">
                        Nomor HP
                    </dt>

                    <dd class="col-7">
                        <?= F::e($pemesanan['pelanggan_no_hp']) ?>
                    </dd>



                    <dt class="col-5 text-muted fw-normal">
                        Layanan
                    </dt>

                    <dd class="col-7">
                        <?= F::e($pemesanan['layanan_nama']) ?>
                    </dd>



                    <?php foreach ($addons as $a): ?>

                        <dt class="col-5 text-muted fw-normal">
                            Add-on
                        </dt>

                        <dd class="col-7">

                            <?= F::e($a['nama_saat_pesan']) ?>

                            <?= (int) $a['jumlah'] > 1
                                ? '× ' . (int) $a['jumlah']
                                : '' ?>

                            (
                            <?= F::e(F::rupiah($a['subtotal'])) ?>
                            )

                        </dd>

                    <?php endforeach; ?>



                    <dt class="col-5 text-muted fw-normal">
                        Jadwal
                    </dt>

                    <dd class="col-7">

                        <?= F::e($tanggal) ?>,
                        <?= F::e($jam) ?>
                        WIB

                    </dd>



                    <dt class="col-5 text-muted fw-normal">
                        Estimasi durasi
                    </dt>

                    <dd class="col-7">

                        <?= F::e(
                            F::durasi(
                                $pemesanan['durasi_total_menit']
                            )
                        ) ?>

                    </dd>



                    <?php if (!empty($pemesanan['catatan_khusus'])): ?>

                        <dt class="col-5 text-muted fw-normal">
                            Catatan
                        </dt>

                        <dd class="col-7">

                            <?= F::e(
                                $pemesanan['catatan_khusus']
                            ) ?>

                        </dd>

                    <?php endif; ?>



                    <dt class="col-5 text-muted fw-normal">
                        Total Tagihan
                    </dt>

                    <dd class="col-7 fw-semibold">

                        <?= F::e(
                            F::rupiah(
                                $pemesanan['total_tagihan']
                            )
                        ) ?>

                    </dd>


                </dl>




                <form action="/pemesanan/submit"
                      method="POST">

                    <?= CsrfHelper::field() ?>


                    <button type="submit"
                            class="btn btn-dark w-100">

                        <i class="bi bi-check-circle"></i>

                        Konfirmasi Pemesanan

                    </button>


                </form>



                <div class="text-center mt-3">

                    <a href="/pemesanan"
                       class="text-muted small">

                        Ubah Data Pemesanan

                    </a>

                </div>


            </div>

        </div>


    </div>

</div>



<?php
$content = ob_get_clean();

require dirname(__DIR__, 2) . '/layouts/guest.php';