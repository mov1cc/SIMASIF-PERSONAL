(function () {
    'use strict';

    const root = document.querySelector('[data-layanan-root]');
    if (!root) return;

    const grid = document.getElementById('layananGrid');
    const cards = Array.from(grid.querySelectorAll('[data-layanan-card]'));
    const search = document.getElementById('layananSearch');
    const counter = document.getElementById('layananCounter');
    const empty = document.getElementById('layananEmpty');
    const filterButtons = root.querySelectorAll('[data-filter]');
    const viewButtons = root.querySelectorAll('[data-view]');

    let status = 'all';

    function apply() {
        const q = search.value.trim().toLowerCase();
        let shown = 0;

        cards.forEach(function (card) {
            const cocokNama = card.dataset.nama.includes(q);
            const cocokStatus = status === 'all' || card.dataset.status === status;
            const visible = cocokNama && cocokStatus;

            card.classList.toggle('d-none', !visible);
            if (visible) shown++;
        });

        counter.textContent = 'Menampilkan ' + shown + ' dari ' + cards.length + ' Layanan';
        empty.classList.toggle('d-none', shown !== 0);
    }

    search.addEventListener('input', apply);

    filterButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            status = btn.dataset.filter;
            filterButtons.forEach(function (b) {
                b.classList.toggle('active', b === btn);
            });
            apply();
        });
    });

    viewButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            grid.classList.toggle('is-list', btn.dataset.view === 'list');
            viewButtons.forEach(function (b) {
                b.classList.toggle('active', b === btn);
            });
        });
    });

    apply();
})();