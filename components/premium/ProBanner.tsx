'use client';

import { useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { useT } from '@/hooks/useT';
import PremiumModal from './PremiumModal';

export default function ProBanner() {
  const isPremium = useAppStore((s) => s.isPremium);
  const t = useT();
  const [showModal, setShowModal] = useState(false);

  if (isPremium) return null;

  return (
    <>
      <button
        onClick={() => setShowModal(true)}
        className="w-full mb-4 rounded-2xl px-4 py-3 flex items-center justify-between"
        style={{
          background: 'linear-gradient(135deg, #3D6640, #5B8A5E)',
        }}
      >
        <div className="flex items-center gap-3">
          <span className="text-2xl">🧠</span>
          <div className="text-left">
            <p className="text-white font-bold text-sm leading-tight">{t.premiumTitle}</p>
            <p className="text-white/70 text-xs mt-0.5">{t.premiumBtn}</p>
          </div>
        </div>
        <div className="bg-white/20 rounded-xl px-3 py-1.5">
          <p className="text-white text-xs font-semibold">Unlock</p>
        </div>
      </button>

      {showModal && <PremiumModal onClose={() => setShowModal(false)} />}
    </>
  );
}
