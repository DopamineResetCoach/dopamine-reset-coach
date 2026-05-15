'use client';

import { useState, useEffect } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { useT } from '@/hooks/useT';
import { UrgeType } from '@/types';
import type { Translations } from '@/lib/i18n';

type Stage = 'select' | 'breathing' | 'reframe' | 'delay' | 'outcome';

const BREATHING_PHASES_CONFIG = [
  { key: 'urgeInhale' as keyof Translations, duration: 4, color: '#5B8A5E' },
  { key: 'urgeHold' as keyof Translations, duration: 4, color: '#C9955A' },
  { key: 'urgeExhale' as keyof Translations, duration: 4, color: '#6B9FD4' },
  { key: 'urgeHold' as keyof Translations, duration: 4, color: '#9CA3AF' },
];

function BreathingExercise({ onDone }: { onDone: () => void }) {
  const t = useT();
  const [phase, setPhase] = useState(0);
  const [count, setCount] = useState(BREATHING_PHASES_CONFIG[0].duration);
  const [cycles, setCycles] = useState(0);
  const totalCycles = 4;

  useEffect(() => {
    const tick = setInterval(() => {
      setCount((c) => {
        if (c <= 1) {
          const nextPhase = (phase + 1) % BREATHING_PHASES_CONFIG.length;
          if (nextPhase === 0) {
            const nextCycle = cycles + 1;
            if (nextCycle >= totalCycles) {
              clearInterval(tick);
              setTimeout(onDone, 600);
              return 0;
            }
            setCycles(nextCycle);
          }
          setPhase(nextPhase);
          return BREATHING_PHASES_CONFIG[nextPhase].duration;
        }
        return c - 1;
      });
    }, 1000);
    return () => clearInterval(tick);
  }, [phase, cycles, onDone]);

  const currentPhase = BREATHING_PHASES_CONFIG[phase];
  const remaining = totalCycles - cycles;
  const cycleText = t.urgeBreathingCycles
    .replace('{n}', String(remaining))
    .replace('{s}', remaining !== 1 ? 's' : '');

  return (
    <div className="flex flex-col items-center py-8">
      <p className="text-stone-500 text-sm mb-6">{cycleText}</p>
      <div className="relative mb-6">
        <div
          className="w-36 h-36 rounded-full flex items-center justify-center"
          style={{
            background: `radial-gradient(circle, ${currentPhase.color}22, ${currentPhase.color}08)`,
            boxShadow: `0 0 0 ${4 + count * 2}px ${currentPhase.color}18`,
            transition: 'all 0.8s ease',
          }}
        >
          <div className="text-center">
            <p className="text-4xl font-bold tabular-nums" style={{ color: currentPhase.color }}>
              {count}
            </p>
            <p className="text-sm font-semibold mt-1" style={{ color: currentPhase.color }}>
              {t[currentPhase.key] as string}
            </p>
          </div>
        </div>
      </div>
      <div className="flex gap-2">
        {Array.from({ length: totalCycles }).map((_, i) => (
          <div
            key={i}
            className={`w-2 h-2 rounded-full transition-all ${
              i < cycles ? 'bg-[#5B8A5E]' : i === cycles ? 'bg-[#5B8A5E]/40' : 'bg-stone-200'
            }`}
          />
        ))}
      </div>
      <p className="text-stone-400 text-xs mt-6 text-center max-w-xs leading-relaxed">
        {t.urgeBreathingNote}
      </p>
    </div>
  );
}

function ReframeStep({ urgeType, onDone }: { urgeType: UrgeType; onDone: () => void }) {
  const t = useT();

  const reframeMap: Record<UrgeType, string> = {
    scrolling: t.reframeScrolling,
    porn: t.reframeAdultContent,
    junk_food: t.reframeJunkFood,
    sugar: t.reframeSugar,
    gaming: t.reframeGaming,
    alcohol: t.reframeAlcohol,
    caffeine: t.reframeCaffeine,
    other: t.reframeOther,
  };

  return (
    <div className="flex flex-col items-center py-6">
      <div className="w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center mb-6">
        <span className="text-3xl">🧠</span>
      </div>
      <h3 className="text-stone-800 font-bold text-base mb-4 text-center">
        {t.urgeReframeTitle}
      </h3>
      <div className="bg-stone-50 rounded-2xl p-5 mb-6">
        <p className="text-stone-600 text-sm leading-relaxed text-center">
          {reframeMap[urgeType]}
        </p>
      </div>
      <p className="text-stone-400 text-xs text-center mb-8">{t.urgeReadIt}</p>
      <button
        onClick={onDone}
        className="w-full py-4 rounded-2xl bg-[#5B8A5E] text-white font-semibold text-base active:scale-[0.98] transition-transform"
      >
        {t.urgeReframeBtn}
      </button>
    </div>
  );
}

