'use client';

import { useEffect, useMemo, useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { getTodayString, getTodayScore } from '@/lib/scoring';
import { getStageProgress, type StageId } from '@/lib/stages';
import DailyQuote from './DailyQuote';
import DopamineScoreCard from './DopamineScoreCard';
import DopamineDebtCard from './DopamineDebtCard';
import WhyInsight from './WhyInsight';
import DailyChecklist from './DailyChecklist';
import UrgeTracker from '@/components/urge/UrgeTracker';
import ProgressView from '@/components/progress/ProgressView';
import StageTransitionModal from '@/components/progress/StageTransitionModal';
import FocusTimer from '@/components/focus/FocusTimer';
import SettingsView from '@/components/settings/SettingsView';
import BottomNav from '@/components/ui/BottomNav';
import StepStrip from '@/components/ui/StepStrip';
import ProBanner from '@/components/premium/ProBanner';
import CheckInCard from '@/components/checkin/CheckInCard';
import BrainTodayCard from './BrainTodayCard';
import { useT } from '@/hooks/useT';

function getBgColor(score: number): string {
  if (score < 35) return '#E8E6E3';
  if (score < 50) return '#EDEAE6';
  if (score < 65) return '#F0EDE8';
  return '#F5F0EB';
}

function TodayView() {
  const { dailyLogs, language, profile } = useAppStore();
  const t = useT();
  const score = getTodayScore(dailyLogs, profile);
  const bg = getBgColor(score);

  const greeting = score < 35
    ? t.greetLow
    : score < 50
    ? t.greetMedium
    : score < 65
    ? t.greetGood
    : t.greetGreat;

  return (
    <div
      className="min-h-screen pb-52 overflow-y-auto"
      style={{ background: bg, animation: 'fade-in 0.3s ease-out', transition: 'background 1s ease' }}
    >
      <div className="max-w-sm mx-auto px-4 pt-safe-top pb-4">
        {/* Header */}
        <div className="flex items-center justify-between mb-5">
          <div>
            <p className="text-stone-400 text-xs font-medium uppercase tracking-widest">
              {new Date().toLocaleDateString(language || 'en', {
                weekday: 'long',
                month: 'long',
                day: 'numeric',
              })}
            </p>
            <h1 className="text-2xl font-bold text-stone-800 mt-0.5">
              {greeting}
            </h1>
          </div>
        </div>

        <ProBanner />
        <BrainTodayCard />
        <CheckInCard />
        <WhyInsight />
        <DopamineScoreCard />
        <DopamineDebtCard />
        <DailyQuote />
        <DailyChecklist />
      </div>

      <UrgeTracker />
    </div>
  );
}

export default function Dashboard() {
  const {
    activeTab,
    ensureTodayLog,
    resetChallengesIfNewWeek,
    dailyLogs,
    profile,
    lastSeenStage,
    markStageSeen,
  } = useAppStore();
  const [pendingStage, setPendingStage] = useState<StageId | null>(null);

  useEffect(() => {
    ensureTodayLog();
    resetChallengesIfNewWeek();
    let lastDate = getTodayString();
    const check = () => {
      const now = getTodayString();
      if (now !== lastDate) {
        lastDate = now;
        ensureTodayLog();
        resetChallengesIfNewWeek();
      } else {
        // Also check weekly reset on visibility change in case the date check
        // hasn't fired but we crossed a week boundary while backgrounded
        resetChallengesIfNewWeek();
      }
    };
    const id = window.setInterval(check, 60_000);
    document.addEventListener('visibilitychange', check);
    return () => {
      window.clearInterval(id);
      document.removeEventListener('visibilitychange', check);
    };
  }, [ensureTodayLog, resetChallengesIfNewWeek]);

  // Detect new stage transitions. Shows the celebration modal once per stage.
  // Skips for fresh installs (lastSeenStage default 1, current stage 1) — only
  // triggers when the user actually advances.
  const currentStage = useMemo<StageId | null>(() => {
    if (!profile) return null;
    return getStageProgress(dailyLogs, profile.startDate).current;
  }, [dailyLogs, profile]);

  useEffect(() => {
    if (currentStage == null) return;
    if (currentStage > lastSeenStage && pendingStage == null) {
      setPendingStage(currentStage);
    }
  }, [currentStage, lastSeenStage, pendingStage]);

  return (
    <div className="relative">
      {activeTab === 'today' && <TodayView />}
      {activeTab === 'progress' && <ProgressView />}
      {activeTab === 'focus' && <FocusTimer />}
      {activeTab === 'settings' && <SettingsView />}
      <StepStrip />
      <BottomNav />
      {pendingStage != null && (
        <StageTransitionModal
          stageId={pendingStage}
          onClose={() => {
            markStageSeen(pendingStage);
            setPendingStage(null);
          }}
        />
      )}
    </div>
  );
}
