import { useEffect } from 'react';

function extractNearbySuggestions(input) {
  const scope = input.closest('section, main') || document.querySelector('main');
  if (!scope) return [];

  const seen = new Set();
  const items = [];
  const candidates = scope.querySelectorAll(
    'table tbody tr td:first-child, table tbody tr td:nth-child(2), h2, h3, [data-search-label]'
  );

  for (const el of candidates) {
    if (el.closest('header, nav, [data-search-dropdown]')) continue;
    const raw = (el.getAttribute('data-search-label') || el.textContent || '').replace(/\s+/g, ' ').trim();
    if (!raw || raw.length < 2 || raw.length > 90) continue;
    const key = raw.toLowerCase();
    if (seen.has(key)) continue;
    seen.add(key);
    items.push(raw);
    if (items.length >= 40) break;
  }
  return items;
}

function setReactInputValue(input, value) {
  const setter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value')?.set;
  if (setter) setter.call(input, value);
  else input.value = value;
  input.dispatchEvent(new Event('input', { bubbles: true }));
}

/**
 * Automatically equips search inputs across Admin & Teacher pages with a clean,
 * keyboard-navigable suggestion dropdown extracted from the active list/table.
 */
export default function GlobalSearchEnhancer() {
  useEffect(() => {
    let activeDropdown = null;
    let activeInput = null;
    let highlightedIndex = -1;

    function closeDropdown() {
      if (activeDropdown) {
        activeDropdown.remove();
        activeDropdown = null;
      }
      activeInput = null;
      highlightedIndex = -1;
    }

    function isEligibleSearchInput(el) {
      if (!el || el.tagName !== 'INPUT') return false;
      if (el.dataset.hasSuggestions === 'true' || el.getAttribute('role') === 'combobox') return false;
      const type = (el.type || '').toLowerCase();
      const ph = (el.placeholder || '').toLowerCase();
      return type === 'search' || (type === 'text' && ph.includes('cari'));
    }

    function renderDropdown(input) {
      const q = (input.value || '').trim().toLowerCase();
      if (!q) {
        if (activeDropdown) activeDropdown.remove();
        activeDropdown = null;
        return;
      }
      const pool = extractNearbySuggestions(input);
      const matches = pool
        .filter(item => item.toLowerCase().includes(q))
        .slice(0, 6);

      if (!matches.length) {
        if (activeDropdown) activeDropdown.remove();
        activeDropdown = null;
        return;
      }

      const rect = input.getBoundingClientRect();
      if (!activeDropdown) {
        activeDropdown = document.createElement('div');
        activeDropdown.setAttribute('data-search-dropdown', 'true');
        activeDropdown.setAttribute('role', 'listbox');
        activeDropdown.className =
          'fixed z-[90] max-h-64 overflow-y-auto rounded-xl border border-red-200 bg-white p-1.5 shadow-xl dark:border-slate-700 dark:bg-slate-900';
        document.body.appendChild(activeDropdown);
      }

      activeDropdown.style.top = `${Math.round(rect.bottom + 6)}px`;
      activeDropdown.style.left = `${Math.round(rect.left)}px`;
      activeDropdown.style.width = `${Math.max(220, Math.round(rect.width))}px`;
      activeDropdown.innerHTML = '';
      highlightedIndex = -1;

      matches.forEach((label, idx) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('role', 'option');
        btn.dataset.idx = String(idx);
        btn.className =
          'flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs font-medium text-slate-700 transition hover:bg-red-50 hover:text-red-700 dark:text-slate-200 dark:hover:bg-red-950/40 dark:hover:text-red-300';

        const dot = document.createElement('span');
        dot.className = 'h-1.5 w-1.5 shrink-0 rounded-full bg-red-500';
        const text = document.createElement('span');
        text.className = 'truncate';
        text.textContent = label;

        btn.appendChild(dot);
        btn.appendChild(text);
        btn.addEventListener('mousedown', e => {
          e.preventDefault();
          setReactInputValue(input, label);
          closeDropdown();
        });
        activeDropdown.appendChild(btn);
      });
    }

    function onFocusIn(e) {
      if (isEligibleSearchInput(e.target)) {
        activeInput = e.target;
        renderDropdown(e.target);
      }
    }

    function onInput(e) {
      if (e.target === activeInput && isEligibleSearchInput(e.target)) {
        renderDropdown(e.target);
      }
    }

    function onKeyDown(e) {
      if (!activeInput || !activeDropdown) return;
      const opts = Array.from(activeDropdown.querySelectorAll('[role="option"]'));
      if (e.key === 'Escape') {
        closeDropdown();
        return;
      }
      if (!opts.length) return;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        highlightedIndex = (highlightedIndex + 1) % opts.length;
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        highlightedIndex = (highlightedIndex - 1 + opts.length) % opts.length;
      } else if (e.key === 'Enter' && highlightedIndex >= 0 && opts[highlightedIndex]) {
        e.preventDefault();
        opts[highlightedIndex].dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
        return;
      } else {
        return;
      }
      opts.forEach((opt, i) => {
        opt.classList.toggle('bg-red-50', i === highlightedIndex);
        opt.classList.toggle('text-red-700', i === highlightedIndex);
      });
    }

    function onMouseDown(e) {
      if (activeInput && e.target !== activeInput && (!activeDropdown || !activeDropdown.contains(e.target))) {
        closeDropdown();
      }
    }

    function onScrollOrResize() {
      if (activeInput && activeDropdown) {
        const rect = activeInput.getBoundingClientRect();
        activeDropdown.style.top = `${Math.round(rect.bottom + 6)}px`;
        activeDropdown.style.left = `${Math.round(rect.left)}px`;
        activeDropdown.style.width = `${Math.max(220, Math.round(rect.width))}px`;
      }
    }

    document.addEventListener('focusin', onFocusIn);
    document.addEventListener('input', onInput);
    document.addEventListener('keydown', onKeyDown);
    document.addEventListener('mousedown', onMouseDown);
    window.addEventListener('scroll', onScrollOrResize, true);
    window.addEventListener('resize', onScrollOrResize);

    return () => {
      closeDropdown();
      document.removeEventListener('focusin', onFocusIn);
      document.removeEventListener('input', onInput);
      document.removeEventListener('keydown', onKeyDown);
      document.removeEventListener('mousedown', onMouseDown);
      window.removeEventListener('scroll', onScrollOrResize, true);
      window.removeEventListener('resize', onScrollOrResize);
    };
  }, []);

  return null;
}

