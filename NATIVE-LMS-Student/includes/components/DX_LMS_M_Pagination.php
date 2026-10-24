<nav id="paginationContainer" aria-label="Navigasi halaman daftar" class="hidden flex flex-col gap-3 rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-3 sm:flex-row sm:items-center sm:justify-between">
    <label class="flex items-center gap-2 text-sm text-on-surface-variant">Baris per halaman
        <select id="pageSizeSelect" class="h-9 rounded-lg border border-outline-variant bg-surface px-2 text-on-surface" aria-label="Jumlah baris per halaman">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </select>
    </label>
    <div class="flex items-center justify-between gap-3 sm:justify-end">
        <button type="button" id="prevPageBtn" class="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-sm text-on-surface disabled:cursor-not-allowed disabled:opacity-40">Sebelumnya</button>
        <span id="pageIndicator" class="min-w-24 text-center text-sm text-on-surface-variant" aria-live="polite">Halaman 1</span>
        <button type="button" id="nextPageBtn" class="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-sm text-on-surface disabled:cursor-not-allowed disabled:opacity-40">Selanjutnya</button>
    </div>
</nav>
<script>
    (() => {
        const nav = document.currentScript.previousElementSibling;
        const select = nav?.querySelector('#pageSizeSelect');
        if (!select) return;
        const allowed = Array.from(select.options, option => option.value);
        const saved = localStorage.getItem('dls-list-page-size');
        if (allowed.includes(saved)) select.value = saved;
        select.addEventListener('change', () => localStorage.setItem('dls-list-page-size', select.value));
    })();
</script>
