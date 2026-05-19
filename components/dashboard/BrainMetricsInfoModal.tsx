'use client';

import { useT } from '@/hooks/useT';
import BottomSheet from '@/components/ui/BottomSheet';

export default function BrainMetricsInfoModal({ onClose }: { onClose: () => void }) {
  const t = useT();

  const metrics = [
    { emoji: '🧠', title: t.brainMetricFocus, body: t.brainInfoFocusBody, color: '#5B8A5E' },
    { emoji: '⚡', title: t.brainMetricImpulse, body: t.brainInfoImpulseBody, color: '#C97B5B' },
    { emoji: '🌿', title: t.brainMetricRecovery, body: t.brainInfoRecoveryBody, color: '#6B7FD4' },
  ];

  return (
    <BottomSheet onClose={onClose}>
      <div className="mb-5">
          <h2 className="text-stone-800 font-bold text-lg mb-2">{t.brainInfoTitle}</h2>
          <p className="text-stone-500 text-sm leading-relaxed">{t.brainInfoSubtitle}</p>
        </div>

        <div className="bg-stone-50 rounded-2xl p-4 mb-3">
          <p className="font-bold text-sm text-stone-800 mb-1.5">{t.brainInfoHeadlineTitle}</p>
          <p className="text-stone-600 text-xs leading-relaxed">{t.brainInfoHeadlineBody}</p>
        </div>

        <div className="space-y-2.5 mb-5">
          {metrics.map((m, i) => (
            <div key={i} className="bg-stone-50 rounded-2xl p-4">
              <div className="flex items-center gap-2 mb-1.5">
                <span className="text-lg">{m.emoji}</span>
                <p className="font-bold text-sm" style={{ color: m.color }}>
                  {m.title}
                </p>
              </div>
              <p className="text-stone-600 text-xs leading-relaxed">{m.body}</p>
            </div>
          ))}
        </div>

        <div className="bg-stone-50 rounded-2xl p-4 mb-5">
          <p className="text-stone-800 font-semibold text-sm mb-1.5">{t.brainInfoLevelsTitle}</p>
          <p className="text-stone-600 text-xs leading-relaxed">{t.brainInfoLevelsBody}</p>
        </div>

        <div className="bg-[#5B8A5E]/8 border border-[#5B8A5E]/20 rounded-2xl px-4 py-3 mb-5">
          <p className="text-[#3D6640] text-xs leading-relaxed">{t.brainInfoFooter}</p>
        </div>

        <button
          onClick={onClose}
          className="w-full py-3 rounded-2xl text-white font-bold text-sm active:scale-[0.98] transition-transform"
          style={{ background: 'linear-gradient(135deg, #5B8A5E, #3D6640)' }}
        >
          {t.brainInfoCloseBtn}
        </button>
    </BottomSheet>
  );
}
