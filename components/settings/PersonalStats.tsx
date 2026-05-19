'use client';

import { useMemo, useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { useT } from '@/hooks/useT';
import { getPersonalStats } from '@/lib/personalStats';
import { getStageProgress, getStageMeta } from '@/lib/stages';
import type { Translations } from '@/lib/i18n/types';
import PersonalStatsInfoModal from './PersonalStatsInfoModal';

function StatTile({ emoji, label, value, sub }: { emoji: string; label: string; value: string; sub?: string }) {
  return (
    <div className="bg-stone-50 rounded-2xl px-3 py-3">
      <div className="flex items-center gap-1.5 mb-1">
        <span className="text-base leading-none" aria-hidden="true">{emoji}</span>
        <p className="text-stone-400 text-[10px] font-medium uppercase tracking-wider truncate">
          {label}
        </p>
      </div>
      <p className="text-stone-800 font-bold text-xl tabular-nums leading-tight">{value}</p>
      {sub && <p className="text-stone-400 text-[11px] mt-0.5">{sub}</p>}
    </div>
  );
}

function weekdayName(weekday: number, language: string, t: Translations): string {
  // Try localized first via Intl, fall back to a generic "best day" label.
  try {
    const d = new Date(2024, 0, 7 + weekday); // 7 Jan 2024 was a Sunday — index 0
    return d.toLocaleDateString(language || 'en', { weekday: 'long' });
  } catch {
    return t.profileStatsBestDayFallback;
  }
}

export default function PersonalStats() {
  const { dailyLogs, profile, language } = useAppStore();
  const t = useT();
  const [infoOpen, setInfoOpen] = useState(false);

  const stats = useMemo(() => {
    if (!profile) return null;
    return getPersonalStats(dailyLogs, profile.startDate);
  }, [dailyLogs, profile]);

  const stage = useMemo(() => {
    if (!profile) return null;
    const prog = getStageProgress(dailyLogs, profile.startDate);
    return { progress: prog, meta: getStageMeta(prog.current) };
  }, [dailyLogs, profile]);

  if (!profile || !stats || !stage) return null;

  const startedLabel = (() => {
    try {
      const d = new Date(profile.startDate + 'T12:00:00').toLocaleDateString(language || 'en', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      });
      return t.profileStatsSinceFormat
        .replace('{day}', String(stats.daysSinceStart))
        .replace('{date}', d);
    } catch {
      return t.profileStatsSinceFormat
        .replace('{day}', String(stats.daysSinceStart))
        .replace('{date}', profile.startDate);
    }
  })();

  const cleanPct = stats.daysWithAnyLog > 0
    ? Math.round((stats.cleanDays / stats.daysWithAnyLog) * 100)
    : 0;

  const motivation = profile.motivation?.trim();

  return (
    <div className="bg-white rounded-2xl p-5 mb-4 shadow-sm">
      <div className="flex items-center gap-1.5 mb-3">
        <h3 className="text-stone-700 font-bold text-sm">{t.profileStatsTitle}</h3>
        <button
          onClick={() => setInfoOpen(true)}
          className="w-4 h-4 rounded-full flex items-center justify-center text-stone-300 active:text-stone-500 transition-colors"
          aria-label={t.profileStatsInfoTitle}
        >
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
            <circle cx="8" cy="8" r="7" stroke="currentColor" strokeWidth="1.4" />
            <path d="M8 11.5v-3.8M8 5.5v.4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
          </svg>
        </button>
      </div>

      {/* Hero */}
      <div className="mb-4">
        {motivation && (
          <p className="text-stone-500 text-xs italic leading-snug mb-2">
            <span className="text-stone-400">{t.motivationReminderPrefix}</span> <span className="text-stone-700 not-italic">{motivation}</span>
          </p>
        )}
        <div className="flex items-center gap-3">
          <div className="flex-shrink-0 w-12 h-12 rounded-2xl bg-stone-50 flex items-center justify-center text-2xl" aria-hidden="true">
            {stage.meta.emoji}
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-stone-700 font-bold text-sm leading-tight">
              {t[stage.meta.nameKey]}
            </p>
            <p className="text-stone-400 text-xs mt-0.5 leading-snug">{startedLabel}</p>
          </div>
        </div>
      </div>

      {/* 6 tiles */}
      <div className="grid grid-cols-2 gap-2 mb-3">
        <StatTile
          emoji="🔥"
          label={t.profileStatsLongestStreak}
          value={String(stats.longestStreak)}
          sub={stats.longestStreak === 1 ? t.profileStatsDayUnit : t.profileStatsDaysUnit}
        />
        <StatTile
          emoji="🏆"
          label={t.profileStatsHighestScore}
          value={String(Math.round(stats.highestScore))}
          sub="/ 100"
        />
        <StatTile
          emoji="🧠"
          label={t.profileStatsUrgesResisted}
          value={String(stats.urgesResistedTotal)}
        />
        <StatTile
          emoji="✅"
          label={t.profileStatsTasksDone}
          value={String(stats.tasksCompletedTotal)}
        />
        <StatTile
          emoji="💧"
          label={t.profileStatsCleanDays}
          value={String(stats.cleanDays)}
          sub={`${cleanPct}%`}
        />
        <StatTile
          emoji="🎙️"
          label={t.profileStatsReflectionMin}
          value={stats.reflectionMinutes < 1 && stats.reflectionCount > 0 ? '<1' : String(stats.reflectionMinutes)}
          sub={(stats.reflectionMinutes === 1 ? t.profileStatsMinUnit : t.profileStatsMinsUnit)}
        />
      </div>

      {/* Pattern insight — only if we have a clear winner */}
      {stats.bestWeekday && (
        <div className="bg-[#5B8A5E]/8 border border-[#5B8A5E]/20 rounded-xl px-3 py-2.5">
          <p className="text-[#3D6640] text-xs leading-relaxed">
            {t.profileStatsBestDayInsight
              .replace('{weekday}', weekdayName(stats.bestWeekday.weekday, language, t))
              .replace('{score}', String(stats.bestWeekday.avgScore))}
          </p>
        </div>
      )}

      {infoOpen && <PersonalStatsInfoModal onClose={() => setInfoOpen(false)} />}
    </div>
  );
}
