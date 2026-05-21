'use client';

import { useEffect, useRef, useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { calcStrideScore } from '@/lib/steps';
import { useT } from '@/hooks/useT';
import PremiumModal from '@/components/premium/PremiumModal';
import StepDetailModal from '@/components/dashboard/StepDetailModal';

function getStrideStateName(steps: number, t: ReturnType<typeof useT>): string {
  if (steps >= 15000) return t.strideStateElite;
  if (steps >= 12000) return t.strideStateRecovery;
  if (steps >= 8000) return t.strideStateDopamine;
  if (steps >= 5000) return t.strideStateFocus;
  if (steps >= 2000) return t.strideStateWakeup;
  if (steps >= 1) return t.strideStateWarmingUp;
  return t.strideStateReady;
}

function getStrideMessage(steps: number, goal: number, t: ReturnType<typeof useT>): string {
  const ratio = steps / goal;
  if (steps === 0) return t.stride0;
  if (ratio < 0.25) return t.strideStarted;
  if (ratio < 0.5) return t.strideLow;
  if (ratio < 0.75) return t.strideHalf;
  if (ratio < 1.0) return t.strideClose.replace('{n}', (goal - steps).toLocaleString());
  if (ratio < 1.5) return t.strideGoal;
  return t.strideExceptional;
}

export default function StepStrip() {
  const todaySteps = useAppStore((s) => s.todaySteps);
  const stepGoal = useAppStore((s) => s.stepGoal);
  const refreshSteps = useAppStore((s) => s.refreshSteps);
  const isPremium = useAppStore((s) => s.isPremium);
  const language = useAppStore((s) => s.language);
  const t = useT();

  const [showDetail, setShowDetail] = useState(false);
  const [showPremium, setShowPremium] = useState(false);
  const initialized = useRef(false);

  useEffect(() => {
    if (initialized.current) return;
    if (!isPremium) return;
    initialized.current = true;
    refreshSteps();
  }, [refreshSteps, isPremium]);

  const locale = language === 'zh' ? 'zh-CN' : language === 'ja' ? 'ja-JP' : language === 'ar' ? 'ar' : language;

  if (!isPremium) {
    return (
      <>
        <button
          onClick={() => setShowPremium(true)}
          className="fixed bottom-[calc(env(safe-area-inset-bottom)+62px)] left-0 right-0 z-30 bg-white border-t border-stone-100 flex justify-center active:bg-stone-50 transition-colors"
        >
          <div className="w-full max-w-sm flex items-center gap-3 px-4 py-2.5">
            <span className="text-xl">🚶</span>
            <div className="flex-1 text-left min-w-0">
              <div className="flex items-center gap-1.5">
                <p className="text-stone-800 font-bold text-sm truncate">{t.stepCounterTitle}</p>
                <span
                  className="text-[9px] font-bold px-1.5 py-0.5 rounded-full text-white flex-shrink-0"
                  style={{ background: '#5B8A5E' }}
                >
                  PRO
                </span>
              </div>
              <div className="flex items-center gap-1 mt-0.5">
                <svg width="10" height="10" viewBox="0 0 12 12" fill="none" aria-hidden="true" className="flex-shrink-0">
                  <path
                    d="M6 10.5S1 7.5 1 4.5A2.5 2.5 0 0 1 6 3.2 2.5 2.5 0 0 1 11 4.5C11 7.5 6 10.5 6 10.5Z"
                    fill="#FF2D55"
                  />
                </svg>
                <p className="text-stone-400 text-[11px] leading-snug truncate">{t.appleHealthSubLabel}</p>
              </div>
            </div>
          </div>
        </button>
        {showPremium && <PremiumModal onClose={() => setShowPremium(false)} />}
      </>
    );
  }

  const score = calcStrideScore(todaySteps, stepGoal);
  const progress = Math.min(1, todaySteps / stepGoal);
  const state = getStrideStateName(todaySteps, t);
  const message = getStrideMessage(todaySteps, stepGoal, t);

  const scoreColor =
    score >= 75 ? '#4CAF50' : score >= 50 ? '#8BC34A' : score >= 25 ? '#FFC107' : '#FF7043';

  return (
    <>
      <button
        onClick={() => setShowDetail(true)}
        className="fixed bottom-[calc(env(safe-area-inset-bottom)+62px)] left-0 right-0 z-30 bg-white border-t border-stone-100 flex justify-center active:bg-stone-50 transition-colors"
        aria-label={state}
      >
        <div className="w-full max-w-sm px-4 py-2.5">
          <div className="flex items-center justify-between gap-3 mb-1.5">
            <div className="flex items-center gap-2 min-w-0">
              <span className="text-base">🚶</span>
              <p
                className="font-bold text-sm truncate"
                style={{ color: scoreColor }}
              >
                {state}
              </p>
              <span className="flex items-center gap-0.5 flex-shrink-0">
                <svg width="10" height="10" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                  <path
                    d="M6 10.5S1 7.5 1 4.5A2.5 2.5 0 0 1 6 3.2 2.5 2.5 0 0 1 11 4.5C11 7.5 6 10.5 6 10.5Z"
                    fill="#FF2D55"
                  />
                </svg>
                <span className="text-stone-400 text-[10px] font-medium">{t.appleHealthLabel}</span>
              </span>
            </div>
            <p className="text-stone-700 font-bold text-sm tabular-nums flex-shrink-0">
              {todaySteps.toLocaleString(locale)}
              <span className="text-stone-400 font-medium"> / {stepGoal.toLocaleString(locale)}</span>
            </p>
          </div>
          <p className="text-stone-500 text-[11px] leading-snug truncate text-left mb-1.5">{message}</p>
          <div className="h-1 bg-stone-100 rounded-full overflow-hidden">
            <div
              className="h-full rounded-full transition-all duration-700"
              style={{ width: `${progress * 100}%`, background: scoreColor }}
            />
          </div>
        </div>
      </button>
      {showDetail && <StepDetailModal onClose={() => setShowDetail(false)} />}
    </>
  );
}
