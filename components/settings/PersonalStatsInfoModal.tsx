'use client';

import { useT } from '@/hooks/useT';
import BottomSheet from '@/components/ui/BottomSheet';

function Row({ emoji, title, body }: { emoji: string; title: string; body: string }) {
  return (
    <div className="bg-stone-50 rounded-2xl p-4">
      <div className="flex items-center gap-2 mb-1.5">
        <span className="text-lg" aria-hidden="true">{emoji}</span>
        <p className="font-bold text-sm text-stone-800">{title}</p>
      </div>
      <p className="text-stone-600 text-xs leading-relaxed">{body}</p>
    </div>
  );
}

export default function PersonalStatsInfoModal({ onClose }: { onClose: () => void }) {
  const t = useT();

  const rows = [
    { emoji: '🌑', title: t.profileStatsTitle, body: t.profileStatsInfoStageBody },
    { emoji: '🔥', title: t.profileStatsLongestStreak, body: t.profileStatsInfoStreakBody },
    { emoji: '🏆', title: t.profileStatsHighestScore, body: t.profileStatsInfoScoreBody },
    { emoji: '🧠', title: t.profileStatsUrgesResisted, body: t.profileStatsInfoUrgesBody },
    { emoji: '✅', title: t.profileStatsTasksDone, body: t.profileStatsInfoTasksBody },
    { emoji: '💧', title: t.profileStatsCleanDays, body: t.profileStatsInfoCleanBody },
    { emoji: '🎙️', title: t.profileStatsReflectionMin, body: t.profileStatsInfoReflectionBody },
    { emoji: '📅', title: t.profileStatsBestDayFallback, body: t.profileStatsInfoBestDayBody },
  ];

  return (
    <BottomSheet onClose={onClose}>
      <div className="mb-4">
        <h2 className="text-stone-800 font-bold text-lg mb-2">{t.profileStatsInfoTitle}</h2>
        <p className="text-stone-500 text-sm leading-relaxed">{t.profileStatsInfoIntro}</p>
      </div>

      <div className="space-y-2.5 mb-5">
        {rows.map((r, i) => (
          <Row key={i} emoji={r.emoji} title={r.title} body={r.body} />
        ))}
      </div>

      <button
        onClick={onClose}
        className="w-full py-3 rounded-2xl text-white font-bold text-sm active:scale-[0.98] transition-transform"
        style={{ background: 'linear-gradient(135deg, #5B8A5E, #3D6640)' }}
      >
        {t.profileStatsInfoCloseBtn}
      </button>
    </BottomSheet>
  );
}
