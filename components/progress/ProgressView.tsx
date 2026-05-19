'use client';

import { useMemo, useState } from 'react';
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  ReferenceLine,
} from 'recharts';
import { useAppStore } from '@/store/useAppStore';
import { getScoreHistory, calculateStreak, getTodayString, calculateDailyDebt, toLocalDateString } from '@/lib/scoring';
import { getTranslatedTasks } from '@/lib/tasks';
import { buildWeeklyInsights, TrendStat } from '@/lib/insights';
import PremiumModal from '@/components/premium/PremiumModal';
import StageCard from './StageCard';
import VoiceJournalCard from './VoiceJournalCard';
import DopamineScoreDetailModal from './DopamineScoreDetailModal';
import { useT } from '@/hooks/useT';

function CustomTooltip({ active, payload, label }: any) {
  if (active && payload && payload.length) {
    return (
      <div className="bg-white rounded-xl shadow-lg border border-stone-100 px-3 py-2">
        <p className="text-stone-400 text-xs">{label}</p>
        <p className="text-[#5B8A5E] font-bold text-sm">
          {Math.round(payload[0].value)} pts
        </p>
      </div>
    );
  }
  return null;
}

function StatCard({
  value,
  label,
  color = '#5B8A5E',
  emoji,
}: {
  value: string | number;
  label: string;
  color?: string;
  emoji: string;
}) {
  return (
    <div className="bg-white rounded-2xl p-4 flex flex-col items-center text-center">
      <span className="text-2xl mb-1">{emoji}</span>
      <p className="font-bold text-xl" style={{ color }}>
        {value}
      </p>
      <p className="text-stone-400 text-sm mt-0.5 leading-tight">{label}</p>
    </div>
  );
}

