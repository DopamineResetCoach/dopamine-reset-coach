'use client';

import { useT } from '@/hooks/useT';
import BottomSheet from '@/components/ui/BottomSheet';
import { useAppStore } from '@/store/useAppStore';
import {
  getTodayString,
  getCheckInAverage,
  calculateStreak,
  calculateDailyDebt,
  getScoreBreakdown,
  type BreakdownItem,
  type ScoreBreakdown,
} from '@/lib/scoring';
import type { Translations } from '@/lib/i18n/types';

function formatLabel(
  t: Translations,
  item: BreakdownItem,
): string {
  const raw = (t as unknown as Record<string, string>)[item.labelKey] ?? item.labelKey;
  if (!item.labelParams) return raw;
  return Object.entries(item.labelParams).reduce(
    (acc, [k, v]) => acc.replace(`{${k}}`, String(v)),
    raw,
  );
}

function formatTip(
  t: Translations,
  tip: NonNullable<ScoreBreakdown['tip']>,
): string {
  const raw = (t as unknown as Record<string, string>)[tip.messageKey] ?? tip.messageKey;
  if (!tip.params) return raw;
  return Object.entries(tip.params).reduce(
    (acc, [k, v]) => acc.replace(`{${k}}`, String(v)),
    raw,
  );
}

function ValuePill({ value }: { value: number }) {
  const rounded = Math.round(value * 10) / 10;
  const isNeg = rounded < 0;
  const isZero = rounded === 0;
  const color = isZero ? '#A8A29E' : isNeg ? '#D97070' : '#5B8A5E';
  const text = isZero ? '0' : `${rounded > 0 ? '+' : ''}${rounded}`;
  return (
    <span className="tabular-nums font-bold text-sm" style={{ color }}>
      {text}
    </span>
  );
}

function BreakdownRow({
  label,
  value,
  max,
  dim,
  t,
}: {
  label: string;
  value: number;
  max?: number;
  dim?: boolean;
  t?: Translations;
}) {
  const isZero = Math.round(value * 10) / 10 === 0;
  return (
    <div className="flex items-center justify-between py-1.5">
      <span className={`text-sm ${dim || isZero ? 'text-stone-400' : 'text-stone-700'}`}>
        {label}
      </span>
      <div className="flex items-baseline gap-1.5">
        <ValuePill value={value} />
        {max != null && t && (
          <span className="text-stone-400 text-[10px] tabular-nums">
            {t.scoreInfoMaxOf.replace('{n}', String(max))}
          </span>
        )}
      </div>
    </div>
  );
}

