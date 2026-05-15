'use client';

import { useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { getTodayString, calculateDailyDebt, getTodayScore } from '@/lib/scoring';
import { getTasksForProfile } from '@/lib/tasks';
import PremiumModal from '@/components/premium/PremiumModal';
import { useT } from '@/hooks/useT';
import type { Translations } from '@/lib/i18n';

function getInsight(
  t: Translations,
  score: number,
  completedCount: number,
  totalTasks: number,
  debt: number,
  urgesLogged: number,
  urgesResisted: number
): { title: string; body: string; accent: string } {
  if (debt >= 30) {
    return {
      title: t.insightHighDebtTitle,
      body: t.insightHighDebtBody.replace('{debt}', String(debt)),
      accent: '#D97070',
    };
  }
  if (score < 35 && completedCount < 3) {
    return {
      title: t.insightLowScoreTitle,
      body: t.insightLowScoreBody,
      accent: '#E4A85A',
    };
  }
  if (urgesResisted > 0 && urgesResisted === urgesLogged) {
    const s = urgesResisted > 1 ? 's' : '';
    return {
      title: t.insightUrgeTitle,
      body: t.insightUrgeBody.replace('{n}', String(urgesResisted)).replace('{s}', s),
      accent: '#5B8A5E',
    };
  }
  if (completedCount >= Math.ceil(totalTasks * 0.5)) {
    return {
      title: t.insightProgressTitle,
      body: t.insightProgressBody.replace('{n}', String(completedCount)),
      accent: '#5B8A5E',
    };
  }
  if (completedCount > 0) {
    return {
      title: t.insightStartedTitle,
      body: t.insightStartedBody,
      accent: '#8DAF8F',
    };
  }
  return {
    title: t.insightWaitingTitle,
    body: t.insightWaitingBody,
    accent: '#E4A85A',
  };
}

export default function WhyInsight() {
  const { dailyLogs, isPremium, profile } = useAppStore();
  const t = useT();
  const [showPremium, setShowPremium] = useState(false);
  const today = getTodayString();
  const log = dailyLogs[today];
  const score = getTodayScore(dailyLogs, profile);
  const completedCount = log?.completedTasks.length ?? 0;
  const debt = calculateDailyDebt(log?.badHabits ?? []);
  const urgesLogged = log?.urges.length ?? 0;
  const urgesResisted = log?.urges.filter((u) => u.completedIntervention).length ?? 0;
  const totalTasks = getTasksForProfile(profile?.hardMode ?? false).length;

  const insight = getInsight(t, score, completedCount, totalTasks, debt, urgesLogged, urgesResisted);

  if (!isPremium) {
    return (
      <>
        <div
          className="relative bg-white rounded-3xl p-5 mb-4 shadow-sm overflow-hidden cursor-pointer"
          onClick={() => setShowPremium(true)}
        >
          <div className="blur-sm pointer-events-none select-none">
            <p className="text-stone-400 text-xs font-medium uppercase tracking-widest mb-1">
              {t.insightLabel}
            </p>
            <p className="text-stone-800 font-bold text-base">
              {t.insightLowScoreTitle}
            </p>
            <p className="text-stone-400 text-sm mt-1 leading-snug">
              {t.insightLowScoreBody}
            </p>
          </div>
          <div className="absolute inset-0 flex flex-col items-center justify-center bg-white/70 rounded-3xl">
            <span className="text-3xl mb-2">🔒</span>
            <p className="text-stone-700 font-bold text-sm">{t.premiumFeature2.split(' — ')[0]}</p>
            <p className="text-stone-400 text-xs mt-1 text-center px-6">
              {t.settingsPremiumFreeDesc}
            </p>
            <div
              className="mt-3 px-5 py-2 rounded-xl text-white text-xs font-bold"
              style={{ background: '#5B8A5E' }}
            >
              {t.premiumLabel}
            </div>
          </div>
        </div>
        {showPremium && <PremiumModal onClose={() => setShowPremium(false)} />}
      </>
    );
  }

  return (
    <div
      className="rounded-3xl p-5 mb-4"
      style={{ background: `${insight.accent}15`, borderLeft: `3px solid ${insight.accent}` }}
    >
      <p className="text-stone-400 text-xs font-medium uppercase tracking-widest mb-1">
        {t.insightLabel}
      </p>
      <p className="font-bold text-stone-800 text-base mb-1">{insight.title}</p>
      <p className="text-stone-600 text-sm leading-relaxed">{insight.body}</p>
    </div>
  );
}
