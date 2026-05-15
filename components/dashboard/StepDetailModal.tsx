'use client';

import { useEffect, useMemo, useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { calcStrideScore } from '@/lib/steps';
import { toLocalDateString } from '@/lib/scoring';
import { useT } from '@/hooks/useT';
import BottomSheet from '@/components/ui/BottomSheet';

const THRESHOLDS = [
  { steps: 2000, stateKey: 'strideStateWakeup' as const, emoji: '🌅' },
  { steps: 5000, stateKey: 'strideStateFocus' as const, emoji: '🎯' },
  { steps: 8000, stateKey: 'strideStateDopamine' as const, emoji: '⚡' },
  { steps: 12000, stateKey: 'strideStateRecovery' as const, emoji: '🌿' },
  { steps: 15000, stateKey: 'strideStateElite' as const, emoji: '🧠' },
];

const STATE_COLORS = ['#D6D3D1', '#FCD34D', '#84CC16', '#5B8A5E', '#6B7FD4', '#A855F7'];

type ViewMode = 'day' | 'week' | 'month';

function getStateIndex(steps: number): number {
  if (steps >= 15000) return 4;
  if (steps >= 12000) return 3;
  if (steps >= 8000) return 2;
  if (steps >= 5000) return 1;
  if (steps >= 2000) return 0;
  return -1;
}

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

function getThresholdFact(steps: number, t: ReturnType<typeof useT>): string {
  if (steps >= 15000) return t.stepFact13;
  if (steps >= 12000) return t.stepFact9;
  if (steps >= 8000) return t.stepFact2;
  if (steps >= 5000) return t.stepFact11;
  if (steps >= 2000) return t.stepFact1;
  return t.stepFact1;
}

interface ViewToggleProps {
  view: ViewMode;
  setView: (v: ViewMode) => void;
  t: ReturnType<typeof useT>;
}

function ViewToggle({ view, setView, t }: ViewToggleProps) {
  const items: { id: ViewMode; label: string }[] = [
    { id: 'day', label: t.stepViewDay },
    { id: 'week', label: t.stepViewWeek },
    { id: 'month', label: t.stepViewMonth },
  ];
  return (
    <div className="flex items-center gap-1 p-1 bg-stone-100 rounded-full mb-4">
      {items.map((it) => {
        const active = view === it.id;
        return (
          <button
            key={it.id}
            onClick={() => setView(it.id)}
            className={`flex-1 py-1.5 rounded-full text-xs font-bold tracking-wide transition-colors ${
              active ? 'bg-white text-stone-800 shadow-sm' : 'text-stone-500'
            }`}
          >
            {it.label}
          </button>
        );
      })}
    </div>
  );
}

function StatsInfoBar({ t }: { t: ReturnType<typeof useT> }) {
  return (
    <div className="bg-stone-50 rounded-xl p-3 mb-4 text-stone-600 text-[11px] leading-relaxed space-y-1">
      <p>{t.stepStatsInfoAvg}</p>
      <p>{t.stepStatsInfoDelta}</p>
    </div>
  );
}

function StatsInfoButton({ open, onToggle, label }: { open: boolean; onToggle: () => void; label: string }) {
  return (
    <button
      onClick={onToggle}
      className="w-6 h-6 rounded-full flex items-center justify-center text-stone-300 active:text-stone-500 transition-colors flex-shrink-0"
      aria-label={label}
      aria-expanded={open}
    >
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
        <circle cx="8" cy="8" r="7" stroke="currentColor" strokeWidth="1.4" />
        <path d="M8 11.5v-3.8M8 5.5v.4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
      </svg>
    </button>
  );
}

function AttributionBar({ label }: { label: string }) {
  return (
    <div className="flex items-center gap-1.5 mb-3 pb-2.5 border-b border-stone-100">
      <svg width="14" height="14" viewBox="0 0 12 12" fill="none" aria-hidden="true">
        <path
          d="M6 10.5S1 7.5 1 4.5A2.5 2.5 0 0 1 6 3.2 2.5 2.5 0 0 1 11 4.5C11 7.5 6 10.5 6 10.5Z"
          fill="#FF2D55"
        />
      </svg>
      <p className="text-[11px] font-semibold text-stone-600">{label}</p>
    </div>
  );
}

export default function StepDetailModal({ onClose }: { onClose: () => void }) {
  const todaySteps = useAppStore((s) => s.todaySteps);
  const stepGoal = useAppStore((s) => s.stepGoal);
  const stepsHistory = useAppStore((s) => s.stepsHistory);
  const refreshSteps = useAppStore((s) => s.refreshSteps);
  const refreshStepsHistory = useAppStore((s) => s.refreshStepsHistory);
  const language = useAppStore((s) => s.language);
  const t = useT();

  const [view, setView] = useState<ViewMode>('day');

  useEffect(() => {
    refreshStepsHistory(365);
  }, [refreshStepsHistory]);

  const locale = language === 'zh' ? 'zh-CN' : language === 'ja' ? 'ja-JP' : language === 'ar' ? 'ar' : language;

  return (
    <BottomSheet onClose={onClose}>
      <AttributionBar label={t.stepCardAttribution} />
      <ViewToggle view={view} setView={setView} t={t} />

      {view === 'day' && (
        <DayView
          todaySteps={todaySteps}
          stepGoal={stepGoal}
          locale={locale}
          t={t}
        />
      )}
      {view === 'week' && (
        <WeekView
          stepsHistory={stepsHistory}
          todaySteps={todaySteps}
          stepGoal={stepGoal}
          locale={locale}
          t={t}
        />
      )}
      {view === 'month' && (
        <MonthView
          stepsHistory={stepsHistory}
          todaySteps={todaySteps}
          stepGoal={stepGoal}
          locale={locale}
          t={t}
        />
      )}

      <button
        onClick={() => {
          refreshSteps();
          refreshStepsHistory(365);
        }}
        className="w-full py-3 rounded-2xl text-stone-600 font-semibold text-sm bg-stone-100 active:scale-[0.98] transition-transform mt-2"
      >
        {t.strideRefresh}
      </button>
    </BottomSheet>
  );
}

interface ViewProps {
  stepsHistory: Record<string, number>;
  todaySteps: number;
  stepGoal: number;
  locale: string;
  t: ReturnType<typeof useT>;
}

function DayView({
  todaySteps,
  stepGoal,
  locale,
  t,
}: Omit<ViewProps, 'stepsHistory'>) {
  const score = calcStrideScore(todaySteps, stepGoal);
  const progress = Math.min(1, todaySteps / stepGoal);
  const state = getStrideStateName(todaySteps, t);
  const message = getStrideMessage(todaySteps, stepGoal, t);
  const fact = getThresholdFact(todaySteps, t);

  const scoreColor =
    score >= 75 ? '#4CAF50' : score >= 50 ? '#8BC34A' : score >= 25 ? '#FFC107' : '#FF7043';

  const r = 70;
  const circ = 2 * Math.PI * r;
  const dashOffset = circ * (1 - progress);

  return (
    <>
      <div className="flex flex-col items-center mb-5">
        <div className="relative mb-3">
          <svg width="170" height="170" viewBox="0 0 170 170">
            <circle cx="85" cy="85" r={r} fill="none" stroke="#f5f5f4" strokeWidth="12" />
            <circle
              cx="85" cy="85" r={r}
              fill="none"
              stroke={scoreColor}
              strokeWidth="12"
              strokeLinecap="round"
              strokeDasharray={circ}
              strokeDashoffset={dashOffset}
              transform="rotate(-90 85 85)"
              style={{ transition: 'stroke-dashoffset 0.8s ease' }}
            />
          </svg>
          <div className="absolute inset-0 flex flex-col items-center justify-center">
            <p className="text-stone-800 font-bold text-3xl tabular-nums">
              {todaySteps.toLocaleString(locale)}
            </p>
            <p className="text-stone-400 text-xs mt-0.5">/ {stepGoal.toLocaleString(locale)}</p>
          </div>
        </div>
        <p className="font-bold text-xl tracking-tight text-center" style={{ color: scoreColor }}>
          {state}
        </p>
        <p className="text-stone-500 text-sm text-center mt-1 leading-relaxed px-4">{message}</p>
      </div>

      <div className="mb-5">
        <p className="text-stone-400 text-[10px] uppercase tracking-widest font-bold mb-2">
          {t.stepHistoryProgressionTitle}
        </p>
        <div className="space-y-1.5">
          {THRESHOLDS.map((th) => {
            const achieved = todaySteps >= th.steps;
            const isCurrent =
              !achieved &&
              THRESHOLDS.find((x) => x.steps > todaySteps)?.steps === th.steps;
            const remaining = th.steps - todaySteps;
            return (
              <div
                key={th.steps}
                className={`flex items-center gap-3 px-3 py-2.5 rounded-xl ${
                  achieved
                    ? 'bg-[#5B8A5E]/8'
                    : isCurrent
                    ? 'bg-amber-50'
                    : 'bg-stone-50'
                }`}
              >
                <span className={`text-xl ${achieved || isCurrent ? '' : 'grayscale opacity-40'}`}>
                  {th.emoji}
                </span>
                <div className="flex-1 min-w-0">
                  <p
                    className={`font-bold text-sm ${
                      achieved ? 'text-[#3D6640]' : isCurrent ? 'text-amber-700' : 'text-stone-400'
                    }`}
                  >
                    {t[th.stateKey]}
                  </p>
                  <p className="text-stone-400 text-[11px]">
                    {th.steps.toLocaleString(locale)} {t.settingsStepGoalUnit ?? 'steps'}
                  </p>
                </div>
                {achieved ? (
                  <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <circle cx="9" cy="9" r="8" fill="#5B8A5E" />
                    <path d="M5.5 9l2.5 2.5 4.5-5" stroke="white" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                ) : isCurrent ? (
                  <p className="text-amber-700 text-[11px] font-bold tabular-nums whitespace-nowrap">
                    {t.stepStepsToGo.replace('{n}', remaining.toLocaleString(locale))}
                  </p>
                ) : (
                  <span className="text-stone-300 text-base">🔒</span>
                )}
              </div>
            );
          })}
        </div>
      </div>

      <div className="bg-[#5B8A5E]/8 border border-[#5B8A5E]/20 rounded-2xl px-4 py-3 mb-3">
        <p className="text-[#3D6640] text-[10px] uppercase tracking-widest font-bold mb-1">💡</p>
        <p className="text-stone-700 text-xs leading-relaxed">{fact}</p>
      </div>
    </>
  );
}

function WeekView({ stepsHistory, todaySteps, stepGoal, locale, t }: ViewProps) {
  const [statsInfoOpen, setStatsInfoOpen] = useState(false);
  const [weekOffset, setWeekOffset] = useState(0);
  const isCurrentWeek = weekOffset === 0;

  const analytics = useMemo(() => {
    const now = new Date();
    const week: { date: string; label: string; steps: number; isToday: boolean; stateIdx: number }[] = [];
    const offsetDays = weekOffset * 7;
    for (let i = 6 + offsetDays; i >= offsetDays; i--) {
      const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() - i);
      const key = toLocalDateString(d);
      const isToday = i === 0;
      const steps = isToday ? todaySteps : (stepsHistory[key] ?? 0);
      week.push({
        date: key,
        label: d.toLocaleDateString(locale, { weekday: 'narrow' }).toUpperCase(),
        steps,
        isToday,
        stateIdx: getStateIndex(steps),
      });
    }
    let prevWeekTotal = 0;
    for (let i = 7 + offsetDays; i < 14 + offsetDays; i++) {
      const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() - i);
      prevWeekTotal += stepsHistory[toLocalDateString(d)] ?? 0;
    }
    const total = week.reduce((s, d) => s + d.steps, 0);
    const daysWithData = week.filter((d) => d.steps > 0).length;
    const avg = daysWithData > 0 ? Math.round(total / daysWithData) : 0;
    const best = week.reduce<typeof week[number] | null>((b, d) => (!b || d.steps > b.steps ? d : b), null);
    const deltaPct =
      prevWeekTotal > 0
        ? Math.round(((total - prevWeekTotal) / prevWeekTotal) * 100)
        : null;

    const firstDay = week[0] ? new Date(week[0].date + 'T12:00:00') : now;
    const lastDay = week[week.length - 1] ? new Date(week[week.length - 1].date + 'T12:00:00') : now;
    const rangeLabel = `${firstDay.toLocaleDateString(locale, { day: 'numeric', month: 'short' })} – ${lastDay.toLocaleDateString(locale, { day: 'numeric', month: 'short' })}`;

    return { week, total, avg, best, deltaPct, daysWithData, rangeLabel };
  }, [stepsHistory, todaySteps, locale, weekOffset]);

  const maxBar = Math.max(...analytics.week.map((d) => d.steps), stepGoal);
  const avgLineTop = maxBar > 0 ? 100 - (analytics.avg / maxBar) * 100 : 100;
  const fact = getThresholdFact(todaySteps, t);

  return (
    <>
      <div className="flex items-center justify-between mb-3 gap-2">
        <div className="flex items-center gap-2 min-w-0">
          <button
            onClick={() => setWeekOffset((w) => w + 1)}
            className="w-7 h-7 rounded-full bg-stone-100 active:bg-stone-200 flex items-center justify-center transition-colors flex-shrink-0"
            aria-label={t.ariaBack}
          >
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
              <path d="M7.5 2L3 6l4.5 4" stroke="#57534e" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </button>
          <div className="min-w-0">
            <p className="text-stone-400 text-[10px] uppercase tracking-widest font-bold truncate">
              {isCurrentWeek ? t.stepHistoryWeekTitle : analytics.rangeLabel}
            </p>
            <p className="text-stone-800 font-bold text-3xl tabular-nums mt-0.5">
              {analytics.total.toLocaleString(locale)}
            </p>
          </div>
          <button
            onClick={() => setWeekOffset((w) => Math.max(0, w - 1))}
            disabled={isCurrentWeek}
            className="w-7 h-7 rounded-full bg-stone-100 active:bg-stone-200 flex items-center justify-center transition-colors flex-shrink-0 disabled:opacity-30"
            aria-label={t.ariaBack}
          >
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
              <path d="M4.5 2L9 6l-4.5 4" stroke="#57534e" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </button>
        </div>
        <div className="flex items-center gap-1 flex-shrink-0">
          {analytics.deltaPct !== null ? (
            <div
              className="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold"
              style={{
                background: analytics.deltaPct >= 0 ? '#5B8A5E15' : '#C97B5B15',
                color: analytics.deltaPct >= 0 ? '#3D6640' : '#9F4A2B',
              }}
            >
              <span className="text-stone-500 text-[10px] font-medium">Ø {analytics.avg.toLocaleString(locale)}</span>
              <span>{analytics.deltaPct >= 0 ? '↗' : '↘'} {Math.abs(analytics.deltaPct)}%</span>
            </div>
          ) : (
            <div className="px-2.5 py-1 rounded-full bg-stone-100 text-stone-500 text-[10px] font-medium">
              Ø {analytics.avg.toLocaleString(locale)}
            </div>
          )}
          <StatsInfoButton open={statsInfoOpen} onToggle={() => setStatsInfoOpen((v) => !v)} label={t.stepStatsInfoAvg} />
        </div>
      </div>

      {statsInfoOpen && <StatsInfoBar t={t} />}

      <div className="relative h-44 mb-2 px-1">
        {/* Average dashed line */}
        {analytics.avg > 0 && (
          <div
            className="absolute left-0 right-0 border-t-2 border-dashed border-stone-300/70 z-10"
            style={{ top: `${avgLineTop}%` }}
          />
        )}
        <div className="flex items-end gap-1.5 h-full relative">
          {analytics.week.map((d, i) => {
            const heightPct = maxBar > 0 ? (d.steps / maxBar) * 100 : 0;
            const color = STATE_COLORS[d.stateIdx + 1];
            const isBest = analytics.best && d.date === analytics.best.date && d.steps > 0;
            return (
              <div key={i} className="flex-1 flex flex-col items-center min-w-0">
                <div className="flex-1 w-full flex flex-col items-center justify-end relative">
                  {d.steps > 0 && (
                    <p className="text-stone-700 text-[10px] font-bold tabular-nums mb-1 whitespace-nowrap">
                      {d.steps.toLocaleString(locale)}
                    </p>
                  )}
                  <div
                    className="w-full rounded-t-lg rounded-b-lg transition-all duration-500"
                    style={{
                      height: `${heightPct}%`,
                      background: color,
                      minHeight: d.steps > 0 ? '6px' : '4px',
                      opacity: d.isToday || isBest ? 1 : 0.85,
                      boxShadow: d.isToday ? `0 0 0 2px ${color}40` : 'none',
                    }}
                  />
                </div>
                {isBest && (
                  <span className="text-[12px] -mt-0.5 mb-0.5" aria-label="best day">⭐</span>
                )}
              </div>
            );
          })}
        </div>
      </div>

      <div className="flex items-center gap-1.5 mb-5 px-1">
        {analytics.week.map((d, i) => (
          <p
            key={i}
            className={`flex-1 text-center text-[10px] tabular-nums ${
              d.isToday ? 'text-stone-700 font-bold' : 'text-stone-400'
            }`}
          >
            {d.label}
          </p>
        ))}
      </div>

      <div className="grid grid-cols-2 gap-2 mb-5">
        <div className="bg-stone-50 rounded-xl p-3">
          <p className="text-stone-400 text-[10px] uppercase tracking-wider font-medium">
            {t.stepStatBest}
          </p>
          <p className="text-stone-800 font-bold text-base tabular-nums mt-0.5">
            {(analytics.best?.steps ?? 0).toLocaleString(locale)}
          </p>
        </div>
        <div className="bg-stone-50 rounded-xl p-3">
          <p className="text-stone-400 text-[10px] uppercase tracking-wider font-medium">
            {t.stepStatAvg}
          </p>
          <p className="text-stone-800 font-bold text-base tabular-nums mt-0.5">
            {analytics.avg.toLocaleString(locale)}
          </p>
        </div>
      </div>

      <div className="bg-[#5B8A5E]/8 border border-[#5B8A5E]/20 rounded-2xl px-4 py-3 mb-3">
        <p className="text-[#3D6640] text-[10px] uppercase tracking-widest font-bold mb-1">💡</p>
        <p className="text-stone-700 text-xs leading-relaxed">{fact}</p>
      </div>
    </>
  );
}

