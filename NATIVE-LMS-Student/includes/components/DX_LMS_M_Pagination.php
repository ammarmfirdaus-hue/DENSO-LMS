<nav id="paginationContainer" aria-label="Navigasi halaman daftar" class="hidden flex flex-col gap-[12px] rounded-[12px] bg-white p-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] sm:flex-row sm:items-center sm:justify-between sm:px-[18px]">
    <label class="flex items-center gap-[12px] text-[14px] font-medium text-[#525252]">Baris per halaman
        <select id="pageSizeSelect" class="fd-select h-[38px] min-h-[38px] rounded-[12px] border border-[#E5E5E5] bg-white px-3 text-[14px] text-[#171717]" aria-label="Jumlah baris per halaman">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </select>
    </label>
    <div class="flex items-center justify-between gap-[12px] sm:justify-end">
        <button type="button" id="prevPageBtn" class="fd-btn fd-btn-ghost fd-btn-sm rounded-full px-[14px] text-[13px] font-medium text-[#525252] disabled:cursor-not-allowed disabled:opacity-40">Sebelumnya</button>
        <span id="pageIndicator" class="fd-mono min-w-24 text-center text-[13px] font-medium text-[#171717]" aria-live="polite">Halaman 1</span>
        <button type="button" id="nextPageBtn" class="fd-btn fd-btn-secondary fd-btn-sm rounded-full px-[14px] text-[13px] font-semibold disabled:cursor-not-allowed disabled:opacity-40">Selanjutnya</button>
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