function DelayStep({ onDone }: { onDone: (passed: boolean) => void }) {
  const t = useT();
  const [seconds, setSeconds] = useState(120);

  useEffect(() => {
    const timer = setInterval(() => {
      setSeconds((s) => {
        if (s <= 1) { clearInterval(timer); return 0; }
        return s - 1;
      });
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  const progress = (120 - seconds) / 120;

  return (
    <div className="flex flex-col items-center py-6">
      <h3 className="text-stone-800 font-bold text-base mb-2 text-center">{t.urgeDelayTitle}</h3>
      <p className="text-stone-400 text-sm text-center mb-6 leading-relaxed">{t.urgeDelaySub}</p>
      <div className="relative mb-6">
        <svg width="120" height="120" viewBox="0 0 120 120">
          <circle cx="60" cy="60" r="50" fill="none" stroke="#E8E3DC" strokeWidth="8" />
          <circle
            cx="60" cy="60" r="50" fill="none" stroke="#5B8A5E" strokeWidth="8"
            strokeLinecap="round"
            strokeDasharray={`${2 * Math.PI * 50}`}
            strokeDashoffset={`${2 * Math.PI * 50 * (1 - progress)}`}
            transform="rotate(-90 60 60)"
            style={{ transition: 'stroke-dashoffset 1s linear' }}
          />
        </svg>
        <div className="absolute inset-0 flex items-center justify-center">
          <span className="text-2xl font-bold text-stone-700 tabular-nums">
            {mins}:{String(secs).padStart(2, '0')}
          </span>
        </div>
      </div>
      <div className="bg-stone-50 rounded-2xl p-4 mb-6 w-full">
        <p className="text-stone-600 text-sm leading-relaxed text-center">{t.urgeDelaySuggestion}</p>
      </div>
      <div className="flex gap-3 w-full">
        <button
          onClick={() => onDone(false)}
          className="flex-1 py-3 rounded-2xl border-2 border-stone-200 text-stone-500 font-semibold text-sm"
        >
          {t.urgeStillThere}
        </button>
        <button
          onClick={() => onDone(true)}
          className="flex-[2] py-3 rounded-2xl bg-[#5B8A5E] text-white font-semibold text-base active:scale-[0.98] transition-transform"
        >
          {t.urgePassedBtn}
        </button>
      </div>
    </div>
  );
}

function OutcomeStep({ passed, onClose }: { passed: boolean; onClose: () => void }) {
  const t = useT();
  return (
    <div className="flex flex-col items-center py-8">
      <div className="text-6xl mb-4">{passed ? '🏆' : '💪'}</div>
      <h3 className="text-stone-800 font-bold text-xl mb-3 text-center">
        {passed ? t.urgeOutcomePassedTitle : t.urgeOutcomeShownTitle}
      </h3>
      <p className="text-stone-500 text-sm text-center leading-relaxed mb-8 max-w-xs">
        {passed ? t.urgeOutcomePassedBody : t.urgeOutcomeShownBody}
      </p>
      {passed && (
        <div className="bg-[#5B8A5E]/8 border border-[#5B8A5E]/20 rounded-2xl px-4 py-3 mb-6 w-full text-center">
          <p className="text-[#3D6640] font-semibold text-sm">{t.urgePoints}</p>
        </div>
      )}
      <button
        onClick={onClose}
        className="w-full py-4 rounded-2xl bg-[#5B8A5E] text-white font-semibold text-base active:scale-[0.98] transition-transform"
      >
        {t.urgeBackBtn}
      </button>
    </div>
  );
}

export default function UrgeTracker() {
  const t = useT();
  const [open, setOpen] = useState(false);
  const [stage, setStage] = useState<Stage>('select');
  const [urgeType, setUrgeType] = useState<UrgeType>('scrolling');
  const [passed, setPassed] = useState(false);
  const logUrge = useAppStore((s) => s.logUrge);

  const URGE_TYPES: { id: UrgeType; label: string; emoji: string }[] = [
    { id: 'scrolling', label: t.habitScrolling, emoji: '📱' },
    { id: 'porn', label: t.habitAdultContentShort, emoji: '🔞' },
    { id: 'junk_food', label: t.habitJunkFoodShort, emoji: '🍔' },
    { id: 'sugar', label: t.habitSugar, emoji: '🍬' },
    { id: 'gaming', label: t.habitGamingShort, emoji: '🎮' },
    { id: 'alcohol', label: t.habitAlcoholShort, emoji: '🍺' },
    { id: 'caffeine', label: t.habitCaffeineShort, emoji: '☕' },
    { id: 'other', label: t.habitOther, emoji: '❓' },
  ];

  const reset = () => {
    setStage('select');
    setUrgeType('scrolling');
    setPassed(false);
  };

  const handleClose = () => {
    setOpen(false);
    setTimeout(reset, 300);
  };

  const handleOutcome = (didPass: boolean) => {
    setPassed(didPass);
    logUrge({ type: urgeType, completedIntervention: didPass });
    setStage('outcome');
  };

  if (!open) {
    return (
      <button
        onClick={() => setOpen(true)}
        aria-label={t.urgeBtn}
        className="fixed bottom-[calc(env(safe-area-inset-bottom)+140px)] right-4 z-40 w-12 h-12 rounded-full bg-white shadow-lg border border-stone-100 flex items-center justify-center active:scale-95 transition-all"
        style={{ animation: 'pulse-soft 3s ease-in-out infinite' }}
      >
        <span className="text-xl">⚡</span>
      </button>
    );
  }

  return (
    <>
      <div className="fixed inset-0 bg-black/30 z-40 modal-backdrop" onClick={handleClose} />
      <div
        className="fixed bottom-0 left-0 right-0 z-50 flex justify-center"
        style={{ animation: 'slide-up 0.4s cubic-bezier(0.16,1,0.3,1)' }}
      >
        <div className="relative w-full max-w-sm bg-white rounded-t-3xl pt-3 pb-safe overflow-hidden">
          <div className="w-10 h-1 bg-stone-200 rounded-full mx-auto mb-4" />
          <button
            onClick={handleClose}
            className="absolute top-2 right-2 w-8 h-8 flex items-center justify-center rounded-full text-stone-400 active:text-stone-600 active:bg-stone-100 transition-colors z-10"
            aria-label={t.ariaClose}
          >
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
              <path d="M2 2l10 10M12 2L2 12" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round"/>
            </svg>
          </button>
          <div className="px-5 pb-8 max-h-[85vh] overflow-y-auto">
            {stage === 'select' && (
              <div>
                <h3 className="text-stone-800 font-bold text-base mb-1">{t.urgeSelectTitle}</h3>
                <p className="text-stone-400 text-sm mb-4">{t.urgeSelectSub}</p>
                <div className="grid grid-cols-2 gap-2 mb-4">
                  {URGE_TYPES.map((u) => (
                    <button
                      key={u.id}
                      onClick={() => setUrgeType(u.id)}
                      className={`flex items-center gap-2 px-3 py-3 rounded-2xl border-2 transition-all ${
                        urgeType === u.id
                          ? 'border-[#5B8A5E] bg-[#5B8A5E]/8'
                          : 'border-stone-100 bg-stone-50'
                      }`}
                    >
                      <span className="text-xl">{u.emoji}</span>
                      <span className={`text-sm font-medium ${urgeType === u.id ? 'text-[#3D6640]' : 'text-stone-600'}`}>
                        {u.label}
                      </span>
                    </button>
                  ))}
                </div>
                <button
                  onClick={() => setStage('breathing')}
                  className="w-full py-4 rounded-2xl bg-[#5B8A5E] text-white font-semibold text-base active:scale-[0.98] transition-transform"
                >
                  {t.urgeStartBtn}
                </button>
              </div>
            )}
            {stage === 'breathing' && (
              <div>
                <h3 className="text-stone-800 font-bold text-base mb-1 text-center">{t.urgeBreatheTitle}</h3>
                <BreathingExercise onDone={() => setStage('reframe')} />
              </div>
            )}
            {stage === 'reframe' && <ReframeStep urgeType={urgeType} onDone={() => setStage('delay')} />}
            {stage === 'delay' && <DelayStep onDone={handleOutcome} />}
            {stage === 'outcome' && <OutcomeStep passed={passed} onClose={handleClose} />}
          </div>
        </div>
      </div>
    </>
  );
}
