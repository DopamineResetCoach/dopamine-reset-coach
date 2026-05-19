'use client';

import { useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { useT } from '@/hooks/useT';
import BottomSheet from '@/components/ui/BottomSheet';
import VoiceRecorder from './VoiceRecorder';

const SLEEP_EMOJIS = ['😵', '😴', '🛌', '💤', '✨'];
const ENERGY_EMOJIS = ['🪫', '😩', '😐', '⚡', '🚀'];
const MOOD_EMOJIS = ['😞', '😟', '😐', '🙂', '😊'];

function ScaleRow({
  question,
  emojis,
  value,
  onChange,
  lowLabel,
  highLabel,
  levelDescriptions,
}: {
  question: string;
  emojis: string[];
  value: number;
  onChange: (v: number) => void;
  lowLabel: string;
  highLabel: string;
  levelDescriptions?: [string, string, string, string, string];
}) {
  const desc = levelDescriptions ? levelDescriptions[Math.max(1, Math.min(5, value)) - 1] : null;
  return (
    <div className="mb-5">
      <p className="text-stone-700 font-semibold text-sm mb-2.5">{question}</p>
      <div className="flex gap-2">
        {emojis.map((e, i) => {
          const v = i + 1;
          const selected = value === v;
          return (
            <button
              key={v}
              onClick={() => onChange(v)}
              className={`flex-1 aspect-square rounded-2xl flex items-center justify-center text-2xl transition-all active:scale-95 ${
                selected
                  ? 'bg-[#5B8A5E]/10 ring-2 ring-[#5B8A5E]'
                  : 'bg-stone-50 ring-1 ring-stone-100'
              }`}
              aria-label={`${v}`}
            >
              {e}
            </button>
          );
        })}
      </div>
      <div className="flex justify-between mt-1.5 text-[10px] text-stone-400 px-1">
        <span>{lowLabel}</span>
        <span>{highLabel}</span>
      </div>
      {desc && (
        <p className="text-stone-500 text-xs mt-2 leading-snug min-h-[1.5em] px-1">
          <span className="font-semibold text-stone-700">{value} —</span> {desc}
        </p>
      )}
    </div>
  );
}

type Initial = { sleep: number; energy: number; mood: number; note?: string; voiceNote?: string; voiceNoteDurationMs?: number };

export default function CheckInModal({
  onClose,
  initialValues,
}: {
  onClose: () => void;
  initialValues?: Initial;
}) {
  const saveCheckIn = useAppStore((s) => s.saveCheckIn);
  const t = useT();
  const [sleep, setSleep] = useState(initialValues?.sleep ?? 3);
  const [energy, setEnergy] = useState(initialValues?.energy ?? 3);
  const [mood, setMood] = useState(initialValues?.mood ?? 3);
  const [note, setNote] = useState(initialValues?.note ?? '');
  const [voice, setVoice] = useState<{ dataUri: string; durationMs: number } | null>(
    initialValues?.voiceNote && initialValues.voiceNoteDurationMs
      ? { dataUri: initialValues.voiceNote, durationMs: initialValues.voiceNoteDurationMs }
      : null,
  );

  const handleSave = () => {
    saveCheckIn({
      sleep,
      energy,
      mood,
      note: note.trim() || undefined,
      voiceNote: voice?.dataUri,
      voiceNoteDurationMs: voice?.durationMs,
    });
    onClose();
  };

  return (
    <BottomSheet onClose={onClose}>
      <div className="text-center mb-6">
        <h2 className="text-xl font-bold text-stone-800">{t.checkInModalTitle}</h2>
        <p className="text-stone-400 text-sm mt-1">{t.checkInModalSubtitle}</p>
      </div>

      <ScaleRow
        question={t.checkInSleepQ}
        emojis={SLEEP_EMOJIS}
        value={sleep}
        onChange={setSleep}
        lowLabel={t.checkInScaleLow}
        highLabel={t.checkInScaleHigh}
        levelDescriptions={[t.sleepLevel1, t.sleepLevel2, t.sleepLevel3, t.sleepLevel4, t.sleepLevel5]}
      />
      <ScaleRow
        question={t.checkInEnergyQ}
        emojis={ENERGY_EMOJIS}
        value={energy}
        onChange={setEnergy}
        lowLabel={t.checkInScaleLow}
        highLabel={t.checkInScaleHigh}
        levelDescriptions={[t.energyLevel1, t.energyLevel2, t.energyLevel3, t.energyLevel4, t.energyLevel5]}
      />
      <ScaleRow
        question={t.checkInMoodQ}
        emojis={MOOD_EMOJIS}
        value={mood}
        onChange={setMood}
        lowLabel={t.checkInScaleLow}
        highLabel={t.checkInScaleHigh}
        levelDescriptions={[t.moodLevel1, t.moodLevel2, t.moodLevel3, t.moodLevel4, t.moodLevel5]}
      />

      <textarea
        value={note}
        onChange={(e) => setNote(e.target.value)}
        placeholder={t.checkInNotePlaceholder}
        maxLength={280}
        rows={2}
        className="w-full bg-stone-50 rounded-2xl px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 outline-none resize-none mb-5"
      />

      <VoiceRecorder value={voice} onChange={setVoice} />

      <button
        onClick={handleSave}
        className="w-full py-4 rounded-2xl text-white font-bold text-sm active:scale-[0.98] transition-transform"
        style={{ background: 'linear-gradient(135deg, #5B8A5E, #3D6640)' }}
      >
        {t.checkInSaveBtn}
      </button>
    </BottomSheet>
  );
}
