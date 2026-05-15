'use client';

import { ReactNode, useRef, useState } from 'react';

interface Props {
  onClose: () => void;
  children: ReactNode;
  /** Tailwind max-width class. Defaults to max-w-sm. */
  maxWidth?: string;
  /** Tailwind/extra classes appended to the sheet container. */
  className?: string;
  /** Bottom padding token. Defaults to pb-8. */
  paddingBottom?: string;
  /** When true, backdrop tap and swipe-down do nothing. Use during critical flows (e.g. purchase loading). */
  disabled?: boolean;
}

const CLOSE_THRESHOLD_PX = 120;
const FLICK_THRESHOLD_PX = 40;
const FLICK_VELOCITY = 0.5;

export default function BottomSheet({
  onClose,
  children,
  maxWidth = 'max-w-sm',
  className = '',
  paddingBottom = 'pb-8',
  disabled = false,
}: Props) {
  const [translateY, setTranslateY] = useState(0);
  const [closing, setClosing] = useState(false);
  const scrollRef = useRef<HTMLDivElement>(null);
  const startY = useRef(0);
  const lastY = useRef(0);
  const lastT = useRef(0);
  const dragging = useRef(false);

  const startClose = () => {
    setClosing(true);
    setTranslateY(window.innerHeight);
    window.setTimeout(onClose, 200);
  };

  const handleTouchStart = (e: React.TouchEvent) => {
    if (disabled) return;
    startY.current = e.touches[0].clientY;
    lastY.current = startY.current;
    lastT.current = Date.now();
    dragging.current = false;
  };

  const handleTouchMove = (e: React.TouchEvent) => {
    if (disabled) return;
    const y = e.touches[0].clientY;
    const dy = y - startY.current;
    const atTop = (scrollRef.current?.scrollTop ?? 0) <= 0;

    if (dy > 0 && atTop) {
      dragging.current = true;
      setTranslateY(dy);
    } else if (dragging.current && dy > 0) {
      setTranslateY(dy);
    } else if (dragging.current && dy <= 0) {
      setTranslateY(0);
      dragging.current = false;
    }

    lastY.current = y;
    lastT.current = Date.now();
  };

  const handleTouchEnd = () => {
    if (disabled) return;
    if (!dragging.current) {
      setTranslateY(0);
      return;
    }
    const now = Date.now();
    const dt = Math.max(now - lastT.current, 16);
    const totalDy = lastY.current - startY.current;
    const velocity = totalDy / dt;

    if (totalDy > CLOSE_THRESHOLD_PX || (totalDy > FLICK_THRESHOLD_PX && velocity > FLICK_VELOCITY)) {
      startClose();
    } else {
      setTranslateY(0);
    }
    dragging.current = false;
  };

  return (
    <div
      className="fixed inset-0 z-50 flex items-end justify-center"
      style={{ background: closing ? 'rgba(0,0,0,0)' : 'rgba(0,0,0,0.5)', transition: 'background 0.2s ease-out' }}
      onClick={disabled ? undefined : onClose}
    >
      <div
        ref={scrollRef}
        className={`w-full ${maxWidth} bg-white rounded-t-3xl px-5 pt-4 ${paddingBottom} max-h-[92vh] overflow-y-auto ${className}`}
        style={{
          transform: `translateY(${translateY}px)`,
          transition: dragging.current ? 'none' : 'transform 0.2s ease-out',
          animation: translateY === 0 && !closing ? 'slide-up 0.25s ease-out' : undefined,
        }}
        onClick={(e) => e.stopPropagation()}
        onTouchStart={handleTouchStart}
        onTouchMove={handleTouchMove}
        onTouchEnd={handleTouchEnd}
      >
        <div className="w-10 h-1 bg-stone-200 rounded-full mx-auto mb-5" />
        {children}
      </div>
    </div>
  );
}
