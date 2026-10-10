(function () {
    'use strict';

    // ---- Form pemesanan: ringkasan layanan terpilih ----
    const form = document.querySelector('[data-booking-form]');

    if (form) {
        const select = form.querySelector('#layanan_id');
        const info = form.querySelector('#ringkasanLayanan');

        const rupiah = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        });

        function updateRingkasan() {
            const opt = select.options[select.selectedIndex];

            if (!opt || !opt.value) {
                info.textContent = '';
                return;
            }

            const harga = Number(opt.dataset.harga || 0);
            const durasi = Number(opt.dataset.durasi || 0);

            info.textContent =
                'Harga ' + rupiah.format(harga) +
                (durasi > 0 ? ' · estimasi sesi ' + durasi + ' menit' : '');
        }

        select.addEventListener('change', updateRingkasan);
        updateRingkasan();
    }

    // ---- Halaman konfirmasi: salin kode ----
    document.querySelectorAll('[data-copy-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.querySelector(btn.dataset.copyTarget);
            if (!target) return;

            const teks = target.textContent.trim();
            const label = btn.querySelector('span');

            function selesai() {
                label.textContent = 'Tersalin!';
                setTimeout(function () { label.textContent = 'Salin kode'; }, 1500);
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(teks).then(selesai);
            } else {
                const ta = document.createElement('textarea');
                ta.value = teks;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                selesai();
            }
        });
    });
})();