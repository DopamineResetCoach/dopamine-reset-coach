'use client';

import { useMemo, useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { useT } from '@/hooks/useT';
import {
  STAGES,
  countCurrentWeekQualifyingDays,
  getStageMeta,
  getStageProgress,
  type StageId,
} from '@/lib/stages';
import {
  TOTAL_INSIGHTS,
  getUnlockedInsightCount,
} from '@/lib/dopamineInsights';
import type { Translations } from '@/lib/i18n/types';
import BottomSheet from '@/components/ui/BottomSheet';
import InsightsSheet from './InsightsSheet';

function applyParams(template: string, params: Record<string, string | number>): string {
  let out = template;
  for (const [key, value] of Object.entries(params)) {
    out = out.replaceAll(`{${key}}`, String(value));
  }
  return out;
}

function stageName(t: Translations, id: StageId): string {
  return t[getStageMeta(id).nameKey];
}

function stageDesc(t: Translations, id: StageId): string {
  return t[getStageMeta(id).descKey];
}

function stageScience(t: Translations, id: StageId): string {
  return t[getStageMeta(id).scienceKey];
}

// Per-stage week markers — used as labels below each roadmap dot to make it
// unmistakable these are weeks, not months.
const WEEK_MARKER_KEYS: Record<StageId, keyof Translations> = {
  1: 'stageWeekMarker0',
  2: 'stageWeekMarker2',
  3: 'stageWeekMarker4',
  4: 'stageWeekMarker8',
  5: 'stageWeekMarker12',
};

export default function StageCard() {
  const t = useT();
  const { dailyLogs, profile } = useAppStore();
  const [infoOpen, setInfoOpen] = useState(false);
  const [insightsOpen, setInsightsOpen] = useState(false);

  const progress = useMemo(() => {
    if (!profile) return null;
    return getStageProgress(dailyLogs, profile.startDate);
  }, [dailyLogs, profile]);

  const thisWeekDays = useMemo(
    () => countCurrentWeekQualifyingDays(dailyLogs),
    [dailyLogs],
  );

  if (!profile || !progress) return null;

  const meta = getStageMeta(progress.current);
  const isMax = progress.next == null;
  const unlockedInsights = getUnlockedInsightCount(progress.current);

  // Single source of truth for the "next stage" line. Made explicit so the
  // user can read it without parsing: "1/2 qualifying weeks to <Stage>".
  const qualifyingProgressLabel = isMax
    ? t.stageReachedMax
    : applyParams(t.stageQualifyingProgress, {
        current: progress.qualifyingWeeks,
        target: progress.nextThreshold ?? 0,
        stage: stageName(t, progress.next as StageId),
      });

  return (
    <>
      <div className="bg-white rounded-2xl p-4 mb-4">
        <div className="flex items-start justify-between gap-2 mb-3">
          <div className="flex items-center gap-1.5">
            <h3 className="text-stone-700 font-bold text-sm">{t.stageCardTitle}</h3>
            <button
              onClick={() => setInfoOpen(true)}
              className="w-4 h-4 rounded-full flex items-center justify-center text-stone-300 active:text-stone-500 transition-colors"
              aria-label={t.stageInfoTitle}
            >
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                <circle cx="8" cy="8" r="7" stroke="currentColor" strokeWidth="1.4" />
                <path
                  d="M8 11.5v-3.8M8 5.5v.4"
                  stroke="currentColor"
                  strokeWidth="1.5"
                  strokeLinecap="round"
                />
              </svg>
            </button>
          </div>
        </div>

        {/* Stage header */}
        <div className="flex items-center gap-3 mb-3">
          <div
            className="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl"
            style={{ background: 'rgba(91,138,94,0.10)' }}
          >
            {meta.emoji}
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-stone-800 font-bold text-base leading-tight">
              {stageName(t, progress.current)}
            </p>
            <p className="text-stone-500 text-xs leading-snug mt-0.5">
              {stageDesc(t, progress.current)}
            </p>
          </div>
        </div>

        {/* Progress bar */}
        <div className="mb-2">
          <div className="h-2 rounded-full bg-stone-100 overflow-hidden">
            <div
              className="h-full rounded-full transition-all duration-500"
              style={{
                width: `${Math.round(progress.bandProgress * 100)}%`,
                background: 'linear-gradient(90deg, #5B8A5E, #6FAB72)',
              }}
            />
          </div>
        </div>

        <p className="text-stone-600 text-[11px] mt-1.5 tabular-nums font-medium">
          {qualifyingProgressLabel}
        </p>

        {/* This-week pacer — 5 dots cap. The week "counts" once 5 of 7 days
            are active, so showing 5 dots (not 7) keeps the visual target
            honest and matches countQualifyingWeeks' ≥5 rule. */}
        <div className="mt-3 rounded-xl p-2.5" style={{ background: 'rgba(91,138,94,0.06)' }}>
          <div className="flex items-center gap-1.5 mb-1.5" aria-hidden>
            {[0, 1, 2, 3, 4].map((i) => {
              const filled = i < Math.min(5, thisWeekDays);
              return (
                <span
                  key={i}
                  className="w-2.5 h-2.5 rounded-full transition-colors"
                  style={{
                    background: filled ? '#5B8A5E' : 'rgba(91,138,94,0.18)',
                  }}
                />
              );
            })}
          </div>
          <p className="text-[#3D6640] text-[10px] font-medium leading-snug">
            {(() => {
              const remaining = Math.max(0, 5 - thisWeekDays);
              if (remaining === 0) return t.stageWeekDotsComplete;
              if (remaining === 1) return t.stageWeekDotsRemainingOne;
              return applyParams(t.stageWeekDotsRemaining, { n: remaining });
            })()}
          </p>
        </div>

        {/* Stage roadmap — growth-arc icons (heal → sprout → grow → tree →
            peak) with a 1..5 order badge and week-marker label under each
            dot. Order badges remove any residual "lunar cycle" ambiguity. */}
        <div className="flex items-start justify-between gap-1 mt-3">
          {STAGES.map((s) => {
            const reached = progress.current >= s.id;
            const isCurrent = progress.current === s.id;
            const weekLabel = t[WEEK_MARKER_KEYS[s.id]];
            return (
              <div
                key={s.id}
                className={`flex-1 flex flex-col items-center ${
                  reached ? '' : 'opacity-50'
                }`}
              >
                <div
                  className={`w-7 h-7 rounded-full flex items-center justify-center text-sm ${
                    isCurrent ? 'ring-2 ring-[#5B8A5E] ring-offset-1' : ''
                  }`}
                  style={{
                    background: reached ? 'rgba(91,138,94,0.15)' : '#F5F5F4',
                  }}
                >
                  {s.emoji}
                </div>
                <span
                  className={`text-[9px] mt-0.5 tabular-nums font-bold ${
                    isCurrent
                      ? 'text-[#3D6640]'
                      : reached
                      ? 'text-stone-500'
                      : 'text-stone-400'
                  }`}
                >
                  {s.id}
                </span>
                <span
                  className={`text-[9px] tabular-nums ${
                    isCurrent
                      ? 'text-[#3D6640] font-bold'
                      : reached
                      ? 'text-stone-500 font-medium'
                      : 'text-stone-400'
                  }`}
                >
                  {weekLabel}
                </span>
                {isCurrent && (
                  <span className="text-[9px] text-[#3D6640] font-bold leading-tight text-center mt-0.5 max-w-[60px]">
                    {stageName(t, s.id)}
                  </span>
                )}
              </div>
            );
          })}
        </div>

        {/* Insights unlock pill */}
        <button
          onClick={() => setInsightsOpen(true)}
          className="w-full mt-3 rounded-xl px-3 py-2.5 flex items-center gap-2 active:scale-[0.99] transition-transform"
          style={{ background: 'rgba(91,138,94,0.08)' }}
          aria-label={t.insightsSheetTitle}
        >
          <span className="text-base">💡</span>
          <span className="text-[#3D6640] text-[11px] font-bold flex-1 text-left">
            {applyParams(t.insightsCardLabel, {
              unlocked: unlockedInsights,
              total: TOTAL_INSIGHTS,
            })}
          </span>
          <span className="text-[#3D6640] text-[11px] font-bold">›</span>
        </button>
      </div>

      {infoOpen && (
        <BottomSheet onClose={() => setInfoOpen(false)}>
          <div className="mb-4">
            <h2 className="text-stone-800 font-bold text-lg mb-2">{t.stageInfoTitle}</h2>
            <p className="text-stone-500 text-sm leading-relaxed">{t.stageInfoBody}</p>
          </div>

          <div className="space-y-2 mb-5">
            {STAGES.map((s) => (
              <div key={s.id} className="bg-stone-50 rounded-2xl p-3">
                <div className="flex items-center gap-2 mb-1">
                  <span className="text-lg">{s.emoji}</span>
                  <p className="font-bold text-sm text-stone-800">
                    {stageName(t, s.id)}
                  </p>
                  <span className="ml-auto text-stone-400 text-[10px] tabular-nums">
                    {t[WEEK_MARKER_KEYS[s.id]]}+
                  </span>
                </div>
                <p className="text-stone-600 text-xs leading-relaxed">
                  {stageScience(t, s.id)}
                </p>
              </div>
            ))}
          </div>

          <button
            onClick={() => setInfoOpen(false)}
            className="w-full py-3 rounded-2xl text-white font-bold text-sm active:scale-[0.98] transition-transform"
            style={{ background: 'linear-gradient(135deg, #5B8A5E, #3D6640)' }}
          >
            {t.ariaClose}
          </button>
        </BottomSheet>
      )}

      {insightsOpen && (
        <InsightsSheet
          currentStage={progress.current}
          onClose={() => setInsightsOpen(false)}
        />
      )}
    </>
  );
}
