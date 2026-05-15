'use client';

import { useT } from '@/hooks/useT';
import { getStageMeta, type StageId } from '@/lib/stages';
import type { Translations } from '@/lib/i18n/types';

interface Props {
  stageId: StageId;
  onClose: () => void;
}

function applyParams(template: string, params: Record<string, string | number>): string {
  let out = template;
  for (const [key, value] of Object.entries(params)) {
    out = out.replaceAll(`{${key}}`, String(value));
  }
  return out;
}

function pick<K extends keyof Translations>(t: Translations, key: K): string {
  return t[key] as string;
}

export default function StageTransitionModal({ stageId, onClose }: Props) {
  const t = useT();
  const meta = getStageMeta(stageId);
  const name = pick(t, meta.nameKey);
  const desc = pick(t, meta.descKey);
  const science = pick(t, meta.scienceKey);

  return (
    <div
      className="fixed inset-0 z-[60] flex items-center justify-center px-5"
      style={{
        background:
          'radial-gradient(circle at 50% 30%, rgba(91,138,94,0.96), rgba(48,72,52,0.98))',
        animation: 'backdrop-in 0.25s ease',
      }}
    >
      <div
        className="w-full max-w-sm rounded-3xl bg-white p-6 shadow-2xl"
        style={{ animation: 'fade-in 0.4s ease-out' }}
      >
        <div className="welcome-fade-1 text-center mb-4">
          <p className="text-[#5B8A5E] text-[10px] font-bold uppercase tracking-[0.15em]">
            {t.stageTransitionEyebrow}
          </p>
        </div>

        <div className="welcome-fade-2 flex justify-center mb-4">
          <div
            className="welcome-glow w-24 h-24 rounded-3xl flex items-center justify-center text-5xl"
            style={{
              background:
                'linear-gradient(135deg, rgba(91,138,94,0.15), rgba(91,138,94,0.05))',
            }}
          >
            {meta.emoji}
          </div>
        </div>

        <div className="welcome-fade-2 text-center mb-1">
          <h2 className="text-stone-800 font-bold text-2xl leading-tight">
            {applyParams(t.stageTransitionTitle, { stage: name })}
          </h2>
        </div>
        <div className="welcome-fade-3 text-center mb-5">
          <p className="text-stone-500 text-sm leading-relaxed">{desc}</p>
        </div>

        <div
          className="welcome-fade-4 rounded-2xl p-4 mb-5"
          style={{ background: 'rgba(91,138,94,0.07)' }}
        >
          <p className="text-[#3D6640] text-[10px] font-bold uppercase tracking-widest mb-2">
            {t.stageTransitionWhatChanges}
          </p>
          <p className="text-stone-700 text-sm leading-relaxed">{science}</p>
        </div>

        <button
          onClick={onClose}
          className="welcome-fade-5 w-full py-3.5 rounded-2xl text-white font-bold text-sm active:scale-[0.98] transition-transform"
          style={{ background: 'linear-gradient(135deg, #5B8A5E, #3D6640)' }}
        >
          {t.stageTransitionContinue}
        </button>
      </div>
    </div>
  );
}
