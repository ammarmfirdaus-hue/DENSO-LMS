import { useState, useEffect, useRef } from 'react';
import { useLocation } from 'react-router-dom';
import { getPageGuideInfo, startUserGuide } from '../utils/userGuide';

export default function FloatingGuideBubble({ role }) {
  const { pathname } = useLocation();
  const bubbleRef = useRef(null);
  const menuRef = useRef(null);
  const dragRef = useRef(null);

  const [position, setPosition] = useState(() => {
    try {
      const saved = localStorage.getItem('dls_guide_bubble_pos');
      if (saved) {
        const parsed = JSON.parse(saved);
        if (typeof parsed.x === 'number' && typeof parsed.y === 'number') {
          return parsed;
        }
      }
    } catch {}
    return null;
  });

  const [isDragging, setIsDragging] = useState(false);
  const [showTooltip, setShowTooltip] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  const [guideInfo, setGuideInfo] = useState(null);

  function clampPosition(x, y, el) {
    const margin = 12;
    const width = el?.offsetWidth || 48;
    const height = el?.offsetHeight || 48;
    const maxX = Math.max(margin, window.innerWidth - width - margin);
    const maxY = Math.max(margin, window.innerHeight - height - margin);
    return {
      x: Math.min(Math.max(margin, x), maxX),
      y: Math.min(Math.max(margin, y), maxY)
    };
  }

  function toggleMenu() {
    if (!menuOpen) {
      setGuideInfo(getPageGuideInfo(pathname, role));
      setMenuOpen(true);
      setShowTooltip(false);
    } else {
      setMenuOpen(false);
    }
  }

  useEffect(() => {
    setMenuOpen(false);
  }, [pathname]);

  useEffect(() => {
    if (!menuOpen) return;
    function handleOutside(e) {
      if (
        menuRef.current &&
        !menuRef.current.contains(e.target) &&
        bubbleRef.current &&
        !bubbleRef.current.contains(e.target)
      ) {
        setMenuOpen(false);
      }
    }
    function handleKey(e) {
      if (e.key === 'Escape') setMenuOpen(false);
    }
    document.addEventListener('pointerdown', handleOutside);
    document.addEventListener('keydown', handleKey);
    return () => {
      document.removeEventListener('pointerdown', handleOutside);
      document.removeEventListener('keydown', handleKey);
    };
  }, [menuOpen]);

  function handlePointerDown(e) {
    if (e.button !== 0 && e.pointerType === 'mouse') return;
    const el = bubbleRef.current;
    if (!el) return;

    const rect = el.getBoundingClientRect();
    dragRef.current = {
      pointerId: e.pointerId,
      startX: e.clientX,
      startY: e.clientY,
      initialLeft: rect.left,
      initialTop: rect.top,
      hasMoved: false
    };

    try {
      el.setPointerCapture(e.pointerId);
    } catch {}
  }

  function handlePointerMove(e) {
    if (!dragRef.current || dragRef.current.pointerId !== e.pointerId) return;
    const dx = e.clientX - dragRef.current.startX;
    const dy = e.clientY - dragRef.current.startY;

    if (!dragRef.current.hasMoved && (Math.abs(dx) > 4 || Math.abs(dy) > 4)) {
      dragRef.current.hasMoved = true;
      setIsDragging(true);
      setShowTooltip(false);
      setMenuOpen(false);
    }

    if (dragRef.current.hasMoved) {
      const nextX = dragRef.current.initialLeft + dx;
      const nextY = dragRef.current.initialTop + dy;
      const clamped = clampPosition(nextX, nextY, bubbleRef.current);
      setPosition(clamped);
    }
  }

  function handlePointerUp(e) {
    if (!dragRef.current || dragRef.current.pointerId !== e.pointerId) return;
    const moved = dragRef.current.hasMoved;
    try {
      if (bubbleRef.current?.hasPointerCapture(e.pointerId)) {
        bubbleRef.current.releasePointerCapture(e.pointerId);
      }
    } catch {}

    dragRef.current = null;
    setIsDragging(false);

    if (moved && position) {
      try {
        localStorage.setItem('dls_guide_bubble_pos', JSON.stringify(position));
      } catch {}
    } else if (!moved) {
      toggleMenu();
    }
  }

  useEffect(() => {
    function handleResize() {
      if (position && bubbleRef.current) {
        setPosition(prev => clampPosition(prev.x, prev.y, bubbleRef.current));
      }
    }
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, [position]);

  const style = position
    ? { left: `${position.x}px`, top: `${position.y}px`, right: 'auto', bottom: 'auto' }
    : undefined;

  // Hitung posisi menu agar selalu responsif & muat di layar
  const rect = bubbleRef.current?.getBoundingClientRect();
  const isSmallScreen = typeof window !== 'undefined' && window.innerWidth < 640;
  const openLeft = !rect || rect.left > window.innerWidth / 2;
  const openUp = !rect || rect.top > window.innerHeight / 2;

  const menuPositionStyle = isSmallScreen
    ? {
        position: 'fixed',
        zIndex: 60,
        left: '12px',
        right: '12px',
        width: 'auto',
        maxHeight: '72vh',
        overflowY: 'auto',
        bottom: `${Math.max(76, rect ? window.innerHeight - rect.top + 10 : 76)}px`
      }
    : rect
      ? {
          position: 'fixed',
          zIndex: 60,
          width: 'min(340px, calc(100vw - 24px))',
          ...(openLeft
            ? { right: `${Math.max(12, window.innerWidth - rect.right)}px` }
            : { left: `${Math.max(12, rect.left)}px` }),
          ...(openUp
            ? { bottom: `${Math.max(12, window.innerHeight - rect.top + 10)}px` }
            : { top: `${Math.max(12, rect.bottom + 10)}px` })
        }
      : { position: 'fixed', zIndex: 60, right: '24px', bottom: '80px', width: 'min(340px, calc(100vw - 24px))' };

  return (
    <>
      {menuOpen && guideInfo && (
        <div
          ref={menuRef}
          data-dls-action-ignore="true"
          style={menuPositionStyle}
          className="rounded-xl border border-outline bg-surface p-4 text-on-surface shadow-xl dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
        >
          <div className="flex items-start justify-between gap-2">
            <div>
              <span className="inline-block rounded bg-primary/10 px-2 py-0.5 text-[11px] font-semibold text-primary">
                {guideInfo.roleLabel}
              </span>
              <h3 className="mt-1 text-sm font-bold">{guideInfo.pageName}</h3>
            </div>
            <button
              type="button"
              onClick={() => setMenuOpen(false)}
              className="rounded p-1 text-xs text-muted hover:bg-surface-low"
              aria-label="Tutup panduan"
            >
              ✕
            </button>
          </div>

          <p className="mt-1.5 text-xs leading-relaxed text-muted dark:text-slate-300">
            {guideInfo.roleSummary}
          </p>

          {guideInfo.sections.length > 0 && (
            <div className="mt-3">
              <p className="mb-1.5 text-[11px] font-semibold uppercase tracking-wide text-muted">
                Sorot Bagian Halaman (Interaktif)
              </p>
              <div className="max-h-48 space-y-1 overflow-y-auto pr-1">
                {guideInfo.sections.map((sec, i) => (
                  <button
                    key={sec.label + i}
                    type="button"
                    onClick={() => {
                      setMenuOpen(false);
                      startUserGuide(pathname, role, sec.index, false);
                    }}
                    className="flex w-full items-center justify-between rounded-lg border border-outline/60 px-2.5 py-1.5 text-left text-xs font-medium hover:border-primary hover:bg-primary/5 dark:border-slate-700"
                  >
                    <span className="truncate">{sec.label}</span>
                    <span className="ml-2 text-[11px] text-primary">Lihat →</span>
                  </button>
                ))}
              </div>
            </div>
          )}

          <button
            type="button"
            onClick={() => {
              setMenuOpen(false);
              startUserGuide(pathname, role, 0, true);
            }}
            className="mt-3 w-full rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white hover:bg-primary-container transition-colors"
          >
            Mulai Tur Lengkap Halaman Ini
          </button>
        </div>
      )}

      <div
        ref={bubbleRef}
        id="tour-guide-btn"
        data-dls-action-ignore="true"
        style={style}
        onPointerDown={handlePointerDown}
        onPointerMove={handlePointerMove}
        onPointerUp={handlePointerUp}
        onPointerCancel={handlePointerUp}
        onMouseEnter={() => !isDragging && !menuOpen && setShowTooltip(true)}
        onMouseLeave={() => setShowTooltip(false)}
        role="button"
        tabIndex={0}
        aria-label="Buka Panduan Interaktif Halaman (Bisa Digeser)"
        title="Panduan Interaktif Halaman (Bisa Digeser)"
        onKeyDown={(e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            toggleMenu();
          }
        }}
        className={`fixed z-50 flex items-center justify-center select-none touch-none cursor-pointer group transition-transform ${
          position ? '' : 'bottom-6 right-6'
        } ${isDragging ? 'scale-105 cursor-grabbing opacity-90' : 'hover:scale-105 active:scale-95'}`}
      >
        {showTooltip && !isDragging && !menuOpen && (
          <div
            className="absolute right-full mr-2.5 whitespace-nowrap rounded-md bg-neutral-900 px-2.5 py-1 text-xs font-medium text-white shadow-md pointer-events-none"
            role="tooltip"
          >
            Panduan Interaktif (Klik / Geser)
            <span className="absolute -right-1 top-1/2 -translate-y-1/2 border-4 border-transparent border-l-neutral-900" />
          </div>
        )}

        <div className="flex size-11 items-center justify-center rounded-full bg-primary text-white shadow-md hover:bg-primary-container border-2 border-white dark:border-slate-800 transition-colors">
          <span className="text-xl font-bold leading-none select-none font-sans">
            ?
          </span>
        </div>
      </div>
    </>
  );
}
