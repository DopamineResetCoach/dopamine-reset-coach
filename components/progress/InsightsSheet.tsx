'use client';

import { useMemo } from 'react';
import BottomSheet from '@/components/ui/BottomSheet';
import { useT } from '@/hooks/useT';
import { getStageMeta, type StageId } from '@/lib/stages';
import {
  INSIGHTS,
  TOTAL_INSIGHTS,
  getInsightsByStage,
  getUnlockedInsightCount,
} from '@/lib/dopamineInsights';
import type { Translations } from '@/lib/i18n/types';

function applyParams(template: string, params: Record<string, string | number>): string {
  let out = template;
  for (const [k, v] of Object.entries(params)) {
    out = out.replaceAll(`{${k}}`, String(v));
  }
  return out;
}

interface Props {
  currentStage: StageId;
  onClose: () => void;
}

export default function InsightsSheet({ currentStage, onClose }: Props) {
  const t = useT();
  const unlocked = useMemo(() => getUnlockedInsightCount(currentStage), [currentStage]);
  const byStage = useMemo(() => getInsightsByStage(), []);
  const progressPct = Math.round((unlocked / TOTAL_INSIGHTS) * 100);

  void INSIGHTS; // ensure module-level catalog loads

  return (
    <BottomSheet onClose={onClose}>
      <div className="mb-4">
        <h2 className="text-stone-800 font-bold text-lg mb-1">{t.insightsSheetTitle}</h2>
        <p className="text-stone-500 text-xs leading-relaxed mb-3">
          {t.insightsSheetSubtitle}
        </p>

        {/* Progress */}
        <div className="flex items-center gap-2 mb-1">
          <div className="flex-1 h-2 rounded-full bg-stone-100 overflow-hidden">
            <div
              className="h-full rounded-full transition-all duration-500"
              style={{
                width: `${progressPct}%`,
                background: 'linear-gradient(90deg, #5B8A5E, #6FAB72)',
              }}
            />
          </div>
          <span className="text-stone-500 text-[10px] tabular-nums font-medium">
            {applyParams(t.insightsProgressLabel, {
              unlocked,
              total: TOTAL_INSIGHTS,
            })}
          </span>
        </div>
      </div>

      <div className="space-y-5 mb-5">
        {([1, 2, 3, 4, 5] as StageId[]).map((stageId) => {
          const stageMeta = getStageMeta(stageId);
          const stageName = t[stageMeta.nameKey];
          const items = byStage[stageId];
          const isStageUnlocked = currentStage >= stageId;

          return (
            <section key={stageId}>
              <div className="flex items-center gap-2 mb-2">
                <span className="text-base">{stageMeta.emoji}</span>
                <p className="text-stone-700 font-bold text-xs uppercase tracking-wide">
                  {stageName}
                </p>
                <span className="text-stone-400 text-[10px] tabular-nums ml-auto">
                  {isStageUnlocked
                    ? t.insightsSectionUnlocked
                    : t.insightsSectionLocked}
                </span>
              </div>

              <div className="space-y-2">
                {items.map((ins) => {
                  const title = t[ins.titleKey as keyof Translations];
                  const body = t[ins.bodyKey as keyof Translations];
                  if (isStageUnlocked) {
                    return (
                      <div
                        key={ins.index}
                        className="bg-stone-50 rounded-2xl p-3"
                      >
                        <p className="text-stone-800 font-bold text-sm leading-tight mb-1">
                          {title}
                        </p>
                        <p className="text-stone-600 text-xs leading-relaxed">
                          {body}
                        </p>
                      </div>
                    );
                  }
                  return (
                    <div
                      key={ins.index}
                      className="bg-stone-50/60 rounded-2xl p-3 flex items-start gap-2"
                    >
                      <span className="text-stone-300 text-sm leading-none mt-0.5">
                        🔒
                      </span>
                      <div className="flex-1 min-w-0">
                        <p className="text-stone-400 font-bold text-sm leading-tight">
                          {title}
                        </p>
                        <p className="text-stone-400 text-[11px] leading-relaxed mt-0.5">
                          {applyParams(t.insightsLockedHint, { stage: stageName })}
                        </p>
                      </div>
                    </div>
                  );
                })}
              </div>
            </section>
          );
        })}
      </div>

      <button
        onClick={onClose}
        className="w-full py-3 rounded-2xl text-white font-bold text-sm active:scale-[0.98] transition-transform"
        style={{ background: 'linear-gradient(135deg, #5B8A5E, #3D6640)' }}
      >
        {t.ariaClose}
      </button>
    </BottomSheet>
  );
}
