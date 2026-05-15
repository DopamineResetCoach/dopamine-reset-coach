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
import type { Translations } from '@/lib/i18n/types';
import BottomSheet from '@/components/ui/BottomSheet';

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

export default function StageCard() {
  const t = useT();
  const { dailyLogs, profile } = useAppStore();
  const [infoOpen, setInfoOpen] = useState(false);

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

  const weeksLabel =
    progress.qualifyingWeeks === 1
      ? t.stageWeeksLabelSingular
      : applyParams(t.stageWeeksLabel, { n: progress.qualifyingWeeks });

  const nextLabel = isMax
    ? t.stageReachedMax
    : applyParams(t.stageNextLabel, {
        n:
          progress.weeksToNext === 1
            ? t.stageWeeksLabelSingular
            : applyParams(t.stageWeeksLabel, { n: progress.weeksToNext }),
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
          <span className="text-stone-400 text-[10px] tabular-nums">{weeksLabel}</span>
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
          <div className="flex justify-between mt-1.5">
            <span className="text-stone-400 text-[10px]">
              {stageName(t, progress.current)}
            </span>
            <span className="text-stone-400 text-[10px]">
              {isMax ? '' : stageName(t, progress.next as StageId)}
            </span>
          </div>
        </div>

        <p className="text-stone-500 text-[10px] mt-1 tabular-nums">{nextLabel}</p>

        {/* This-week pacer */}
        <div className="mt-3 rounded-xl p-2.5" style={{ background: 'rgba(91,138,94,0.06)' }}>
          <p className="text-[#3D6640] text-[10px] font-medium leading-snug">
            {applyParams(t.stageThisWeekDays, { n: thisWeekDays })}
          </p>
        </div>

        {/* Stage roadmap */}
        <div className="flex items-center justify-between gap-1 mt-3">
          {STAGES.map((s) => {
            const reached = progress.current >= s.id;
            const isCurrent = progress.current === s.id;
            return (
              <div
                key={s.id}
                className={`flex-1 flex flex-col items-center ${
                  reached ? '' : 'opacity-35'
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
              </div>
            );
          })}
        </div>
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
    </>
  );
}
