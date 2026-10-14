(function () {
    'use strict';

    // ---- Form pemesanan: add-on per layanan + ringkasan harga/durasi ----
    const form = document.querySelector('[data-booking-form]');

    if (form) {
        const select = form.querySelector('#layanan_id');
        const info = form.querySelector('#ringkasanLayanan');
        const addonWrap = form.querySelector('#addonWrap');
        const addonItems = Array.from(form.querySelectorAll('[data-addon-item]'));

        const rupiah = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        });

        // Tampilkan hanya add-on milik layanan terpilih; sisanya dimatikan
        // supaya tidak ikut terkirim.
        function syncAddon() {
            const layananId = select.value;
            let tampil = 0;

            addonItems.forEach(function (item) {
                const cb = item.querySelector('input[type="checkbox"]');
                const cocok = layananId !== '' && item.dataset.layanan === layananId;

                item.classList.toggle('d-none', !cocok);
                cb.disabled = !cocok;
                if (!cocok) cb.checked = false;
                if (cocok) tampil++;
            });

            if (addonWrap) addonWrap.classList.toggle('d-none', tampil === 0);
        }

        function updateRingkasan() {
            const opt = select.options[select.selectedIndex];

            if (!opt || !opt.value) {
                info.textContent = '';
                return;
            }

            let harga = Number(opt.dataset.harga || 0);
            // Sama dengan aturan server: durasi kosong dianggap 60 menit
            let durasi = Number(opt.dataset.durasi || 0) || 60;

            addonItems.forEach(function (item) {
                const cb = item.querySelector('input[type="checkbox"]');
                if (cb.checked && !cb.disabled) {
                    harga += Number(cb.dataset.harga || 0);
                    durasi += Number(cb.dataset.durasi || 0);
                }
            });

            info.textContent =
                'Total ' + rupiah.format(harga) + ' · estimasi sesi ' + durasi + ' menit';
        }

        select.addEventListener('change', function () {
            syncAddon();
            updateRingkasan();
        });

        addonItems.forEach(function (item) {
            item.querySelector('input[type="checkbox"]')
                .addEventListener('change', updateRingkasan);
        });

        syncAddon();
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