export default function ScoreInfoModal({ onClose }: { onClose: () => void }) {
  const t = useT();
  const { profile, dailyLogs, todaySteps, stepGoal } = useAppStore();
  const today = getTodayString();
  const log = dailyLogs[today];

  // Compute breakdown only when profile exists (after onboarding).
  const fields: Array<'sleep' | 'energy' | 'mood'> = ['sleep', 'energy', 'mood'];
  const avgs = fields
    .map((f) => getCheckInAverage(dailyLogs, 7, f))
    .filter((v): v is number => v != null);
  const checkInAvg = avgs.length > 0 ? avgs.reduce((s, v) => s + v, 0) / avgs.length : null;

  const breakdown: ScoreBreakdown | null = profile
    ? getScoreBreakdown(profile, {
        completedTaskIds: log?.completedTasks ?? [],
        checkInAvg,
        streak: calculateStreak(dailyLogs),
        steps: todaySteps,
        stepGoal,
        debtPoints: calculateDailyDebt(log?.badHabits ?? []),
        challengesToday: (log?.challengesCompletedToday ?? []).length,
        urgesResisted: (log?.urges ?? []).filter((u) => u.completedIntervention).length,
      })
    : null;

  const items = [
    { title: t.scoreInfoTasksTitle, desc: t.scoreInfoTasksDesc },
    { title: t.scoreInfoCheckInTitle, desc: t.scoreInfoCheckInDesc },
    { title: t.scoreInfoStreakTitle, desc: t.scoreInfoStreakDesc },
    { title: t.scoreInfoStepsTitle, desc: t.scoreInfoStepsDesc },
    { title: t.scoreInfoChallengeTitle, desc: t.scoreInfoChallengeDesc },
    { title: t.scoreInfoDebtTitle, desc: t.scoreInfoDebtDesc },
  ];

  return (
    <BottomSheet onClose={onClose}>
      <div className="mb-5">
        <h2 className="text-stone-800 font-bold text-lg mb-2">{t.scoreInfoTitle}</h2>
        <p className="text-stone-500 text-sm leading-relaxed">{t.scoreInfoSubtitle}</p>
      </div>

      {breakdown && (
        <>
          <p className="text-stone-400 text-xs uppercase tracking-widest font-semibold mb-3">
            {t.scoreInfoBreakdownHeader}
          </p>

          <div className="bg-stone-50 rounded-2xl p-4 mb-3">
            <div className="flex items-baseline justify-between mb-1">
              <p className="text-stone-800 font-semibold text-sm">{t.scoreInfoBaselineTitle}</p>
              <p className="text-stone-800 font-bold text-lg tabular-nums">
                {breakdown.baseline.clampedTotal}
              </p>
            </div>
            <p className="text-stone-500 text-xs leading-relaxed mb-3">
              {t.scoreInfoBaselineSubtitle}
            </p>

            <div className="divide-y divide-stone-200/60">
              <BreakdownRow label={t.scoreInfoBaselineStart} value={breakdown.baseline.start} />
              {breakdown.baseline.items.map((it) => (
                <BreakdownRow key={it.key} label={formatLabel(t, it)} value={it.value} max={it.max} t={t} />
              ))}
            </div>

            <div className="border-t border-stone-300 mt-2 pt-2 flex items-center justify-between">
              <span className="text-stone-700 text-sm font-semibold">
                {t.scoreInfoBaselineTotal}
                {breakdown.baseline.rawTotal !== breakdown.baseline.clampedTotal && (
                  <span className="text-stone-400 font-normal ml-1">
                    ({breakdown.baseline.rawTotal} →{' '}
                    {t.scoreInfoBaselineRounded.replace(
                      '{n}',
                      String(breakdown.baseline.clampedTotal),
                    )}
                    )
                  </span>
                )}
              </span>
              <span className="text-stone-800 font-bold text-base tabular-nums">
                {breakdown.baseline.clampedTotal}
              </span>
            </div>
          </div>

          <div className="bg-stone-50 rounded-2xl p-4 mb-3">
            <p className="text-stone-800 font-semibold text-sm mb-2">{t.scoreInfoTodayTitle}</p>

            <div className="divide-y divide-stone-200/60">
              {breakdown.today.items.map((it) => (
                <BreakdownRow
                  key={it.key}
                  label={formatLabel(t, it)}
                  value={it.value}
                  max={it.max}
                  t={t}
                />
              ))}
              {breakdown.today.debtItem && (
                <BreakdownRow
                  label={formatLabel(t, breakdown.today.debtItem)}
                  value={breakdown.today.debtItem.value}
                />
              )}
            </div>

            {(() => {
              const headroom = breakdown.today.items.reduce(
                (sum, it) => sum + (it.max != null ? Math.max(0, it.max - it.value) : 0),
                0,
              );
              const stillReachable = Math.max(0, 100 - breakdown.today.final);
              const shown = Math.min(headroom, stillReachable);
              return shown > 0 ? (
                <p className="text-stone-400 text-[11px] mt-2 leading-relaxed">
                  {t.scoreInfoTodayHeadroom.replace('{n}', String(Math.round(shown)))}
                </p>
              ) : null;
            })()}

            <div className="border-t border-stone-300 mt-2 pt-2 flex items-center justify-between">
              <span className="text-stone-700 text-sm font-semibold">{t.scoreInfoTodayFinal}</span>
              <span
                className="font-bold text-lg tabular-nums"
                style={{ color: '#3D6640' }}
              >
                {Math.round(breakdown.today.final)}
              </span>
            </div>
          </div>

          {breakdown.tip && (
            <div className="bg-[#E4A85A]/10 border border-[#E4A85A]/30 rounded-2xl px-4 py-3 mb-5">
              <p className="text-[#8a5a1f] text-xs leading-relaxed">
                {formatTip(t, breakdown.tip)}
              </p>
            </div>
          )}
        </>
      )}

      <p className="text-stone-400 text-xs uppercase tracking-widest font-semibold mb-3">
        {t.scoreInfoIngredientsTitle}
      </p>
      <div className="space-y-2.5 mb-5">
        {items.map((item, i) => (
          <div key={i} className="bg-stone-50 rounded-2xl p-4">
            <p className="text-stone-800 font-semibold text-sm mb-1">{item.title}</p>
            <p className="text-stone-500 text-xs leading-relaxed">{item.desc}</p>
          </div>
        ))}
      </div>

      <div className="bg-[#5B8A5E]/8 border border-[#5B8A5E]/20 rounded-2xl px-4 py-3 mb-5">
        <p className="text-[#3D6640] text-xs leading-relaxed">{t.scoreInfoFooter}</p>
      </div>

      <button
        onClick={onClose}
        className="w-full py-3 rounded-2xl text-white font-bold text-sm active:scale-[0.98] transition-transform"
        style={{ background: 'linear-gradient(135deg, #5B8A5E, #3D6640)' }}
      >
        {t.scoreInfoCloseBtn}
      </button>
    </BottomSheet>
  );
}
