'use client';

import { useMemo } from 'react';
import { useT } from '@/hooks/useT';

interface Props {
  onContinue: () => void;
}

// Pre-generated particle config so positions stay stable across re-renders
const PARTICLE_COUNT = 14;

export default function WelcomeIntro({ onContinue }: Props) {
  const t = useT();

  const particles = useMemo(() => {
    return Array.from({ length: PARTICLE_COUNT }).map((_, i) => {
      // Deterministic pseudo-random spread so SSR + client match
      const seed = (i + 1) * 1.618;
      const left = (seed * 37) % 100;
      const drift = ((seed * 53) % 80) - 40;
      const delay = (seed * 17) % 6;
      const size = 3 + ((seed * 7) % 6);
      const opacityBase = 0.4 + ((seed * 11) % 30) / 100;
      return { left, drift, delay, size, opacityBase, key: i };
    });
  }, []);

  return (
    <div
      className="welcome-bg fixed inset-0 z-50 flex flex-col items-center justify-center overflow-hidden"
      style={{ animation: 'fade-in 0.6s ease-out' }}
    >
      {/* Floating particles */}
      <div className="absolute inset-0 pointer-events-none">
        {particles.map((p) => (
          <span
            key={p.key}
            className="welcome-particle absolute bottom-0 rounded-full bg-white"
            style={{
              left: `${p.left}%`,
              width: `${p.size}px`,
              height: `${p.size}px`,
              opacity: p.opacityBase,
              animationDelay: `${p.delay}s`,
              ['--drift' as string]: `${p.drift}px`,
            }}
          />
        ))}
      </div>

      {/* Brain orb */}
      <div className="relative w-56 h-56 mb-10 flex items-center justify-center">
        {/* Outermost pulsing rings */}
        <div
          className="welcome-pulse-ring absolute inset-0 rounded-full"
          style={{ background: 'radial-gradient(circle, rgba(255,255,255,0.18), transparent 70%)' }}
        />
        <div
          className="welcome-pulse-ring-2 absolute inset-0 rounded-full"
          style={{ background: 'radial-gradient(circle, rgba(255,255,255,0.12), transparent 70%)' }}
        />

        {/* Slow-spinning rings with neuron dots */}
        <div className="welcome-spin-slow absolute inset-4 rounded-full border border-white/20">
          {[0, 90, 180, 270].map((deg) => (
            <span
              key={deg}
              className="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-white/80"
              style={{ transform: `rotate(${deg}deg) translate(96px) translate(-50%, -50%)`, transformOrigin: 'center' }}
            />
          ))}
        </div>
        <div className="welcome-spin-slower absolute inset-10 rounded-full border border-white/15">
          {[45, 135, 225, 315].map((deg) => (
            <span
              key={deg}
              className="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-white/60"
              style={{ transform: `rotate(${deg}deg) translate(72px) translate(-50%, -50%)`, transformOrigin: 'center' }}
            />
          ))}
        </div>

        {/* Inner glow core */}
        <div
          className="welcome-glow absolute inset-14 rounded-full"
          style={{
            background:
              'radial-gradient(circle, rgba(255,255,255,0.55), rgba(91,138,94,0.4) 60%, transparent 100%)',
            filter: 'blur(2px)',
          }}
        />

        {/* Brain emoji center */}
        <span className="welcome-glow relative text-6xl drop-shadow-[0_0_24px_rgba(255,255,255,0.4)]">
          🧠
        </span>
      </div>

      {/* Eyebrow */}
      <p className="welcome-fade-1 text-white/70 text-xs font-bold uppercase tracking-[0.4em] mb-3">
        {t.welcomeIntroEyebrow}
      </p>

      {/* Headline */}
      <h1 className="welcome-fade-2 text-white text-3xl font-bold text-center leading-tight px-8 max-w-sm">
        {t.welcomeIntroHeadline}
      </h1>

      {/* Body */}
      <p className="welcome-fade-3 text-white/80 text-sm text-center leading-relaxed px-10 mt-5 max-w-sm">
        {t.welcomeIntroBody}
      </p>

      {/* Subtle decorative line */}
      <div className="welcome-fade-4 w-12 h-px bg-white/30 mt-10" />

      {/* CTA */}
      <button
        onClick={onContinue}
        className="welcome-fade-5 mt-8 px-10 py-4 rounded-full bg-white text-[#2D3A2E] font-bold text-sm tracking-wide shadow-2xl active:scale-95 transition-transform"
      >
        {t.welcomeIntroCta}
      </button>
    </div>
  );
}
