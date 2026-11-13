import { useState, useEffect, useRef } from 'react';
import { useLocation } from 'react-router-dom';
import { startUserGuide } from '../utils/userGuide';

/**
 * Floating Guide Bubble (?) Component
 * Desain bersih, sederhana, profesional, dan non-AI look:
 * - Bentuk lingkaran bersih dengan simbol '?' tegas
 * - Warna merah DENSO solid dengan kontras tinggi
 * - Draggable (bisa dipindah) bebas ke segala sudut layar
 * - Posisi tersimpan otomatis di localStorage
 */
export default function FloatingGuideBubble() {
  const { pathname } = useLocation();
  const bubbleRef = useRef(null);
  const dragRef = useRef(null);

  // Posisi bubble (null = default CSS: bottom-6 right-6)
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
      // Klik biasa: jalankan panduan interaktif
      startUserGuide(pathname);
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

  return (
    <div
      ref={bubbleRef}
      id="tour-guide-btn"
      data-dls-action-ignore="true"
      style={style}
      onPointerDown={handlePointerDown}
      onPointerMove={handlePointerMove}
      onPointerUp={handlePointerUp}
      onPointerCancel={handlePointerUp}
      onMouseEnter={() => !isDragging && setShowTooltip(true)}
      onMouseLeave={() => setShowTooltip(false)}
      role="button"
      tabIndex={0}
      aria-label="Buka Panduan Halaman (Bisa Digeser)"
      title="Panduan Halaman (Bisa Digeser)"
      onKeyDown={(e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          startUserGuide(pathname);
        }
      }}
      className={`fixed z-50 flex items-center justify-center select-none touch-none cursor-pointer group transition-transform ${
        position ? '' : 'bottom-6 right-6'
      } ${isDragging ? 'scale-105 cursor-grabbing opacity-90' : 'hover:scale-105 active:scale-95'}`}
    >
      {/* Tooltip teks sederhana */}
      {showTooltip && !isDragging && (
        <div
          className="absolute right-full mr-2.5 whitespace-nowrap rounded-md bg-neutral-900 px-2.5 py-1 text-xs font-medium text-white shadow-md pointer-events-none"
          role="tooltip"
        >
          Panduan Halaman (Bisa Digeser)
          <span className="absolute -right-1 top-1/2 -translate-y-1/2 border-4 border-transparent border-l-neutral-900" />
        </div>
      )}

      {/* Tombol Bulat Simpel & Bersih */}
      <div className="flex size-11 items-center justify-center rounded-full bg-primary text-white shadow-md hover:bg-primary-container border-2 border-white dark:border-slate-800 transition-colors">
        <span className="text-xl font-bold leading-none select-none font-sans">
          ?
        </span>
      </div>
    </div>
  );
}
