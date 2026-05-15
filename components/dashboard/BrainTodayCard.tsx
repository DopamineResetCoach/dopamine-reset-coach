'use client';

import { useMemo, useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { computeBrainState, Level } from '@/lib/brainState';
import { useT } from '@/hooks/useT';
import BrainMetricsInfoModal from './BrainMetricsInfoModal';

function MetricChip({ label, level, t }: { label: string; level: Level; t: ReturnType<typeof useT> }) {
  const config = {
    high: { color: '#5B8A5E', bg: 'rgba(91,138,94,0.10)', text: t.brainLevelHigh },
    medium: { color: '#B8985A', bg: 'rgba(184,152,90,0.12)', text: t.brainLevelMedium },
    low: { color: '#C97B5B', bg: 'rgba(201,123,91,0.12)', text: t.brainLevelLow },
  }[level];

  return (
    <div className="flex-1 rounded-xl px-3 py-2.5" style={{ background: config.bg }}>
      <p className="text-stone-500 text-[10px] font-medium uppercase tracking-wider truncate">
        {label}
      </p>
      <p className="font-bold text-sm mt-0.5" style={{ color: config.color }}>
        {config.text}
      </p>
    </div>
  );
}

function applyParams(template: string, params: Record<string, string | number>): string {
  let out = template;
  for (const [key, value] of Object.entries(params)) {
    out = out.replaceAll(`{${key}}`, String(value));
  }
  return out;
}

export default function BrainTodayCard() {
  const { dailyLogs, profile, todaySteps, stepGoal } = useAppStore();
  const t = useT();
  const [infoOpen, setInfoOpen] = useState(false);

  const brainState = useMemo(() => {
    if (!profile) return null;
    return computeBrainState(dailyLogs, profile, todaySteps, stepGoal);
  }, [dailyLogs, profile, todaySteps, stepGoal]);

  if (!profile || !brainState) return null;

  const templateMap: Record<number, string> = {
    1: t.brainState1, 2: t.brainState2, 3: t.brainState3, 4: t.brainState4, 5: t.brainState5,
    6: t.brainState6, 7: t.brainState7, 8: t.brainState8, 9: t.brainState9, 10: t.brainState10,
    11: t.brainState11, 12: t.brainState12, 13: t.brainState13, 14: t.brainState14, 15: t.brainState15,
    16: t.brainState16, 17: t.brainState17, 18: t.brainState18, 19: t.brainState19, 20: t.brainState20,
    21: t.brainState21, 22: t.brainState22, 23: t.brainState23, 24: t.brainState24, 25: t.brainState25,
    26: t.brainState26, 27: t.brainState27, 28: t.brainState28, 29: t.brainState29, 30: t.brainState30,
  };

  const headline = applyParams(templateMap[brainState.stateKey] ?? t.brainState25, brainState.params);

  return (
    <div className="bg-white rounded-3xl p-5 mb-4 shadow-sm">
      <div className="flex items-center gap-2 mb-3">
        <span className="text-stone-400 text-[10px] font-bold uppercase tracking-widest">
          {t.brainTodayTitle}
        </span>
        <span className="h-px flex-1 bg-stone-100" />
        <button
          onClick={() => setInfoOpen(true)}
          className="w-5 h-5 rounded-full flex items-center justify-center text-stone-300 active:text-stone-500 transition-colors"
          aria-label={t.brainInfoTitle}
        >
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <circle cx="8" cy="8" r="7" stroke="currentColor" strokeWidth="1.4" />
            <path d="M8 11.5v-3.8M8 5.5v.4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
          </svg>
        </button>
      </div>

      <p className="text-stone-800 text-[15px] leading-relaxed font-medium mb-4">
        {headline}
      </p>

      <div className="flex gap-2">
        <MetricChip label={t.brainMetricFocus} level={brainState.metrics.focus} t={t} />
        <MetricChip label={t.brainMetricImpulse} level={brainState.metrics.impulse} t={t} />
        <MetricChip label={t.brainMetricRecovery} level={brainState.metrics.recovery} t={t} />
      </div>

      {infoOpen && <BrainMetricsInfoModal onClose={() => setInfoOpen(false)} />}
    </div>
  );
}
