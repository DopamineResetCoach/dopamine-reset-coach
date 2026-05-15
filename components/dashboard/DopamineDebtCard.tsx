'use client';

import { useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { getTodayString, calculateDailyDebt, getDebtLabel, getDebtColor, BAD_HABIT_DEBT, formatDebtDisplay } from '@/lib/scoring';
import PremiumModal from '@/components/premium/PremiumModal';
import BottomSheet from '@/components/ui/BottomSheet';
import { useT } from '@/hooks/useT';
import type { BadHabit } from '@/types';

const BAD_HABIT_EMOJIS: Record<BadHabit['type'], string> = {
  scrolling: '📱',
  porn: '🔞',
  junk_food: '🍔',
  sugar: '🍭',
  gaming: '🎮',
  alcohol: '🍺',
  caffeine: '☕',
  other: '⚡',
};

function LogSheet({ onClose }: { onClose: () => void }) {
  const { logBadHabit } = useAppStore();
  const t = useT();

  const BAD_HABIT_OPTIONS: { type: BadHabit['type']; label: string; emoji: string }[] = [
    { type: 'scrolling', label: t.habitScrolling, emoji: '📱' },
    { type: 'porn', label: t.habitAdultContentShort, emoji: '🔞' },
    { type: 'junk_food', label: t.habitJunkFoodShort, emoji: '🍔' },
    { type: 'sugar', label: t.habitSugar, emoji: '🍭' },
    { type: 'gaming', label: t.habitGamingShort, emoji: '🎮' },
    { type: 'alcohol', label: t.habitAlcoholShort, emoji: '🍺' },
    { type: 'caffeine', label: t.habitCaffeineShort, emoji: '☕' },
    { type: 'other', label: t.habitOther, emoji: '⚡' },
  ];

  const handleLog = (type: BadHabit['type']) => {
    logBadHabit({ type, debtPoints: BAD_HABIT_DEBT[type] });
    onClose();
  };

  return (
    <BottomSheet onClose={onClose} paddingBottom="pb-10">
      <p className="text-stone-500 text-xs font-semibold uppercase tracking-widest mb-1">
        {t.debtSheetTitle}
      </p>
      <p className="text-stone-400 text-sm mb-4">{t.debtSheetSubtitle}</p>
      <div className="grid grid-cols-4 gap-2">
        {BAD_HABIT_OPTIONS.map((opt) => (
          <button
            key={opt.type}
            onClick={() => handleLog(opt.type)}
            className="flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-stone-50 active:bg-stone-100"
          >
            <span className="text-2xl">{opt.emoji}</span>
            <span className="text-[10px] text-stone-500 font-medium">{opt.label}</span>
            <span className="text-[9px] text-red-400 font-bold">+{BAD_HABIT_DEBT[opt.type]}</span>
          </button>
        ))}
      </div>
    </BottomSheet>
  );
}

export default function DopamineDebtCard() {
  const { dailyLogs, isPremium } = useAppStore();
  const t = useT();
  const [showLog, setShowLog] = useState(false);
  const [showPremium, setShowPremium] = useState(false);
  const [infoOpen, setInfoOpen] = useState(false);
  const today = getTodayString();
  const todayLog = dailyLogs[today];
  const badHabits = todayLog?.badHabits ?? [];
  const debt = calculateDailyDebt(badHabits);
  const label = t[getDebtLabel(debt)];
  const color = getDebtColor(debt);

  const HABIT_LABELS: Record<BadHabit['type'], string> = {
    scrolling: t.habitScrolling,
    porn: t.habitAdultContentShort,
    junk_food: t.habitJunkFoodShort,
    sugar: t.habitSugar,
    gaming: t.habitGamingShort,
    alcohol: t.habitAlcoholShort,
    caffeine: t.habitCaffeineShort,
    other: t.habitOther,
  };

  if (!isPremium) {
    return (
      <>
        <div
          className="relative bg-white rounded-3xl p-5 mb-4 shadow-sm overflow-hidden cursor-pointer"
          onClick={() => setShowPremium(true)}
        >
          <div className="blur-sm pointer-events-none select-none">
            <div className="flex items-center justify-between mb-3">
              <div>
                <p className="text-stone-400 text-xs font-medium uppercase tracking-widest">
                  {t.debtLabel}
                </p>
                <p className="text-2xl font-bold text-red-400 mt-0.5">{t.debtHigh}</p>
              </div>
              <div className="text-right">
                <p className="text-4xl font-bold text-red-400">42</p>
                <p className="text-stone-400 text-sm">{t.debtPtsToday}</p>
              </div>
            </div>
            <div className="grid grid-cols-3 gap-2">
              {['📱 Scrolling', '🍔 Junk', '🎮 Gaming'].map((h) => (
                <div key={h} className="bg-red-50 rounded-xl px-2 py-2 text-center">
                  <p className="text-sm text-stone-500">{h}</p>
                </div>
              ))}
            </div>
          </div>
          <div className="absolute inset-0 flex flex-col items-center justify-center bg-white/70 rounded-3xl">
            <span className="text-3xl mb-2">🔒</span>
            <p className="text-stone-700 font-bold text-sm">{t.premiumFeature1.split(' — ')[0]}</p>
            <p className="text-stone-400 text-sm mt-1 text-center px-4">
              {t.settingsPremiumFreeDesc}
            </p>
            <div
              className="mt-3 px-5 py-2 rounded-xl text-white text-sm font-bold"
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
    <>
      <div className="bg-white rounded-3xl p-5 mb-4 shadow-sm">
        <div className="flex items-center justify-between mb-3">
          <div>
            <div className="flex items-center gap-1.5">
              <p className="text-stone-400 text-xs font-medium uppercase tracking-widest">
                {t.debtLabel}
              </p>
              <button
                onClick={() => setInfoOpen((v) => !v)}
                className="w-4 h-4 rounded-full flex items-center justify-center text-stone-300 active:text-stone-500 transition-colors"
                aria-label={t.debtInfoBody}
                aria-expanded={infoOpen}
              >
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                  <circle cx="8" cy="8" r="7" stroke="currentColor" strokeWidth="1.4" />
                  <path d="M8 11.5v-3.8M8 5.5v.4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
                </svg>
              </button>
            </div>
            <p className="text-2xl font-bold mt-0.5" style={{ color }}>
              {label}
            </p>
          </div>
          <div className="text-right">
            <p className="text-4xl font-bold tabular-nums" style={{ color }}>
              {formatDebtDisplay(debt)}
            </p>
            <p className="text-stone-400 text-sm">{t.debtPtsToday}</p>
          </div>
        </div>

        {infoOpen && (
          <div className="bg-stone-50 rounded-xl p-3 mb-3 text-stone-600 text-[11px] leading-relaxed">
            {t.debtInfoBody}
          </div>
        )}

        {badHabits.length > 0 && (
          <div className="flex flex-wrap gap-2 mb-3">
            {badHabits.map((h) => (
              <div
                key={h.id}
                className="flex items-center gap-1 bg-red-50 rounded-xl px-2.5 py-1.5"
              >
                <span className="text-sm">{BAD_HABIT_EMOJIS[h.type]}</span>
                <span className="text-sm text-stone-500 font-medium">{HABIT_LABELS[h.type]}</span>
                <span className="text-[10px] text-red-400 font-bold">+{h.debtPoints}</span>
              </div>
            ))}
          </div>
        )}

        {debt === 0 && (
          <p className="text-stone-400 text-sm mb-3">{t.debtNoDebt}</p>
        )}

        <button
          onClick={() => setShowLog(true)}
          className="w-full py-2.5 rounded-2xl text-sm font-semibold border-2 border-stone-100 text-stone-500 active:bg-stone-50"
        >
          {t.debtLogBtn}
        </button>
      </div>

      {showLog && <LogSheet onClose={() => setShowLog(false)} />}
    </>
  );
}
