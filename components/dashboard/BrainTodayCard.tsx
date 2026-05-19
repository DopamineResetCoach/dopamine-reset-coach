'use client';

import { useMemo, useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { computeBrainState, computeMetricDeltas, percentageToLevel } from '@/lib/brainState';
import { useT } from '@/hooks/useT';
import BrainMetricsInfoModal from './BrainMetricsInfoModal';

function MetricChip({ label, value, delta }: { label: string; value: number; delta: number | null }) {
  const palette = {
    high: { color: '#5B8A5E', bg: 'rgba(91,138,94,0.10)' },
    medium: { color: '#B8985A', bg: 'rgba(184,152,90,0.12)' },
    low: { color: '#C97B5B', bg: 'rgba(201,123,91,0.12)' },
  }[percentageToLevel(value)];

  // Negative delta is shown amber-warm regardless of chip color, so the
  // "down vs avg" signal reads consistently across all three metrics.
  const deltaColor = delta == null || delta === 0
    ? '#A8A29E'
    : delta > 0
    ? '#5B8A5E'
    : '#C97B5B';

  return (
    <div className="flex-1 rounded-xl px-3 py-2.5" style={{ background: palette.bg }}>
      <p className="text-stone-500 text-[10px] font-medium uppercase tracking-wider truncate">
        {label}
      </p>
      <div className="flex items-baseline gap-1 mt-0.5">
        <p className="font-bold text-base tabular-nums" style={{ color: palette.color }}>
          {value}%
        </p>
        {delta != null && (
          <p className="text-[10px] font-bold tabular-nums" style={{ color: deltaColor }}>
            {delta > 0 ? '+' : ''}{delta}
          </p>
        )}
      </div>
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

  const deltas = useMemo(() => {
    if (!profile) return null;
    return computeMetricDeltas(dailyLogs, profile);
  }, [dailyLogs, profile]);

  if (!profile || !brainState || !deltas) return null;

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
      <div className="flex items-start gap-2 mb-3">
        <div className="flex-1 min-w-0">
          <h2 className="text-stone-800 font-bold text-base leading-tight">
            {t.brainTodayTitle}
          </h2>
          <p className="text-stone-400 text-xs mt-0.5 leading-snug">
            {t.brainTodaySubtitle}
          </p>
        </div>
        <button
          onClick={() => setInfoOpen(true)}
          className="w-5 h-5 mt-0.5 rounded-full flex items-center justify-center text-stone-300 active:text-stone-500 transition-colors flex-shrink-0"
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
        <MetricChip label={t.brainMetricFocus} value={brainState.metrics.focus} delta={deltas.focus} />
        <MetricChip label={t.brainMetricImpulse} value={brainState.metrics.impulse} delta={deltas.impulse} />
        <MetricChip label={t.brainMetricRecovery} value={brainState.metrics.recovery} delta={deltas.recovery} />
      </div>

      {infoOpen && <BrainMetricsInfoModal onClose={() => setInfoOpen(false)} />}
    </div>
  );
}
