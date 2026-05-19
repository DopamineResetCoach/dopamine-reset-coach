'use client';

import { useMemo, useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { getTodayScore, getScoreColor, toLocalDateString } from '@/lib/scoring';
import { useT } from '@/hooks/useT';
import BottomSheet from '@/components/ui/BottomSheet';
import type { Translations } from '@/lib/i18n/types';

type ViewMode = 'day' | 'week' | 'month';

function getScoreLabelKey(score: number): keyof Translations {
  if (score < 20) return 'scoreDepleted';
  if (score < 35) return 'scoreLow';
  if (score < 50) return 'scoreRecovering';
  if (score < 65) return 'scoreBalanced';
  if (score < 80) return 'scoreEnergized';
  return 'scoreOptimal';
}

function getScoreLabelI18n(score: number, t: Translations): string {
  return t[getScoreLabelKey(score)] as string;
}

interface ViewToggleProps {
  view: ViewMode;
  setView: (v: ViewMode) => void;
  t: Translations;
}

function ViewToggle({ view, setView, t }: ViewToggleProps) {
  const items: { id: ViewMode; label: string }[] = [
    { id: 'day', label: t.scoreViewDay },
    { id: 'week', label: t.scoreViewWeek },
    { id: 'month', label: t.scoreViewMonth },
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

function NavArrow({
  dir,
  onClick,
  disabled,
  label,
}: {
  dir: 'left' | 'right';
  onClick: () => void;
  disabled?: boolean;
  label: string;
}) {
  return (
    <button
      onClick={onClick}
      disabled={disabled}
      className="w-7 h-7 rounded-full bg-stone-100 active:bg-stone-200 flex items-center justify-center transition-colors flex-shrink-0 disabled:opacity-30"
      aria-label={label}
    >
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
        {dir === 'left' ? (
          <path d="M7.5 2L3 6l4.5 4" stroke="#57534e" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
        ) : (
          <path d="M4.5 2L9 6l-4.5 4" stroke="#57534e" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
        )}
      </svg>
    </button>
  );
}

function LevelLegend({ t }: { t: Translations }) {
  const levels: { label: string; color: string }[] = [
    { label: t.scoreDepleted, color: getScoreColor(10) },
    { label: t.scoreRecovering, color: getScoreColor(55) },
    { label: t.scoreBalanced, color: getScoreColor(55) },
    { label: t.scoreEnergized, color: getScoreColor(70) },
    { label: t.scoreOptimal, color: getScoreColor(90) },
  ];
  // Dedupe by color (Depleted/Low share <35; Balanced shares range with Recovering visually)
  const seen = new Set<string>();
  const unique = levels.filter((l) => {
    if (seen.has(l.color)) return false;
    seen.add(l.color);
    return true;
  });
  return (
    <div className="bg-stone-50 rounded-xl p-3 mb-4">
      <p className="text-stone-400 text-[10px] uppercase tracking-widest font-bold mb-2">
        {t.scoreLevelLegendTitle}
      </p>
      <div className="flex flex-wrap items-center gap-x-3 gap-y-1.5">
        {unique.map((l) => (
          <div key={l.color} className="flex items-center gap-1.5">
            <span className="w-2.5 h-2.5 rounded-full" style={{ background: l.color }} />
            <span className="text-stone-600 text-[11px]">{l.label}</span>
          </div>
        ))}
      </div>
    </div>
  );
}

export default function DopamineScoreDetailModal({ onClose }: { onClose: () => void }) {
  const dailyLogs = useAppStore((s) => s.dailyLogs);
  const profile = useAppStore((s) => s.profile);
  const language = useAppStore((s) => s.language);
  const t = useT();

  const [view, setView] = useState<ViewMode>('day');

  const locale = language === 'zh' ? 'zh-CN' : language === 'ja' ? 'ja-JP' : language === 'ar' ? 'ar' : language;
  const todayScore = getTodayScore(dailyLogs, profile);

  return (
    <BottomSheet onClose={onClose}>
      <ViewToggle view={view} setView={setView} t={t} />

      {view === 'day' && (
        <DayView todayScore={todayScore} dailyLogs={dailyLogs} locale={locale} t={t} />
      )}
      {view === 'week' && (
        <WeekView todayScore={todayScore} dailyLogs={dailyLogs} locale={locale} t={t} />
      )}
      {view === 'month' && (
        <MonthView todayScore={todayScore} dailyLogs={dailyLogs} locale={locale} t={t} />
      )}

      <button
        onClick={onClose}
        className="w-full py-3 rounded-2xl text-stone-600 font-semibold text-sm bg-stone-100 active:scale-[0.98] transition-transform mt-2"
      >
        {t.ariaClose}
      </button>
    </BottomSheet>
  );
}

interface ViewProps {
  todayScore: number;
  dailyLogs: ReturnType<typeof useAppStore.getState>['dailyLogs'];
  locale: string;
  t: Translations;
}

function DayView({ todayScore, locale, t }: ViewProps) {
  const score = todayScore;
  const color = getScoreColor(score);
  const label = getScoreLabelI18n(score, t);
  const today = useMemo(() => new Date(), []);
  const dateLabel = today.toLocaleDateString(locale, {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
  });

  const r = 70;
  const circ = 2 * Math.PI * r;
  const dashOffset = circ * (1 - score / 100);

  return (
    <>
      <div className="flex flex-col items-center mb-5">
        <p className="text-stone-400 text-[10px] uppercase tracking-widest font-bold mb-3">
          {dateLabel}
        </p>
        <div className="relative mb-3">
          <svg width="170" height="170" viewBox="0 0 170 170">
            <circle cx="85" cy="85" r={r} fill="none" stroke="#f5f5f4" strokeWidth="12" />
            <circle
              cx="85"
              cy="85"
              r={r}
              fill="none"
              stroke={color}
              strokeWidth="12"
              strokeLinecap="round"
              strokeDasharray={circ}
              strokeDashoffset={dashOffset}
              transform="rotate(-90 85 85)"
              style={{ transition: 'stroke-dashoffset 0.8s ease' }}
            />
          </svg>
          <div className="absolute inset-0 flex flex-col items-center justify-center">
            <p className="text-stone-800 font-bold text-4xl tabular-nums" style={{ color }}>
              {Math.round(score)}
            </p>
            <p className="text-stone-400 text-xs mt-0.5">/ 100</p>
          </div>
        </div>
        <p className="font-bold text-xl tracking-tight text-center" style={{ color }}>
          {label}
        </p>
      </div>

      <LevelLegend t={t} />
    </>
  );
}

interface PointData {
  date: string;
  label: string;
  score: number;
  hasLog: boolean;
  isToday: boolean;
}

function WeekView({ todayScore, dailyLogs, locale, t }: ViewProps) {
  const [weekOffset, setWeekOffset] = useState(0);
  const isCurrentWeek = weekOffset === 0;

  const analytics = useMemo(() => {
    const now = new Date();
    const week: PointData[] = [];
    const offsetDays = weekOffset * 7;
    for (let i = 6 + offsetDays; i >= offsetDays; i--) {
      const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() - i);
      const key = toLocalDateString(d);
      const isToday = i === 0;
      const log = dailyLogs[key];
      const score = isToday ? todayScore : (log?.dopamineScore ?? 0);
      week.push({
        date: key,
        label: d.toLocaleDateString(locale, { weekday: 'narrow' }).toUpperCase(),
        score,
        hasLog: !!log || isToday,
        isToday,
      });
    }

    // Previous week for delta
    let prevWeekSum = 0;
    let prevWeekDays = 0;
    for (let i = 7 + offsetDays; i < 14 + offsetDays; i++) {
      const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() - i);
      const log = dailyLogs[toLocalDateString(d)];
      if (log?.dopamineScore) {
        prevWeekSum += log.dopamineScore;
        prevWeekDays++;
      }
    }
    const prevWeekAvg = prevWeekDays > 0 ? prevWeekSum / prevWeekDays : 0;

    const validDays = week.filter((d) => d.hasLog && d.score > 0);
    const avg = validDays.length > 0 ? Math.round(validDays.reduce((s, d) => s + d.score, 0) / validDays.length) : 0;
    const best = validDays.reduce<PointData | null>((b, d) => (!b || d.score > b.score ? d : b), null);
    const deltaPct =
      prevWeekAvg > 0 ? Math.round(((avg - prevWeekAvg) / prevWeekAvg) * 100) : null;

    const firstDay = new Date(week[0].date + 'T12:00:00');
    const lastDay = new Date(week[week.length - 1].date + 'T12:00:00');
    const rangeLabel = `${firstDay.toLocaleDateString(locale, { day: 'numeric', month: 'short' })} – ${lastDay.toLocaleDateString(locale, { day: 'numeric', month: 'short' })}`;

    return { week, avg, best, deltaPct, rangeLabel };
  }, [dailyLogs, todayScore, locale, weekOffset]);

  return (
    <>
      <div className="flex items-center justify-between mb-3 gap-2">
        <div className="flex items-center gap-2 min-w-0">
          <NavArrow dir="left" onClick={() => setWeekOffset((w) => w + 1)} label={t.ariaBack} />
          <div className="min-w-0">
            <p className="text-stone-400 text-[10px] uppercase tracking-widest font-bold truncate">
              {isCurrentWeek ? t.scoreHistoryWeekTitle : analytics.rangeLabel}
            </p>
            <p className="text-stone-800 font-bold text-3xl tabular-nums mt-0.5">
              {analytics.avg || '—'}
            </p>
          </div>
          <NavArrow
            dir="right"
            onClick={() => setWeekOffset((w) => Math.max(0, w - 1))}
            disabled={isCurrentWeek}
            label={t.ariaBack}
          />
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
              <span className="text-stone-500 text-[10px] font-medium">Ø {analytics.avg}</span>
              <span>
                {analytics.deltaPct >= 0 ? '↗' : '↘'} {Math.abs(analytics.deltaPct)}%
              </span>
            </div>
          ) : (
            <div className="px-2.5 py-1 rounded-full bg-stone-100 text-stone-500 text-[10px] font-medium">
              Ø {analytics.avg || 0}
            </div>
          )}
        </div>
      </div>

      {/* 7 bars */}
      <div className="relative h-44 mb-2 px-1">
        <div className="flex items-end gap-1.5 h-full">
          {analytics.week.map((d, i) => {
            const heightPct = d.hasLog && d.score > 0 ? (d.score / 100) * 100 : 0;
            const color = d.score > 0 ? getScoreColor(d.score) : '#E7E5E4';
            const isBest = analytics.best && d.date === analytics.best.date && d.score > 0;
            return (
              <div key={i} className="flex-1 flex flex-col items-center min-w-0">
                <div className="flex-1 w-full flex flex-col items-center justify-end relative">
                  {d.score > 0 && (
                    <p className="text-stone-700 text-[10px] font-bold tabular-nums mb-1 whitespace-nowrap">
                      {Math.round(d.score)}
                    </p>
                  )}
                  <div
                    className="w-full rounded-t-lg rounded-b-lg transition-all duration-500"
                    style={{
                      height: `${heightPct}%`,
                      background: color,
                      minHeight: d.hasLog && d.score > 0 ? '6px' : '4px',
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
            {t.scoreStatBest}
          </p>
          <p className="text-stone-800 font-bold text-base tabular-nums mt-0.5">
            {analytics.best ? Math.round(analytics.best.score) : '—'}
          </p>
        </div>
        <div className="bg-stone-50 rounded-xl p-3">
          <p className="text-stone-400 text-[10px] uppercase tracking-wider font-medium">
            {t.scoreStatAvg}
          </p>
          <p className="text-stone-800 font-bold text-base tabular-nums mt-0.5">
            {analytics.avg || '—'}
          </p>
        </div>
      </div>

      <LevelLegend t={t} />
    </>
  );
}

function MonthView({ todayScore, dailyLogs, locale, t }: ViewProps) {
  const today = useMemo(() => new Date(), []);
  const currentMonthStart = useMemo(
    () => new Date(today.getFullYear(), today.getMonth(), 1),
    [today],
  );
  const [viewedMonth, setViewedMonth] = useState(currentMonthStart);
  const [selectedKey, setSelectedKey] = useState<string | null>(null);

  const isCurrentMonth =
    viewedMonth.getFullYear() === currentMonthStart.getFullYear() &&
    viewedMonth.getMonth() === currentMonthStart.getMonth();

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
      score: number;
      hasLog: boolean;
      isToday: boolean;
      isFuture: boolean;
    }
    const cells: (Cell | null)[] = [];
    for (let i = 0; i < firstWeekday; i++) cells.push(null);
    for (let d = 1; d <= daysInMonth; d++) {
      const date = new Date(year, month, d);
      const key = toLocalDateString(date);
      const isToday = key === todayKey;
      const isFuture = date > today;
      const log = dailyLogs[key];
      const score = isToday ? todayScore : (log?.dopamineScore ?? 0);
      cells.push({ day: d, key, score, hasLog: !!log || isToday, isToday, isFuture });
    }
    while (cells.length < 42) cells.push(null);

    const valid = cells.filter((c): c is Cell => !!c && c.hasLog && c.score > 0);
    const avg = valid.length > 0 ? Math.round(valid.reduce((s, c) => s + c.score, 0) / valid.length) : 0;
    const best = valid.reduce<Cell | null>((b, c) => (!b || c.score > b.score ? c : b), null);

    // Previous month for delta
    let prevSum = 0;
    let prevDays = 0;
    const prevLast = new Date(year, month, 0).getDate();
    for (let d = 1; d <= prevLast; d++) {
      const date = new Date(year, month - 1, d);
      const log = dailyLogs[toLocalDateString(date)];
      if (log?.dopamineScore) {
        prevSum += log.dopamineScore;
        prevDays++;
      }
    }
    const prevAvg = prevDays > 0 ? prevSum / prevDays : 0;
    const deltaPct = prevAvg > 0 ? Math.round(((avg - prevAvg) / prevAvg) * 100) : null;

    return { cells, avg, best, deltaPct };
  }, [dailyLogs, todayScore, viewedMonth, today]);

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
    ? t.scoreHistoryMonthTitle
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
  const selectedLabel = selectedCell && selectedCell.score > 0 ? getScoreLabelI18n(selectedCell.score, t) : '';
  const selectedColor = selectedCell && selectedCell.score > 0 ? getScoreColor(selectedCell.score) : '#A8A29E';

  return (
    <>
      <div className="flex items-center justify-between mb-3">
        <div className="flex items-center gap-2 min-w-0">
          <NavArrow dir="left" onClick={goPrev} label={t.ariaBack} />
          <div className="min-w-0">
            <p className="text-stone-400 text-[10px] uppercase tracking-widest font-bold truncate">
              {monthLabel}
            </p>
            <p className="text-stone-800 font-bold text-3xl tabular-nums mt-0.5">
              {analytics.avg || '—'}
            </p>
          </div>
          <NavArrow dir="right" onClick={goNext} disabled={isCurrentMonth} label={t.ariaBack} />
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
              <span className="text-stone-500 text-[10px] font-medium">Ø {analytics.avg}</span>
              <span>
                {analytics.deltaPct >= 0 ? '↗' : '↘'} {Math.abs(analytics.deltaPct)}%
              </span>
            </div>
          ) : (
            <div className="px-2.5 py-1 rounded-full bg-stone-100 text-stone-500 text-[10px] font-medium">
              Ø {analytics.avg || 0}
            </div>
          )}
        </div>
      </div>

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
          const isSelected = selectedKey === c.key;
          const fillColor = c.hasLog && c.score > 0 ? getScoreColor(c.score) : '#F5F5F4';
          const textColor = c.hasLog && c.score >= 35 ? '#FFFFFF' : c.isFuture ? '#D6D3D1' : '#78716C';
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
                <circle cx="20" cy="20" r="16" fill={fillColor} />
                {c.isToday && (
                  <circle cx="20" cy="20" r="18" fill="none" stroke="#5B8A5E" strokeWidth="1.5" />
                )}
                {isSelected && (
                  <circle cx="20" cy="20" r="19" fill="none" stroke="#1C1917" strokeWidth="1.5" />
                )}
              </svg>
              <p
                className="relative text-[11px] tabular-nums z-10 font-semibold"
                style={{ color: textColor }}
              >
                {c.day}
              </p>
            </button>
          );
        })}
      </div>

      {selectedCell && (
        <div
          className="rounded-xl px-4 py-3 mb-5 flex items-center justify-between gap-3"
          style={{
            background: selectedCell.score > 0 ? `${selectedColor}18` : '#f5f5f4',
          }}
        >
          <div className="min-w-0">
            <p className="text-stone-500 text-[10px] uppercase tracking-widest font-medium truncate">
              {selectedDateLabel}
            </p>
            {selectedCell.score > 0 ? (
              <p className="font-bold text-sm mt-0.5" style={{ color: selectedColor }}>
                {selectedLabel}
              </p>
            ) : (
              <p className="text-stone-400 text-xs italic mt-0.5">{t.scoreEmptyDay}</p>
            )}
          </div>
          <p className="text-stone-800 font-bold text-xl tabular-nums flex-shrink-0">
            {selectedCell.score > 0 ? Math.round(selectedCell.score) : '—'}
          </p>
        </div>
      )}

      <div className="grid grid-cols-2 gap-2 mb-5">
        <div className="bg-stone-50 rounded-xl p-3">
          <p className="text-stone-400 text-[10px] uppercase tracking-wider font-medium">
            {t.scoreStatBest}
          </p>
          <p className="text-stone-800 font-bold text-base tabular-nums mt-0.5">
            {analytics.best ? Math.round(analytics.best.score) : '—'}
          </p>
        </div>
        <div className="bg-stone-50 rounded-xl p-3">
          <p className="text-stone-400 text-[10px] uppercase tracking-wider font-medium">
            {t.scoreStatAvg}
          </p>
          <p className="text-stone-800 font-bold text-base tabular-nums mt-0.5">
            {analytics.avg || '—'}
          </p>
        </div>
      </div>

      <LevelLegend t={t} />
    </>
  );
}
