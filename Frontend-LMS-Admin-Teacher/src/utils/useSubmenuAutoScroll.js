import { useEffect, useRef } from 'react';
import { useLocation } from 'react-router-dom';

function resolveTargetId(location) {
  const rawHash = location.hash ? location.hash.replace(/^#/, '').trim() : '';
  if (rawHash) return rawHash;

  if (location.pathname === '/materials') {
    const view = new URLSearchParams(location.search).get('view');
    if (view === 'published') return 'published-materials-section';
  }
  return '';
}

export function scrollToTargetById(targetId) {
  if (!targetId) return false;
  const el = document.getElementById(targetId);
  if (!el) return false;
  el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  return true;
}

/**
 * Automatically scrolls the viewport to the submenu target section (tracking initial
 * mount and brief async layout shifts) or smoothly back to top on standalone route changes.
 */
export default function useSubmenuAutoScroll() {
  const location = useLocation();
  const prevPathnameRef = useRef(location.pathname);

  useEffect(() => {
    const targetId = resolveTargetId(location);
    const pathnameChanged = prevPathnameRef.current !== location.pathname;
    prevPathnameRef.current = location.pathname;

    if (!targetId) {
      if (pathnameChanged) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
      return undefined;
    }

    let cancelled = false;
    let mutationObserver = null;
    let resizeObserver = null;
    let settleTimer = null;
    let rafId = 0;

    const cleanup = () => {
      cancelled = true;
      if (rafId) window.cancelAnimationFrame(rafId);
      if (settleTimer) window.clearTimeout(settleTimer);
      mutationObserver?.disconnect();
      resizeObserver?.disconnect();
      window.removeEventListener('wheel', cleanup);
      window.removeEventListener('touchstart', cleanup);
    };

    const alignTarget = () => {
      if (cancelled) return false;
      const el = document.getElementById(targetId);
      if (!el) return false;
      const desiredTop = 80; // Matches scroll-mt-20 clearance under 64px sticky header
      const { top } = el.getBoundingClientRect();
      if (Math.abs(top - desiredTop) > 24) {
        if (rafId) window.cancelAnimationFrame(rafId);
        rafId = window.requestAnimationFrame(() => {
          if (!cancelled) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      }
      return true;
    };

    alignTarget();

    window.addEventListener('wheel', cleanup, { passive: true, once: true });
    window.addEventListener('touchstart', cleanup, { passive: true, once: true });

    mutationObserver = new MutationObserver(() => alignTarget());
    mutationObserver.observe(document.body, { childList: true, subtree: true });

    if (typeof ResizeObserver !== 'undefined') {
      resizeObserver = new ResizeObserver(() => alignTarget());
      resizeObserver.observe(document.body);
    }

    settleTimer = window.setTimeout(cleanup, 1500);

    return cleanup;
  }, [location.pathname, location.search, location.hash, location.state]);
}