function MonthView({ stepsHistory, todaySteps, stepGoal, locale, t }: ViewProps) {
  const today = useMemo(() => new Date(), []);
  const currentMonthStart = useMemo(
    () => new Date(today.getFullYear(), today.getMonth(), 1),
    [today],
  );
  const [viewedMonth, setViewedMonth] = useState(currentMonthStart);
  const [selectedKey, setSelectedKey] = useState<string | null>(null);
  const [statsInfoOpen, setStatsInfoOpen] = useState(false);

  const isCurrentMonth =
    viewedMonth.getFullYear() === currentMonthStart.getFullYear()
    && viewedMonth.getMonth() === currentMonthStart.getMonth();

  const analytics = useMemo(() => {
    const todayKey = toLocalDateString(today);
    const year = viewedMonth.getFullYear();
    const month = viewedMonth.getMonth();
    const firstOfMonth = new Date(year, month, 1);
    const lastOfMonth = new Date(year, month + 1, 0);
    const daysInMonth = lastOfMonth.getDate();
    const firstWeekday = (firstOfMonth.getDay() + 6) % 7;

    interface Cell {
      day: number;
      key: string;
      steps: number;
      hit: boolean;
      isToday: boolean;
      isFuture: boolean;
      progress: number;
    }
    const cells: (Cell | null)[] = [];
    for (let i = 0; i < firstWeekday; i++) cells.push(null);
    for (let d = 1; d <= daysInMonth; d++) {
      const date = new Date(year, month, d);
      const key = toLocalDateString(date);
      const isToday = key === todayKey;
      const isFuture = date > today;
      const steps = isToday ? todaySteps : (stepsHistory[key] ?? 0);
      const progress = stepGoal > 0 ? Math.min(1, steps / stepGoal) : 0;
      const hit = steps >= stepGoal && stepGoal > 0;
      cells.push({ day: d, key, steps, hit, isToday, isFuture, progress });
    }
    while (cells.length < 42) cells.push(null);

    let monthTotal = 0;
    let monthDaysWithData = 0;
    for (const c of cells) {
      if (c && c.steps > 0) {
        monthTotal += c.steps;
        monthDaysWithData++;
      }
    }
    const monthAvg = monthDaysWithData > 0 ? Math.round(monthTotal / monthDaysWithData) : 0;
    const bestDay = cells.reduce<Cell | null>(
      (b, c) => (c && (!b || c.steps > b.steps) ? c : b),
      null,
    );

    let prevMonthTotal = 0;
    const prevMonth = new Date(year, month - 1, 1);
    const prevLast = new Date(year, month, 0).getDate();
    for (let d = 1; d <= prevLast; d++) {
      const date = new Date(prevMonth.getFullYear(), prevMonth.getMonth(), d);
      prevMonthTotal += stepsHistory[toLocalDateString(date)] ?? 0;
    }
    const deltaPct =
      prevMonthTotal > 0
        ? Math.round(((monthTotal - prevMonthTotal) / prevMonthTotal) * 100)
        : null;

    return { cells, monthTotal, monthAvg, bestDay, deltaPct };
  }, [stepsHistory, todaySteps, stepGoal, viewedMonth, today]);

  const weekdayLabels = useMemo(() => {
    const labels: string[] = [];
    const base = new Date(2024, 0, 1);
    for (let i = 0; i < 7; i++) {
      const d = new Date(base.getFullYear(), base.getMonth(), base.getDate() + i);
      labels.push(d.toLocaleDateString(locale, { weekday: 'narrow' }).toUpperCase());
    }
    return labels;
  }, [locale]);

  const monthLabel = isCurrentMonth
    ? t.stepHistoryMonthTitle
    : viewedMonth.toLocaleDateString(locale, { month: 'long', year: 'numeric' });

  const goPrev = () => {
    setSelectedKey(null);
    setViewedMonth(new Date(viewedMonth.getFullYear(), viewedMonth.getMonth() - 1, 1));
  };
  const goNext = () => {
    if (isCurrentMonth) return;
    setSelectedKey(null);
    setViewedMonth(new Date(viewedMonth.getFullYear(), viewedMonth.getMonth() + 1, 1));
  };

  const selectedCell = selectedKey
    ? analytics.cells.find((c) => c && c.key === selectedKey) ?? null
    : null;
  const selectedDate = selectedKey ? new Date(selectedKey + 'T12:00:00') : null;
  const selectedDateLabel = selectedDate
    ? selectedDate.toLocaleDateString(locale, { weekday: 'long', day: 'numeric', month: 'long' })
    : '';
  const selectedStateName = selectedCell ? getStrideStateName(selectedCell.steps, t) : '';
  const selectedStateIdx = selectedCell ? getStateIndex(selectedCell.steps) : -1;
  const selectedStateColor = STATE_COLORS[selectedStateIdx + 1];

  const fact = getThresholdFact(todaySteps, t);

  return (
    <>
      <div className="flex items-center justify-between mb-3">
        <div className="flex items-center gap-2 min-w-0">
          <button
            onClick={goPrev}
            className="w-7 h-7 rounded-full bg-stone-100 active:bg-stone-200 flex items-center justify-center transition-colors flex-shrink-0"
            aria-label={t.ariaBack}
          >
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
              <path d="M7.5 2L3 6l4.5 4" stroke="#57534e" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </button>
          <div className="min-w-0">
            <p className="text-stone-400 text-[10px] uppercase tracking-widest font-bold truncate">
              {monthLabel}
            </p>
            <p className="text-stone-800 font-bold text-3xl tabular-nums mt-0.5">
              {analytics.monthTotal.toLocaleString(locale)}
            </p>
          </div>
          <button
            onClick={goNext}
            disabled={isCurrentMonth}
            className="w-7 h-7 rounded-full bg-stone-100 active:bg-stone-200 flex items-center justify-center transition-colors flex-shrink-0 disabled:opacity-30"
            aria-label={t.ariaBack}
          >
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
              <path d="M4.5 2L9 6l-4.5 4" stroke="#57534e" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </button>
        </div>
        <div className="flex items-center gap-1 flex-shrink-0">
          {analytics.deltaPct !== null ? (
            <div
              className="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold"
              style={{
                background: analytics.deltaPct >= 0 ? '#5B8A5E15' : '#C97B5B15',
                color: analytics.deltaPct >= 0 ? '#3D6640' : '#9F4A2B',
              }}
            >
              <span className="text-stone-500 text-[10px] font-medium">Ø {analytics.monthAvg.toLocaleString(locale)}</span>
              <span>{analytics.deltaPct >= 0 ? '↗' : '↘'} {Math.abs(analytics.deltaPct)}%</span>
            </div>
          ) : (
            <div className="px-2.5 py-1 rounded-full bg-stone-100 text-stone-500 text-[10px] font-medium">
              Ø {analytics.monthAvg.toLocaleString(locale)}
            </div>
          )}
          <StatsInfoButton open={statsInfoOpen} onToggle={() => setStatsInfoOpen((v) => !v)} label={t.stepStatsInfoAvg} />
        </div>
      </div>

      {statsInfoOpen && <StatsInfoBar t={t} />}

      <div className="grid grid-cols-7 gap-1 mb-2">
        {weekdayLabels.map((l, i) => (
          <p key={i} className="text-center text-[9px] text-stone-400 font-bold tracking-wider">
            {l}
          </p>
        ))}
      </div>

      <div className="grid grid-cols-7 gap-1 mb-3">
        {analytics.cells.map((c, i) => {
          if (!c) return <div key={i} className="aspect-square" />;
          const circumference = 2 * Math.PI * 16;
          const offset = circumference * (1 - c.progress);
          const isSelected = selectedKey === c.key;
          return (
            <button
              key={i}
              onClick={() =>
                setSelectedKey(isSelected ? null : c.isFuture ? null : c.key)
              }
              disabled={c.isFuture}
              className="aspect-square relative flex items-center justify-center disabled:pointer-events-none active:scale-95 transition-transform"
            >
              <svg viewBox="0 0 40 40" className="absolute inset-0 w-full h-full">
                <circle cx="20" cy="20" r="16" fill="none" stroke="#f5f5f4" strokeWidth="2.5" />
                {c.steps > 0 && !c.hit && (
                  <circle
                    cx="20" cy="20" r="16"
                    fill="none"
                    stroke="#5B8A5E"
                    strokeWidth="2.5"
                    strokeLinecap="round"
                    strokeDasharray={circumference}
                    strokeDashoffset={offset}
                    transform="rotate(-90 20 20)"
                  />
                )}
                {c.hit && <circle cx="20" cy="20" r="16" fill="#5B8A5E" />}
                {c.isToday && (
                  <circle cx="20" cy="20" r="18" fill="none" stroke="#5B8A5E" strokeWidth="1.5" />
                )}
                {isSelected && (
                  <circle cx="20" cy="20" r="19" fill="none" stroke="#1C1917" strokeWidth="1.5" />
                )}
              </svg>
              <p
                className={`relative text-[11px] tabular-nums z-10 ${
                  c.hit
                    ? 'text-white font-bold'
                    : c.isFuture
                    ? 'text-stone-300'
                    : c.isToday
                    ? 'text-[#3D6640] font-bold'
                    : c.steps > 0
                    ? 'text-stone-700 font-semibold'
                    : 'text-stone-400'
                }`}
              >
                {c.day}
              </p>
              {c.hit && (
                <span className="absolute -bottom-0.5 text-[8px] z-10" aria-label="goal hit">⭐</span>
              )}
            </button>
          );
        })}
      </div>

      {/* Selected day info bar */}
      {selectedCell && (
        <div
          className="rounded-xl px-4 py-3 mb-5 flex items-center justify-between gap-3"
          style={{
            background: selectedCell.steps > 0 ? `${selectedStateColor}18` : '#f5f5f4',
          }}
        >
          <div className="min-w-0">
            <p className="text-stone-500 text-[10px] uppercase tracking-widest font-medium truncate">
              {selectedDateLabel}
            </p>
            {selectedCell.steps > 0 ? (
              <p
                className="font-bold text-sm mt-0.5"
                style={{ color: selectedStateColor }}
              >
                {selectedStateName}
              </p>
            ) : (
              <p className="text-stone-400 text-xs italic mt-0.5">{t.profileNoCheckInData}</p>
            )}
          </div>
          <p className="text-stone-800 font-bold text-xl tabular-nums flex-shrink-0">
            {selectedCell.steps.toLocaleString(locale)}
          </p>
        </div>
      )}

      <div className="grid grid-cols-2 gap-2 mb-5">
        <div className="bg-stone-50 rounded-xl p-3">
          <p className="text-stone-400 text-[10px] uppercase tracking-wider font-medium">
            {t.stepStatBest}
          </p>
          <p className="text-stone-800 font-bold text-base tabular-nums mt-0.5">
            {(analytics.bestDay?.steps ?? 0).toLocaleString(locale)}
          </p>
        </div>
        <div className="bg-stone-50 rounded-xl p-3">
          <p className="text-stone-400 text-[10px] uppercase tracking-wider font-medium">
            {t.stepStatAvg}
          </p>
          <p className="text-stone-800 font-bold text-base tabular-nums mt-0.5">
            {analytics.monthAvg.toLocaleString(locale)}
          </p>
        </div>
      </div>

      <div className="bg-[#5B8A5E]/8 border border-[#5B8A5E]/20 rounded-2xl px-4 py-3 mb-3">
        <p className="text-[#3D6640] text-[10px] uppercase tracking-widest font-bold mb-1">💡</p>
        <p className="text-stone-700 text-xs leading-relaxed">{fact}</p>
      </div>
    </>
  );
}
