'use client';

import { useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import {
  getTodayString,
  getScoreLabel,
  getScoreColor,
  getPlanDay,
  calculateStreak,
  getTodayScore,
} from '@/lib/scoring';
import { useT } from '@/hooks/useT';
import ScoreInfoModal from './ScoreInfoModal';

function ScoreRing({ score }: { score: number }) {
  const radius = 52;
  const circumference = 2 * Math.PI * radius;
  const offset = circumference - (score / 100) * circumference;
  const color = getScoreColor(score);

  return (
    <div className="relative flex items-center justify-center">
      <svg width="140" height="140" viewBox="0 0 140 140">
        {/* Track */}
        <circle
          cx="70"
          cy="70"
          r={radius}
          fill="none"
          stroke="#E8E3DC"
          strokeWidth="10"
        />
        {/* Progress */}
        <circle
          cx="70"
          cy="70"
          r={radius}
          fill="none"
          stroke={color}
          strokeWidth="10"
          strokeLinecap="round"
          strokeDasharray={circumference}
          strokeDashoffset={offset}
          transform="rotate(-90 70 70)"
          style={{ transition: 'stroke-dashoffset 0.6s ease' }}
        />
      </svg>
      <div className="absolute flex flex-col items-center">
        <span
          className="text-4xl font-bold tabular-nums"
          style={{ color }}
        >
          {Math.round(score)}
        </span>
        <span className="text-stone-400 text-sm font-medium mt-0.5">
          / 100
        </span>
      </div>
    </div>
  );
}

export default function DopamineScoreCard() {
  const { dailyLogs, profile } = useAppStore();
  const t = useT();
  const [infoOpen, setInfoOpen] = useState(false);
  const today = getTodayString();
  const log = dailyLogs[today];
  const score = getTodayScore(dailyLogs, profile);
  const label = getScoreLabel(score);
  const color = getScoreColor(score);
  const streak = calculateStreak(dailyLogs);
  const planDay = profile ? getPlanDay(profile.startDate) : 1;

  const completedCount = log?.completedTasks.length ?? 0;
  const urgesResisted = log?.urges.filter((u) => u.completedIntervention).length ?? 0;

  return (
    <div className="bg-white rounded-3xl p-5 mb-4 shadow-sm">
      <div className="flex items-start justify-between mb-4">
        <div>
          <div className="flex items-center gap-1.5">
            <p className="text-stone-400 text-xs font-medium uppercase tracking-widest">
              {t.scoreLabel}
            </p>
            <button
              onClick={() => setInfoOpen(true)}
              className="w-4 h-4 rounded-full flex items-center justify-center text-stone-300 active:text-stone-500 transition-colors"
              aria-label={t.scoreInfoTitle}
            >
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                <circle cx="8" cy="8" r="7" stroke="currentColor" strokeWidth="1.4" />
                <path d="M8 11.5v-3.8M8 5.5v.4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
              </svg>
            </button>
          </div>
          <p
            className="text-2xl font-bold mt-0.5"
            style={{ color }}
          >
            {label}
          </p>
        </div>
        <div className="text-right">
          <p className="text-stone-400 text-sm font-medium">{t.scoreDay.replace('{n}', String(planDay))}</p>
          {streak > 0 && (
            <p className="text-amber-500 font-bold text-sm mt-0.5">
              🔥 {t.scoreStreak.replace('{n}', String(streak))}
            </p>
          )}
        </div>
      </div>

      <div className="flex items-center justify-center mb-4">
        <ScoreRing score={score} />
      </div>

      <div className="grid grid-cols-2 gap-3">
        <div className="bg-stone-50 rounded-2xl px-4 py-3 text-center">
          <p className="text-2xl font-bold text-[#5B8A5E]">{completedCount}</p>
          <p className="text-stone-400 text-sm mt-0.5">{t.scoreTasksDone}</p>
        </div>
        <div className="bg-stone-50 rounded-2xl px-4 py-3 text-center">
          <p className="text-2xl font-bold text-amber-500">{urgesResisted}</p>
          <p className="text-stone-400 text-sm mt-0.5">{t.scoreUrgesResisted}</p>
        </div>
      </div>

      {infoOpen && <ScoreInfoModal onClose={() => setInfoOpen(false)} />}
    </div>
  );
}
