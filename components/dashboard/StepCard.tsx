'use client';

import { useEffect, useRef, useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { calcStrideScore } from '@/lib/steps';
import { useT } from '@/hooks/useT';
import PremiumModal from '@/components/premium/PremiumModal';

function getDailyFact(t: ReturnType<typeof useT>): string {
  const facts = [
    t.stepFact1, t.stepFact2, t.stepFact3, t.stepFact4, t.stepFact5,
    t.stepFact6, t.stepFact7, t.stepFact8, t.stepFact9, t.stepFact10,
    t.stepFact11, t.stepFact12, t.stepFact13, t.stepFact14, t.stepFact15,
    t.stepFact16, t.stepFact17, t.stepFact18, t.stepFact19, t.stepFact20,
    t.stepFact21, t.stepFact22, t.stepFact23, t.stepFact24, t.stepFact25,
    t.stepFact26, t.stepFact27, t.stepFact28, t.stepFact29, t.stepFact30,
  ];
  const now = new Date();
  const yearStart = new Date(now.getFullYear(), 0, 0);
  const dayOfYear = Math.floor((now.getTime() - yearStart.getTime()) / 86400000);
  return facts[dayOfYear % facts.length];
}

function getStrideState(steps: number, t: ReturnType<typeof useT>): string {
  if (steps >= 15000) return t.strideStateElite;
  if (steps >= 12000) return t.strideStateRecovery;
  if (steps >= 8000) return t.strideStateDopamine;
  if (steps >= 5000) return t.strideStateFocus;
  if (steps >= 2000) return t.strideStateWakeup;
  if (steps >= 1) return t.strideStateWarmingUp;
  return t.strideStateReady;
}

function getThresholdFact(steps: number, t: ReturnType<typeof useT>): string | null {
  if (steps >= 15000) return t.stepFact13;
  if (steps >= 12000) return t.stepFact9;
  if (steps >= 8000) return t.stepFact2;
  if (steps >= 5000) return t.stepFact11;
  if (steps >= 2000) return t.stepFact1;
  return null;
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

export default function StepCard() {
  const todaySteps = useAppStore((s) => s.todaySteps);
  const stepGoal = useAppStore((s) => s.stepGoal);
  const refreshSteps = useAppStore((s) => s.refreshSteps);
  const language = useAppStore((s) => s.language);
  const isPremium = useAppStore((s) => s.isPremium);
  const initialized = useRef(false);
  const [showPremium, setShowPremium] = useState(false);
  const t = useT();

  useEffect(() => {
    if (initialized.current) return;
    if (!isPremium) return;
    initialized.current = true;
    refreshSteps();
  }, [refreshSteps, isPremium]);

  if (!isPremium) {
    return (
      <>
        <div
          className="bg-white rounded-2xl p-5 mb-4 shadow-sm border border-stone-100 cursor-pointer"
          onClick={() => setShowPremium(true)}
        >
          <div className="flex items-start gap-3 mb-3">
            <span className="text-3xl">🚶</span>
            <div className="flex-1">
              <div className="flex items-center gap-2 mb-1">
                <p className="text-stone-800 font-bold text-sm">{t.stepCounterTitle}</p>
                <span
                  className="text-[10px] font-bold px-2 py-0.5 rounded-full text-white"
                  style={{ background: '#5B8A5E' }}
                >
                  PRO
                </span>
              </div>
              <p className="text-stone-500 text-xs leading-snug">{t.stepCounterScience}</p>
            </div>
          </div>
          <button
            className="w-full py-2.5 rounded-xl text-white font-bold text-xs"
            style={{ background: 'linear-gradient(135deg, #5B8A5E, #3D6640)' }}
          >
            {t.settingsUnlockPro}
          </button>
        </div>
        {showPremium && <PremiumModal onClose={() => setShowPremium(false)} />}
      </>
    );
  }

  const score = calcStrideScore(todaySteps, stepGoal);
  const progress = Math.min(1, todaySteps / stepGoal);
  const message = getStrideMessage(todaySteps, stepGoal, t);
  const state = getStrideState(todaySteps, t);
  const fact = getThresholdFact(todaySteps, t) ?? getDailyFact(t);
  const locale = language === 'zh' ? 'zh-CN' : language === 'ja' ? 'ja-JP' : language === 'ar' ? 'ar' : language;

  // Score kleur
  const scoreColor =
    score >= 75 ? '#4CAF50' : score >= 50 ? '#8BC34A' : score >= 25 ? '#FFC107' : '#FF7043';

  // SVG cirkel
  const r = 40;
  const circ = 2 * Math.PI * r;
  const dashOffset = circ * (1 - progress);

  return (
    <div className="bg-white rounded-2xl p-4 mb-4 shadow-sm border border-stone-100">
      {/* Apple Health attribution — required by Guideline 2.5.1.
          Visible at top of the card so it's clear this feature uses HealthKit. */}
      <div className="flex items-center gap-1.5 mb-3 pb-2.5 border-b border-stone-100">
        <svg width="14" height="14" viewBox="0 0 12 12" fill="none" aria-hidden="true">
          <path
            d="M6 10.5S1 7.5 1 4.5A2.5 2.5 0 0 1 6 3.2 2.5 2.5 0 0 1 11 4.5C11 7.5 6 10.5 6 10.5Z"
            fill="#FF2D55"
          />
        </svg>
        <p className="text-[11px] font-semibold text-stone-600">
          {t.stepCardAttribution}
        </p>
      </div>

      <div className="flex items-start justify-between mb-3 gap-3">
        <div className="flex-1 min-w-0">
          <p className="text-[10px] font-semibold uppercase tracking-widest text-stone-400">
            {t.strideScoreLabel}
          </p>
          <p
            className="text-stone-800 font-bold text-base mt-0.5 transition-colors"
            style={{ color: scoreColor }}
          >
            {state}
          </p>
          <p className="text-stone-500 text-xs mt-1 leading-snug">{message}</p>
        </div>
        {/* Score badge */}
        <div
          className="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white text-sm flex-shrink-0"
          style={{ background: scoreColor }}
        >
          {score}
        </div>
      </div>

      <div className="flex items-center gap-4">
        {/* Cirkel progress */}
        <div className="relative flex-shrink-0">
          <svg width="100" height="100" viewBox="0 0 100 100">
            {/* Achtergrond cirkel */}
            <circle
              cx="50" cy="50" r={r}
              fill="none"
              stroke="#f5f5f4"
              strokeWidth="10"
            />
            {/* Progress cirkel */}
            <circle
              cx="50" cy="50" r={r}
              fill="none"
              stroke={scoreColor}
              strokeWidth="10"
              strokeLinecap="round"
              strokeDasharray={circ}
              strokeDashoffset={dashOffset}
              transform="rotate(-90 50 50)"
              style={{ transition: 'stroke-dashoffset 0.6s ease' }}
            />
            {/* Stap icon */}
            <text x="50" y="46" textAnchor="middle" fontSize="18">🚶</text>
            <text x="50" y="62" textAnchor="middle" fontSize="10" fill="#78716c">
              {Math.round(progress * 100)}%
            </text>
          </svg>
        </div>

        {/* Stappen info */}
        <div className="flex-1">
          <p className="text-3xl font-bold text-stone-800">
            {todaySteps.toLocaleString(locale)}
          </p>
          <p className="text-stone-400 text-sm">
            {t.strideOfSteps.replace('{goal}', stepGoal.toLocaleString(locale))}
          </p>

          {/* Mini progress bar */}
          <div className="mt-2 h-1.5 bg-stone-100 rounded-full overflow-hidden">
            <div
              className="h-full rounded-full transition-all duration-700"
              style={{ width: `${progress * 100}%`, background: scoreColor }}
            />
          </div>

          {/* Extra steps at goal */}
          {progress >= 1 && (
            <p className="text-xs font-semibold mt-2" style={{ color: scoreColor }}>
              {t.strideExtraSteps.replace('{n}', (todaySteps - stepGoal).toLocaleString(locale))}
            </p>
          )}
        </div>
      </div>

      {/* Daily walking fact — rotates each day */}
      <div className="mt-3 pt-3 border-t border-stone-100">
        <p className="text-xs text-stone-500 leading-relaxed italic">
          💡 {fact}
        </p>
      </div>

      <div className="mt-3 flex items-center justify-between">
        <button
          onClick={refreshSteps}
          className="text-xs text-stone-400 underline"
        >
          {t.strideRefresh}
        </button>
      </div>
    </div>
  );
}
