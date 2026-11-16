<nav id="paginationContainer" aria-label="Navigasi halaman daftar" class="hidden flex flex-col gap-3 rounded-xl border border-slate-200/80 bg-white p-3 shadow-xs dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:items-center sm:justify-between sm:px-4">
    <label class="flex items-center gap-2 text-xs font-medium text-slate-600 dark:text-slate-400">
        <span>Tampilkan:</span>
        <select id="pageSizeSelect" class="h-8 rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-800 outline-none transition focus:border-red-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" aria-label="Jumlah baris per halaman">
            <option value="5">5 / hal</option>
            <option value="10" selected>10 / hal</option>
            <option value="25">25 / hal</option>
            <option value="50">50 / hal</option>
        </select>
    </label>
    <div class="flex items-center justify-between gap-2 sm:justify-end">
        <button type="button" id="prevPageBtn" class="inline-flex h-8 items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 hover:border-slate-300 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Sebelumnya</button>
        <span id="pageIndicator" class="min-w-24 text-center text-xs font-semibold text-slate-700 dark:text-slate-300" aria-live="polite">Halaman 1</span>
        <button type="button" id="nextPageBtn" class="inline-flex h-8 items-center gap-1 rounded-lg bg-red-600 px-3 text-xs font-semibold text-white shadow-xs transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-40">Selanjutnya</button>
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