function HabitHeatmap() {
  const t = useT();
  const { dailyLogs, profile, language } = useAppStore();
  if (!profile) return null;
  const tasks = getTranslatedTasks(t, profile.hardMode, profile.habits);

  const days = Array.from({ length: 7 }, (_, i) => {
    const d = new Date();
    d.setDate(d.getDate() - (6 - i));
    return toLocalDateString(d);
  });

  const dayLabels = days.map((d, i) => {
    if (i === 6) return t.progressToday;
    return new Date(d + 'T12:00:00').toLocaleDateString(language || 'en', { weekday: 'short' });
  });

  return (
    <div className="bg-white rounded-2xl p-4 mb-4">
      <h3 className="text-stone-700 font-bold text-sm mb-3">{t.progressHeatmapTitle}</h3>
      <div className="max-h-64 overflow-y-auto -mx-1 px-1">
        <table className="w-full">
          <thead className="sticky top-0 bg-white z-10">
            <tr>
              <td className="w-24" />
              {dayLabels.map((l) => (
                <td key={l} className="text-center text-xs text-stone-400 pb-2 px-0.5">
                  {l}
                </td>
              ))}
            </tr>
          </thead>
          <tbody>
            {tasks.map((task) => (
              <tr key={task.id}>
                <td className="text-xs text-stone-500 pr-2 py-0.5 whitespace-nowrap max-w-[80px] overflow-hidden text-ellipsis">
                  {task.icon} {task.title.split(' ')[0]}
                </td>
                {days.map((day) => {
                  const log = dailyLogs[day];
                  const done = log?.completedTasks.includes(task.id);
                  const hasLog = !!log;
                  return (
                    <td key={day} className="px-0.5 py-0.5">
                      <div
                        className={`w-6 h-6 rounded-md mx-auto ${
                          done
                            ? 'bg-[#5B8A5E]'
                            : hasLog
                            ? 'bg-stone-100'
                            : 'bg-stone-50'
                        }`}
                      />
                    </td>
                  );
                })}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      <div className="flex items-center gap-3 mt-3">
        <div className="flex items-center gap-1.5">
          <div className="w-3 h-3 rounded-sm bg-[#5B8A5E]" />
          <span className="text-xs text-stone-400">{t.progressDone}</span>
        </div>
        <div className="flex items-center gap-1.5">
          <div className="w-3 h-3 rounded-sm bg-stone-100" />
          <span className="text-xs text-stone-400">{t.progressSkipped}</span>
        </div>
      </div>
    </div>
  );
}

function BrainChallenges() {
  const t = useT();
  const { isPremium, profile, completedChallenges, completeChallenge, uncompleteChallenge, challengeResetMode } = useAppStore();
  const [showPremium, setShowPremium] = useState(false);
  const [expanded, setExpanded] = useState<string | null>(null);
  const [infoOpen, setInfoOpen] = useState(false);

  type HabitKey = keyof NonNullable<typeof profile>['habits'];
  const rawChallenges: {
    id: string;
    emoji: string;
    title: string;
    description: string;
    duration: string;
    reflection: string;
    habitTag?: HabitKey;
  }[] = [
    {
      id: 'no-phone-morning',
      emoji: '🌅',
      title: t.challenge1Title,
      description: t.challenge1Desc,
      duration: t.challenge1Duration,
      reflection: t.challenge1Reflection,
      habitTag: 'socialMedia',
    },
    {
      id: 'boredom-session',
      emoji: '🪑',
      title: t.challenge2Title,
      description: t.challenge2Desc,
      duration: t.challenge2Duration,
      reflection: t.challenge2Reflection,
    },
    {
      id: 'cold-shower',
      emoji: '🧊',
      title: t.challenge3Title,
      description: t.challenge3Desc,
      duration: t.challenge3Duration,
      reflection: t.challenge3Reflection,
    },
    {
      id: 'no-junk-24h',
      emoji: '🥗',
      title: t.challenge4Title,
      description: t.challenge4Desc,
      duration: t.challenge4Duration,
      reflection: t.challenge4Reflection,
      habitTag: 'junkFood',
    },
    {
      id: 'walk-no-music',
      emoji: '🚶',
      title: t.challenge5Title,
      description: t.challenge5Desc,
      duration: t.challenge5Duration,
      reflection: t.challenge5Reflection,
    },
  ];

  const habits = profile?.habits;
  const forYou = rawChallenges.filter((c) => c.habitTag && habits?.[c.habitTag]);
  const others = rawChallenges.filter((c) => !forYou.includes(c));

  const renderChallenge = (c: typeof rawChallenges[number], isForYou: boolean) => {
    const done = completedChallenges.includes(c.id);
    const open = expanded === c.id;
    return (
      <div
        key={c.id}
        className={`rounded-xl overflow-hidden ${
          done ? 'bg-[#5B8A5E]/8' : isForYou ? 'bg-[#5B8A5E]/6 ring-1 ring-[#5B8A5E]/15' : 'bg-stone-50'
        }`}
      >
        <button
          className="w-full flex items-center gap-3 p-3 text-left"
          onClick={() => setExpanded(open ? null : c.id)}
        >
          <span className={`text-xl ${done ? '' : isForYou ? '' : 'grayscale'}`}>{c.emoji}</span>
          <div className="flex-1">
            <p className={`text-sm font-semibold ${done ? 'text-[#3D6640]' : 'text-stone-700'}`}>
              {c.title}
            </p>
            <p className="text-sm text-stone-400">{c.duration}</p>
          </div>
          {done && (
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <circle cx="8" cy="8" r="7" fill="#5B8A5E" />
              <path d="M5 8l2.5 2.5 3.5-4" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          )}
        </button>
        {open && (
          <div className="px-3 pb-3">
            <p className="text-stone-600 text-sm leading-relaxed mb-2">{c.description}</p>
            <p className="text-stone-400 text-sm italic mb-3">"{c.reflection}"</p>
            {!done ? (
              <button
                onClick={() => { completeChallenge(c.id); setExpanded(null); }}
                className="w-full py-2 rounded-xl text-white text-sm font-bold"
                style={{ background: '#5B8A5E' }}
              >
                {t.progressMarkComplete}
              </button>
            ) : (
              <button
                onClick={() => { uncompleteChallenge(c.id); setExpanded(null); }}
                className="w-full py-2 rounded-xl text-stone-500 text-sm font-semibold border border-stone-200 active:bg-stone-100 transition-colors"
              >
                {t.progressMarkUncomplete}
              </button>
            )}
          </div>
        )}
      </div>
    );
  };

  if (!isPremium) {
    return (
      <>
        <div
          className="relative bg-white rounded-2xl p-4 mb-4 overflow-hidden cursor-pointer"
          onClick={() => setShowPremium(true)}
        >
          <div className="blur-sm pointer-events-none select-none">
            <h3 className="text-stone-700 font-bold text-sm mb-3">{t.progressChallengesTitle}</h3>
            <div className="space-y-2">
              {rawChallenges.slice(0, 3).map((c) => (
                <div key={c.id} className="flex items-center gap-3 p-3 rounded-xl bg-stone-50">
                  <span className="text-xl">{c.emoji}</span>
                  <div className="flex-1">
                    <p className="text-sm font-semibold text-stone-700">{c.title}</p>
                    <p className="text-sm text-stone-400">{c.duration}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
          <div className="absolute inset-0 flex flex-col items-center justify-center bg-white/75 rounded-2xl">
            <span className="text-3xl mb-2">🔒</span>
            <p className="text-stone-700 font-bold text-sm">{t.progressChallengesTitle}</p>
            <p className="text-stone-400 text-sm mt-1 text-center px-6">
              {t.challengesLockSub}
            </p>
            <div
              className="mt-3 px-5 py-2 rounded-xl text-white text-sm font-bold"
              style={{ background: '#5B8A5E' }}
            >
              {t.unlockProBtn}
            </div>
          </div>
        </div>
        {showPremium && <PremiumModal onClose={() => setShowPremium(false)} />}
      </>
    );
  }

  return (
    <div className="bg-white rounded-2xl p-4 mb-4">
      <div className="flex items-center gap-1.5">
        <h3 className="text-stone-700 font-bold text-sm">{t.progressChallengesTitle}</h3>
        <button
          onClick={() => setInfoOpen((v) => !v)}
          className="w-4 h-4 rounded-full flex items-center justify-center text-stone-300 active:text-stone-500 transition-colors"
          aria-label={t.progressChallengesInfoBody}
          aria-expanded={infoOpen}
        >
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
            <circle cx="8" cy="8" r="7" stroke="currentColor" strokeWidth="1.4" />
            <path d="M8 11.5v-3.8M8 5.5v.4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
          </svg>
        </button>
      </div>
      <p className="text-stone-400 text-xs mt-0.5 mb-3">
        {challengeResetMode === 'daily' ? t.progressChallengesSubDaily : t.progressChallengesSub}
      </p>
      {infoOpen && (
        <div className="bg-stone-50 rounded-xl p-3 mb-3 text-stone-600 text-[11px] leading-relaxed">
          {t.progressChallengesInfoBody}
        </div>
      )}
      {forYou.length > 0 && (
        <div className="flex items-center gap-2 mb-2">
          <span className="text-[10px] font-bold uppercase tracking-wide text-[#3D6640]">
            🎯 {t.challengesForYouSection}
          </span>
          <div className="flex-1 h-px bg-[#5B8A5E]/20" />
        </div>
      )}
      <div className="space-y-2">
        {forYou.map((c) => renderChallenge(c, true))}
      </div>
      {forYou.length > 0 && others.length > 0 && (
        <div className="flex items-center gap-2 mt-4 mb-2">
          <span className="text-[10px] font-bold uppercase tracking-wide text-stone-400">
            {t.challengesOtherSection}
          </span>
          <div className="flex-1 h-px bg-stone-200" />
        </div>
      )}
      <div className="space-y-2">
        {others.map((c) => renderChallenge(c, false))}
      </div>
    </div>
  );
}

function TrendGrid({
  trends,
  labels,
  t,
  showDelta,
}: {
  trends: TrendStat[];
  labels: Record<TrendStat['key'], string>;
  t: ReturnType<typeof useT>;
  showDelta: boolean;
}) {
  return (
    <div className="grid grid-cols-2 gap-2">
      {trends.map((tr) => {
        const isFlat = tr.direction === 'flat';
        const isGood = tr.direction === tr.goodDirection;
        const color = isFlat ? '#A8A29E' : isGood ? '#5B8A5E' : '#C97B5B';
        const arrow = tr.direction === 'down' ? '↓' : tr.direction === 'up' ? '↑' : '—';
        const deltaAbs = Math.abs(tr.delta);
        return (
          <div key={tr.key} className="bg-stone-50 rounded-xl p-3">
            <p className="text-stone-400 text-[10px] font-medium uppercase tracking-wider">
              {labels[tr.key]}
            </p>
            <div className="flex items-baseline gap-1.5 mt-0.5">
              <p className="text-stone-800 font-bold text-xl tabular-nums">{tr.current}</p>
              {showDelta && !isFlat && (
                <p className="text-sm font-semibold tabular-nums" style={{ color }}>
                  {arrow}{deltaAbs}
                </p>
              )}
              {showDelta && isFlat && (
                <p className="text-sm font-semibold text-stone-300">—</p>
              )}
            </div>
            {showDelta && (
              <p className="text-stone-400 text-[10px] mt-0.5">{t.trendVsLast}</p>
            )}
          </div>
        );
      })}
    </div>
  );
}

function WeeklyInsights() {
  const t = useT();
  const { dailyLogs, isPremium } = useAppStore();
  const [showPremium, setShowPremium] = useState(false);
  const [infoOpen, setInfoOpen] = useState(false);

  const insights = useMemo(() => buildWeeklyInsights(dailyLogs), [dailyLogs]);

  const statLabels: Record<TrendStat['key'], string> = {
    fastDopamine: t.trendStatFastDopamine,
    avoidance: t.trendStatAvoidance,
    urgesGivenIn: t.trendStatUrges,
    tasksDone: t.trendStatTasks,
  };

  const patternText = (() => {
    const p = insights.pattern;
    if (p.kind === 'sleepBadHabit')
      return t.patternSleepBadHabit.replace('{factor}', String(p.values.factor));
    if (p.kind === 'energyAvoidance')
      return t.patternEnergyAvoidance.replace('{lowPct}', String(p.values.lowPct));
    if (p.kind === 'moodTasks')
      return t.patternMoodTasks.replace('{diff}', String(p.values.diff));
    if (p.kind === 'bestDay')
      return t.patternBestDay.replace('{tasks}', String(p.values.tasks));
    if (p.kind === 'interventionWin')
      return t.patternInterventionWin.replace('{pct}', String(p.values.pct));
    return null;
  })();

  const checkInsTracked = Object.values(dailyLogs).filter((l) => l.checkIn).length;
  const checkInsNeeded = Math.max(0, 4 - checkInsTracked);
  const patternBlock =
    patternText ??
    (checkInsNeeded > 0
      ? t.patternNeedData.replace('{n}', String(checkInsNeeded))
      : null);

  const actionMap = {
    sleep: t.actionSleep,
    walk: t.actionWalk,
    morning: t.actionMorning,
    intervention: t.actionIntervention,
    continue: t.actionContinue,
    startTracking: t.actionStartTracking,
  };
  const actionText = actionMap[insights.action.kind];

  if (!isPremium) {
    return (
      <>
        <div
          className="relative bg-white rounded-2xl p-4 mb-4 overflow-hidden cursor-pointer"
          onClick={() => setShowPremium(true)}
        >
          <div className="blur-sm pointer-events-none select-none">
            <h3 className="text-stone-700 font-bold text-sm mb-3">{t.weeklyInsightsTitle}</h3>
            <TrendGrid trends={insights.trends} labels={statLabels} t={t} showDelta />
          </div>
          <div className="absolute inset-0 flex flex-col items-center justify-center bg-white/75 rounded-2xl">
            <span className="text-3xl mb-2">🔒</span>
            <p className="text-stone-700 font-bold text-sm">{t.weeklyInsightsTitle}</p>
            <p className="text-stone-400 text-sm mt-1 text-center px-6">
              {t.weeklyInsightsLockSub}
            </p>
            <div
              className="mt-3 px-5 py-2 rounded-xl text-white text-sm font-bold"
              style={{ background: '#5B8A5E' }}
            >
              {t.unlockProBtn}
            </div>
          </div>
        </div>
        {showPremium && <PremiumModal onClose={() => setShowPremium(false)} />}
      </>
    );
  }

  const hasBaseline = insights.trends.some((tr) => tr.previous > 0);

  return (
    <div className="bg-white rounded-2xl p-4 mb-4">
      <div className="flex items-center gap-1.5 mb-1">
        <h3 className="text-stone-700 font-bold text-sm">{t.weeklyInsightsTitle}</h3>
        <button
          onClick={() => setInfoOpen((v) => !v)}
          className="w-4 h-4 rounded-full flex items-center justify-center text-stone-300 active:text-stone-500 transition-colors"
          aria-label={t.weeklyInsightsInfoBody}
          aria-expanded={infoOpen}
        >
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
            <circle cx="8" cy="8" r="7" stroke="currentColor" strokeWidth="1.4" />
            <path d="M8 11.5v-3.8M8 5.5v.4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
          </svg>
        </button>
      </div>
      <p className="text-stone-400 text-sm mb-3">
        {hasBaseline ? t.weeklyInsightsSub : t.trendNoBaseline}
      </p>
      {infoOpen && (
        <div className="bg-stone-50 rounded-xl p-3 mb-3 text-stone-600 text-[11px] leading-relaxed">
          {t.weeklyInsightsInfoBody}
        </div>
      )}

      <TrendGrid
        trends={insights.trends}
        labels={statLabels}
        t={t}
        showDelta={hasBaseline}
      />

      {patternBlock && (
        <div className="mt-3 rounded-xl p-3" style={{ background: 'rgba(91,138,94,0.08)' }}>
          <p className="text-[#3D6640] text-[10px] font-bold uppercase tracking-widest mb-1">
            {t.patternHeader}
          </p>
          <p className="text-stone-700 text-sm leading-relaxed">{patternBlock}</p>
        </div>
      )}

      <div className="mt-2 rounded-xl p-3 bg-amber-50">
        <p className="text-amber-700 text-[10px] font-bold uppercase tracking-widest mb-1">
          {t.actionHeader}
        </p>
        <p className="text-stone-700 text-sm leading-relaxed">{actionText}</p>
      </div>
    </div>
  );
}

function buildCheckInChartData(
  dailyLogs: Record<string, { checkIn?: { sleep: number; energy: number; mood: number } }>,
  daysBack = 0,
) {
  const out: { label: string; sleep: number | null; energy: number | null; mood: number | null }[] = [];
  const today = new Date();
  for (let i = 13 + daysBack; i >= daysBack; i--) {
    const d = new Date(today);
    d.setDate(today.getDate() - i);
    const key = toLocalDateString(d);
    const c = dailyLogs[key]?.checkIn;
    out.push({
      label: `${d.getDate()}/${d.getMonth() + 1}`,
      sleep: c ? c.sleep : null,
      energy: c ? c.energy : null,
      mood: c ? c.mood : null,
    });
  }
  return out;
}

function formatDateRange(daysBack: number, locale: string): string {
  const today = new Date();
  const end = new Date(today);
  end.setDate(today.getDate() - daysBack);
  const start = new Date(end);
  start.setDate(end.getDate() - 13);
  const fmt = (d: Date) =>
    d.toLocaleDateString(locale, { day: 'numeric', month: 'short' });
  return `${fmt(start)} – ${fmt(end)}`;
}

export default function ProgressView() {
  const t = useT();
  const { dailyLogs, profile, language } = useAppStore();
  const [weekOffset, setWeekOffset] = useState(0);
  const [scoreChartInfoOpen, setScoreChartInfoOpen] = useState(false);
  const [scoreDetailOpen, setScoreDetailOpen] = useState(false);
  const [milestonesInfoOpen, setMilestonesInfoOpen] = useState(false);
  const [checkInChartInfoOpen, setCheckInChartInfoOpen] = useState(false);
  if (!profile) return null;

  const history = getScoreHistory(dailyLogs, 14, { today: t.progressToday, yesterday: t.progressYesterday, locale: language });
  const activeHistory = history.filter((h) => h.score > 0);
  const streak = calculateStreak(dailyLogs);
  const checkInChart = buildCheckInChartData(dailyLogs, weekOffset * 7);
  const hasCheckInData = checkInChart.some((d) => d.sleep !== null);
  const checkInRangeLabel = formatDateRange(weekOffset * 7, language);
  const isCurrentCheckInWindow = weekOffset === 0;

  const allLogs = Object.values(dailyLogs);
  const totalTasksDone = allLogs.reduce(
    (sum, l) => sum + l.completedTasks.length,
    0
  );
  const totalUrges = allLogs.reduce((sum, l) => sum + l.urges.length, 0);
  const urgesResisted = allLogs.reduce(
    (sum, l) => sum + l.urges.filter((u) => u.completedIntervention).length,
    0
  );
  const avgScore =
    activeHistory.length > 0
      ? Math.round(
          activeHistory.reduce((s, h) => s + h.score, 0) / activeHistory.length
        )
      : 0;

  const milestones = [
    { days: 3, label: t.milestone3, emoji: '🌱' },
    { days: 7, label: t.milestone7, emoji: '💪' },
    { days: 14, label: t.milestone14, emoji: '🔥' },
    { days: 21, label: t.milestone21, emoji: '⚡' },
    { days: 30, label: t.milestone30, emoji: '🏆' },
    { days: 60, label: t.milestone60, emoji: '🦋' },
  ];

  const sinceDate = new Date(profile.startDate + 'T12:00:00').toLocaleDateString(language || 'en', {
    month: 'long',
    day: 'numeric',
  });

  return (
    <div
      className="min-h-screen bg-[#F5F0EB] pb-52 overflow-y-auto"
      style={{ animation: 'fade-in 0.3s ease-out' }}
    >
      <div className="max-w-sm mx-auto px-4 pt-12 pb-4">
        <h1 className="text-2xl font-bold text-stone-800 mb-1">{t.progressTitle}</h1>
        <p className="text-stone-400 text-sm mb-6">
          {t.progressSince.replace('{date}', sinceDate)}
        </p>

        {/* Brain Recovery Stage */}
        <StageCard />

        {/* Stats grid */}
        <div className="grid grid-cols-2 gap-3 mb-4">
          <StatCard value={streak} label={t.progressStreakStat} emoji="🔥" color="#E4A85A" />
          <StatCard value={avgScore} label={t.progressAvgScore} emoji="📊" color="#5B8A5E" />
          <StatCard value={totalTasksDone} label={t.progressTasksDone} emoji="✅" />
          <StatCard
            value={urgesResisted}
            label={t.progressUrgesOf.replace('{n}', String(totalUrges))}
            emoji="💪"
            color="#6B9FD4"
          />
        </div>

        {/* Score Chart */}
        <div className="bg-white rounded-2xl p-4 mb-4 active:bg-stone-50/40 transition-colors cursor-pointer" onClick={() => setScoreDetailOpen(true)}>
          <div className="flex items-center justify-between gap-1.5 mb-3">
            <div className="flex items-center gap-1.5">
              <h3 className="text-stone-700 font-bold text-sm">
                {t.progressChartTitle}
              </h3>
              <button
                onClick={(e) => { e.stopPropagation(); setScoreChartInfoOpen((v) => !v); }}
                className="w-4 h-4 rounded-full flex items-center justify-center text-stone-300 active:text-stone-500 transition-colors"
                aria-label={t.progressScoreChartInfoBody}
                aria-expanded={scoreChartInfoOpen}
              >
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                  <circle cx="8" cy="8" r="7" stroke="currentColor" strokeWidth="1.4" />
                  <path d="M8 11.5v-3.8M8 5.5v.4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
                </svg>
              </button>
            </div>
            <span className="text-stone-300 text-[10px] font-medium">{t.scoreChartTapHint}</span>
          </div>
          {scoreChartInfoOpen && (
            <div className="bg-stone-50 rounded-xl p-3 mb-3 text-stone-600 text-[11px] leading-relaxed">
              {t.progressScoreChartInfoBody}
            </div>
          )}
          {activeHistory.length > 1 ? (
            <ResponsiveContainer width="100%" height={160}>
              <LineChart
                data={history}
                margin={{ top: 4, right: 4, bottom: 0, left: -20 }}
              >
                <CartesianGrid stroke="#F0EDE8" vertical={false} />
                <XAxis
                  dataKey="label"
                  tick={{ fontSize: 9, fill: '#9CA3AF' }}
                  axisLine={false}
                  tickLine={false}
                  interval={1}
                />
                <YAxis
                  domain={[0, 100]}
                  tick={{ fontSize: 9, fill: '#9CA3AF' }}
                  axisLine={false}
                  tickLine={false}
                  ticks={[0, 25, 50, 75, 100]}
                />
                <ReferenceLine
                  y={50}
                  stroke="#5B8A5E"
                  strokeDasharray="3 3"
                  strokeOpacity={0.3}
                />
                <Tooltip content={<CustomTooltip />} />
                <Line
                  type="monotone"
                  dataKey="score"
                  stroke="#5B8A5E"
                  strokeWidth={2.5}
                  dot={(props) => {
                    if (!props.value) return <g key={props.key} />;
                    return (
                      <circle
                        key={props.key}
                        cx={props.cx}
                        cy={props.cy}
                        r={3}
                        fill="#5B8A5E"
                        stroke="white"
                        strokeWidth={1.5}
                      />
                    );
                  }}
                  connectNulls={false}
                />
              </LineChart>
            </ResponsiveContainer>
          ) : (
            <div className="h-36 flex items-center justify-center">
              <p className="text-stone-300 text-sm text-center">
                {t.progressChartEmpty}
              </p>
            </div>
          )}
        </div>

        {/* Check-in line chart — sleep / energy / mood */}
        <div className="bg-white rounded-2xl p-4 mb-4">
          <div className="flex items-start justify-between gap-2 mb-3">
            <div className="min-w-0">
              <div className="flex items-center gap-1.5">
                <h3 className="text-stone-700 font-bold text-sm">{t.progressCheckInChartTitle}</h3>
                <button
                  onClick={() => setCheckInChartInfoOpen((v) => !v)}
                  className="w-4 h-4 rounded-full flex items-center justify-center text-stone-300 active:text-stone-500 transition-colors"
                  aria-label={t.progressCheckInChartInfoBody}
                  aria-expanded={checkInChartInfoOpen}
                >
                  <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                    <circle cx="8" cy="8" r="7" stroke="currentColor" strokeWidth="1.4" />
                    <path d="M8 11.5v-3.8M8 5.5v.4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
                  </svg>
                </button>
              </div>
              <p className="text-stone-400 text-xs mt-0.5 tabular-nums">{checkInRangeLabel}</p>
            </div>
            <div className="flex items-center gap-1.5 flex-shrink-0">
              <button
                onClick={() => setWeekOffset((w) => w + 1)}
                className="w-7 h-7 rounded-full bg-stone-100 active:bg-stone-200 flex items-center justify-center transition-colors"
                aria-label={t.ariaBack}
              >
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                  <path d="M7.5 2L3 6l4.5 4" stroke="#57534e" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </button>
              <button
                onClick={() => setWeekOffset((w) => Math.max(0, w - 1))}
                disabled={isCurrentCheckInWindow}
                className="w-7 h-7 rounded-full bg-stone-100 active:bg-stone-200 flex items-center justify-center transition-colors disabled:opacity-30"
                aria-label={t.ariaBack}
              >
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                  <path d="M4.5 2L9 6l-4.5 4" stroke="#57534e" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </button>
            </div>
          </div>
          {checkInChartInfoOpen && (
            <div className="bg-stone-50 rounded-xl p-3 mb-3 text-stone-600 text-[11px] leading-relaxed">
              {t.progressCheckInChartInfoBody}
            </div>
          )}
          {hasCheckInData ? (
            <>
              <ResponsiveContainer width="100%" height={160}>
                <LineChart data={checkInChart} margin={{ top: 4, right: 4, bottom: 0, left: -20 }}>
                  <CartesianGrid stroke="#F0EDE8" vertical={false} />
                  <XAxis
                    dataKey="label"
                    tick={{ fontSize: 9, fill: '#9CA3AF' }}
                    axisLine={false}
                    tickLine={false}
                    interval={1}
                  />
                  <YAxis
                    domain={[0, 5]}
                    ticks={[1, 3, 5]}
                    tick={{ fontSize: 9, fill: '#9CA3AF' }}
                    axisLine={false}
                    tickLine={false}
                  />
                  <Tooltip
                    contentStyle={{ borderRadius: 12, border: 'none', boxShadow: '0 4px 12px rgba(0,0,0,0.08)', fontSize: 12 }}
                    labelStyle={{ color: '#78716c' }}
                  />
                  <Line type="monotone" dataKey="sleep" stroke="#6B7FD4" strokeWidth={2} dot={{ r: 2.5 }} connectNulls name={t.progressSleepLabel} />
                  <Line type="monotone" dataKey="energy" stroke="#E4A85A" strokeWidth={2} dot={{ r: 2.5 }} connectNulls name={t.progressEnergyLabel} />
                  <Line type="monotone" dataKey="mood" stroke="#5B8A5E" strokeWidth={2} dot={{ r: 2.5 }} connectNulls name={t.progressMoodLabel} />
                </LineChart>
              </ResponsiveContainer>
              <div className="flex justify-center gap-4 mt-2 text-[11px]">
                <span className="flex items-center gap-1.5"><span className="w-2.5 h-2.5 rounded-full bg-[#6B7FD4]" />{t.progressSleepLabel}</span>
                <span className="flex items-center gap-1.5"><span className="w-2.5 h-2.5 rounded-full bg-[#E4A85A]" />{t.progressEnergyLabel}</span>
                <span className="flex items-center gap-1.5"><span className="w-2.5 h-2.5 rounded-full bg-[#5B8A5E]" />{t.progressMoodLabel}</span>
              </div>
            </>
          ) : (
            <div className="h-36 flex items-center justify-center">
              <p className="text-stone-300 text-sm text-center">{t.progressCheckInChartEmpty}</p>
            </div>
          )}
        </div>

        {/* Habit heatmap */}
        <HabitHeatmap />

        {/* Voice reflection journal */}
        <VoiceJournalCard />

        {/* Weekly Insights */}
        <WeeklyInsights />

        {/* Brain challenges */}
        <BrainChallenges />

        {/* Milestones */}
        <div className="bg-white rounded-2xl p-4 mb-4">
          <div className="flex items-center gap-1.5 mb-3">
            <h3 className="text-stone-700 font-bold text-sm">{t.progressMilestonesTitle}</h3>
            <button
              onClick={() => setMilestonesInfoOpen((v) => !v)}
              className="w-4 h-4 rounded-full flex items-center justify-center text-stone-300 active:text-stone-500 transition-colors"
              aria-label={t.progressMilestonesInfoBody}
              aria-expanded={milestonesInfoOpen}
            >
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                <circle cx="8" cy="8" r="7" stroke="currentColor" strokeWidth="1.4" />
                <path d="M8 11.5v-3.8M8 5.5v.4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
              </svg>
            </button>
          </div>
          {milestonesInfoOpen && (
            <div className="bg-stone-50 rounded-xl p-3 mb-3 text-stone-600 text-[11px] leading-relaxed">
              {t.progressMilestonesInfoBody}
            </div>
          )}
          <div className="space-y-2">
            {milestones.map((m) => {
              const unlocked = streak >= m.days;
              const daysAway = m.days - streak;
              return (
                <div
                  key={m.days}
                  className={`flex items-center gap-3 p-3 rounded-xl ${
                    unlocked ? 'bg-[#5B8A5E]/8' : 'bg-stone-50'
                  }`}
                >
                  <span className={`text-2xl ${unlocked ? '' : 'grayscale opacity-40'}`}>
                    {m.emoji}
                  </span>
                  <div className="flex-1">
                    <p
                      className={`text-sm font-semibold ${
                        unlocked ? 'text-[#3D6640]' : 'text-stone-400'
                      }`}
                    >
                      {m.label}
                    </p>
                    <p className="text-stone-400 text-sm">
                      {unlocked
                        ? t.progressUnlocked
                        : daysAway === 1
                        ? t.progressDayAway.replace('{n}', String(daysAway))
                        : t.progressDaysAway.replace('{n}', String(daysAway))}
                    </p>
                  </div>
                  {unlocked && (
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                      <circle cx="8" cy="8" r="7" fill="#5B8A5E" />
                      <path
                        d="M5 8l2.5 2.5 3.5-4"
                        stroke="white"
                        strokeWidth="1.5"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                      />
                    </svg>
                  )}
                </div>
              );
            })}
          </div>
        </div>
      </div>
      {scoreDetailOpen && <DopamineScoreDetailModal onClose={() => setScoreDetailOpen(false)} />}
    </div>
  );
}
