'use client';

import { useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { getTodayString } from '@/lib/scoring';
import { useT } from '@/hooks/useT';
import CheckInModal from './CheckInModal';
import BottomSheet from '@/components/ui/BottomSheet';

export default function CheckInCard() {
  const dailyLogs = useAppStore((s) => s.dailyLogs);
  const dismissCheckInCard = useAppStore((s) => s.dismissCheckInCard);
  const checkInPromptDisabled = useAppStore((s) => s.checkInPromptDisabled);
  const setCheckInPromptDisabled = useAppStore((s) => s.setCheckInPromptDisabled);
  const t = useT();
  const [open, setOpen] = useState(false);
  const [confirmDismiss, setConfirmDismiss] = useState(false);
  const today = getTodayString();
  const checkIn = dailyLogs[today]?.checkIn;

  if (checkInPromptDisabled) {
    return null;
  }

  if (checkIn?.dismissed) {
    return null;
  }

  if (checkIn) {
    return (
      <div className="bg-white rounded-2xl p-4 mb-4 shadow-sm border border-stone-100">
        <div className="flex items-start gap-3">
          <span className="text-2xl">✅</span>
          <div className="flex-1">
            <p className="text-stone-800 font-bold text-sm">{t.checkInCardDoneTitle}</p>
            <p className="text-stone-400 text-xs mt-0.5 leading-snug">
              {t.checkInCardDoneSubtitle}
            </p>
          </div>
          <button
            onClick={dismissCheckInCard}
            className="flex-shrink-0 w-7 h-7 -mt-1 -mr-1 flex items-center justify-center rounded-full text-stone-400 active:bg-stone-100 transition-colors"
            aria-label={t.ariaClose}
          >
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
              <path d="M2 2l10 10M12 2L2 12" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round"/>
            </svg>
          </button>
        </div>
      </div>
    );
  }

  return (
    <>
      <div className="relative bg-white rounded-2xl mb-4 shadow-sm border border-stone-100">
        <button
          onClick={() => setOpen(true)}
          className="w-full p-4 text-left active:scale-[0.99] transition-transform"
        >
          <div className="flex items-center gap-3 pr-6">
            <span className="text-2xl">📝</span>
            <div className="flex-1 min-w-0">
              <p className="text-stone-800 font-bold text-sm">{t.checkInCardTitle}</p>
              <p className="text-stone-400 text-xs mt-0.5">{t.checkInCardSubtitle}</p>
            </div>
            <span
              className="text-xs font-bold px-3 py-1.5 rounded-full text-white flex-shrink-0"
              style={{ background: '#5B8A5E' }}
            >
              {t.checkInCardCta}
            </span>
          </div>
        </button>
        <button
          onClick={() => setConfirmDismiss(true)}
          className="absolute top-1 right-1 w-7 h-7 flex items-center justify-center rounded-full text-stone-300 active:text-stone-500 active:bg-stone-100 transition-colors z-10"
          aria-label={t.checkInDismissAria}
        >
          <svg width="11" height="11" viewBox="0 0 14 14" fill="none">
            <path d="M2 2l10 10M12 2L2 12" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round"/>
          </svg>
        </button>
      </div>
      {open && <CheckInModal onClose={() => setOpen(false)} />}
      {confirmDismiss && (
        <BottomSheet onClose={() => setConfirmDismiss(false)}>
          <h2 className="text-stone-800 font-bold text-lg mb-2">
            {t.checkInDisableConfirmTitle}
          </h2>
          <p className="text-stone-500 text-sm leading-relaxed mb-5">
            {t.checkInDisableConfirmBody}
          </p>
          <button
            onClick={() => {
              setCheckInPromptDisabled(true);
              setConfirmDismiss(false);
            }}
            className="w-full py-3 rounded-2xl text-white font-bold text-sm mb-2 active:scale-[0.98] transition-transform"
            style={{ background: '#C97B5B' }}
          >
            {t.checkInDisableConfirmYes}
          </button>
          <button
            onClick={() => setConfirmDismiss(false)}
            className="w-full py-3 rounded-2xl text-stone-600 font-bold text-sm bg-stone-100 active:scale-[0.98] transition-transform"
          >
            {t.checkInDisableConfirmCancel}
          </button>
        </BottomSheet>
      )}
    </>
  );
}
