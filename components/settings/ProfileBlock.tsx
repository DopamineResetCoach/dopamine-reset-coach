'use client';

import { useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { getCheckInAverage, getTodayString } from '@/lib/scoring';
import { useT } from '@/hooks/useT';
import type { UserProfile } from '@/types';
import SleepDetailPage from './SleepDetailPage';
import CheckInModal from '@/components/checkin/CheckInModal';
import BottomSheet from '@/components/ui/BottomSheet';

type EditMode = 'screen_time' | 'sleep' | 'energy' | 'habits' | null;

function StarBar({ value }: { value: number }) {
  const rounded = Math.round(value);
  return (
    <span className="text-stone-700 font-medium text-sm tracking-tight">
      {'★'.repeat(rounded)}{'☆'.repeat(5 - rounded)}
    </span>
  );
}

function ScaleSelector({
  emojis,
  value,
  onChange,
}: {
  emojis: string[];
  value: number;
  onChange: (v: number) => void;
}) {
  return (
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
          >
            {e}
          </button>
        );
      })}
    </div>
  );
}

function EditModal({
  title,
  children,
  onSave,
  onClose,
  saveDisabled,
}: {
  title: string;
  children: React.ReactNode;
  onSave: () => void;
  onClose: () => void;
  saveDisabled?: boolean;
}) {
  const t = useT();
  return (
    <BottomSheet onClose={onClose}>
      <h3 className="text-stone-800 font-bold text-base mb-4 text-center">{title}</h3>
      <div className="mb-5">{children}</div>
      <div className="flex gap-3">
        <button
          onClick={onClose}
          className="flex-1 py-3 rounded-2xl border-2 border-stone-200 text-stone-500 font-semibold text-sm"
        >
          {t.profileEditCancel}
        </button>
        <button
          onClick={onSave}
          disabled={saveDisabled}
          className="flex-[2] py-3 rounded-2xl text-white font-semibold text-sm disabled:opacity-50"
          style={{ background: 'linear-gradient(135deg, #5B8A5E, #3D6640)' }}
        >
          {t.profileEditSave}
        </button>
      </div>
    </BottomSheet>
  );
}

function Row({
  label,
  value,
  badge,
  onClick,
  chevron,
}: {
  label: string;
  value: React.ReactNode;
  badge?: string;
  onClick?: () => void;
  chevron?: boolean;
}) {
  const Comp = onClick ? 'button' : 'div';
  return (
    <Comp
      onClick={onClick}
      className={`w-full flex items-center justify-between py-2.5 ${
        onClick ? 'active:scale-[0.99] transition-transform text-left' : ''
      }`}
    >
      <div className="flex flex-col items-start">
        <span className="text-stone-400 text-sm">{label}</span>
        {badge && (
          <span className="text-[10px] text-[#5B8A5E] font-semibold mt-0.5">{badge}</span>
        )}
      </div>
      <div className="flex items-center gap-1.5">
        {value}
        {chevron && (
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9 18l6-6-6-6" stroke="#9CA3AF" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
        )}
      </div>
    </Comp>
  );
}

const SLEEP_EMOJIS = ['😵', '😴', '🛌', '💤', '✨'];
const ENERGY_EMOJIS = ['🪫', '😩', '😐', '⚡', '🚀'];

const HABIT_LABELS_KEY: { key: keyof UserProfile['habits']; tKey: 'habitSocialMedia' | 'habitCaffeine' | 'habitJunkFood' | 'habitAlcohol' | 'habitAdultContent' | 'habitGaming' }[] = [
  { key: 'socialMedia', tKey: 'habitSocialMedia' },
  { key: 'caffeine', tKey: 'habitCaffeine' },
  { key: 'junkFood', tKey: 'habitJunkFood' },
  { key: 'alcohol', tKey: 'habitAlcohol' },
  { key: 'porn', tKey: 'habitAdultContent' },
  { key: 'gaming', tKey: 'habitGaming' },
];

export default function ProfileBlock() {
  const { profile, dailyLogs, updateProfileField } = useAppStore();
  const checkInPromptDisabled = useAppStore((s) => s.checkInPromptDisabled);
  const setCheckInPromptDisabled = useAppStore((s) => s.setCheckInPromptDisabled);
  const t = useT();
  const [edit, setEdit] = useState<EditMode>(null);
  const [sleepDetailOpen, setSleepDetailOpen] = useState(false);
  const [checkInOpen, setCheckInOpen] = useState(false);

  // Edit state buffers
  const [screenTime, setScreenTime] = useState(profile?.screenTimeHours ?? 3);
  const [sleepEdit, setSleepEdit] = useState(profile?.sleepQuality ?? 3);
  const [energyEdit, setEnergyEdit] = useState(profile?.energyLevel ?? 3);
  const [habitsEdit, setHabitsEdit] = useState<UserProfile['habits']>(
    profile?.habits ?? { socialMedia: false, caffeine: false, junkFood: false, alcohol: false, porn: false, gaming: false }
  );

  if (!profile) return null;

  const sleepAvg = getCheckInAverage(dailyLogs, 7, 'sleep');
  const energyAvg = getCheckInAverage(dailyLogs, 7, 'energy');
  const moodAvg = getCheckInAverage(dailyLogs, 7, 'mood');

  const sleepShown = sleepAvg ?? profile.sleepQuality;
  const energyShown = energyAvg ?? profile.energyLevel;
  const habitsCount = Object.values(profile.habits).filter(Boolean).length;

  const todayCheckIn = dailyLogs[getTodayString()]?.checkIn;
  const SLEEP_E = ['😵', '😴', '🛌', '💤', '✨'];
  const ENERGY_E = ['🪫', '😩', '😐', '⚡', '🚀'];
  const MOOD_E = ['😞', '😟', '😐', '🙂', '😊'];

  const openEdit = (mode: EditMode) => {
    if (mode === 'screen_time') setScreenTime(profile.screenTimeHours);
    if (mode === 'sleep') setSleepEdit(Math.round(sleepShown));
    if (mode === 'energy') setEnergyEdit(Math.round(energyShown));
    if (mode === 'habits') setHabitsEdit(profile.habits);
    setEdit(mode);
  };

  return (
    <>
      <div className="bg-white rounded-2xl p-5 mb-4 shadow-sm">
        <h3 className="text-stone-700 font-bold text-sm mb-2">{t.settingsProfileTitle}</h3>
        <div className="divide-y divide-stone-100">
          <Row
            label={t.settingsScreenTime}
            value={
              <span className="text-stone-700 font-medium text-sm">
                ~{profile.screenTimeHours} {t.profileScreenTimeUnit}
              </span>
            }
            onClick={() => openEdit('screen_time')}
            chevron
          />
          <Row
            label={t.settingsSleepQuality}
            value={<StarBar value={sleepShown} />}
            badge={sleepAvg !== null ? t.profileSinceCheckIns : undefined}
            onClick={() => setSleepDetailOpen(true)}
            chevron
          />
          <Row
            label={t.settingsEnergyLevel}
            value={<StarBar value={energyShown} />}
            badge={energyAvg !== null ? t.profileSinceCheckIns : undefined}
            onClick={() => openEdit('energy')}
            chevron
          />
          <Row
            label={t.profileMood}
            value={
              moodAvg !== null ? (
                <StarBar value={moodAvg} />
              ) : (
                <span className="text-stone-400 text-xs italic">{t.profileNoCheckInData}</span>
              )
            }
            badge={moodAvg !== null ? t.profileSinceCheckIns : undefined}
          />
          <Row
            label={t.settingsHabitsTargeted}
            value={
              <span className="text-stone-700 font-medium text-sm">
                {habitsCount} {t.settingsHabitsUnit}
              </span>
            }
            onClick={() => openEdit('habits')}
            chevron
          />
          <Row
            label={t.profileTodayCheckIn}
            value={
              checkInPromptDisabled ? (
                <span className="text-stone-400 text-xs italic">{t.checkInDisabledStatus}</span>
              ) : todayCheckIn ? (
                <span className="text-base tracking-tight">
                  {SLEEP_E[todayCheckIn.sleep - 1]}
                  {ENERGY_E[todayCheckIn.energy - 1]}
                  {MOOD_E[todayCheckIn.mood - 1]}
                </span>
              ) : (
                <span className="text-stone-400 text-xs italic">{t.profileNoCheckInData}</span>
              )
            }
            badge={
              checkInPromptDisabled
                ? t.checkInReenable
                : todayCheckIn
                  ? t.profileTodayCheckInEdit
                  : undefined
            }
            onClick={() => {
              if (checkInPromptDisabled) {
                setCheckInPromptDisabled(false);
              }
              setCheckInOpen(true);
            }}
            chevron
          />
        </div>
      </div>

      {edit === 'screen_time' && (
        <EditModal
          title={t.profileEditScreenTimeTitle}
          onClose={() => setEdit(null)}
          onSave={() => {
            updateProfileField({ screenTimeHours: Math.max(0, Math.min(24, screenTime)) });
            setEdit(null);
          }}
        >
          <div className="flex items-center gap-3 justify-center">
            <input
              type="number"
              min={0}
              max={24}
              step={0.5}
              value={screenTime}
              onChange={(e) => setScreenTime(Number(e.target.value))}
              className="w-24 text-center text-2xl font-bold text-stone-800 bg-stone-50 rounded-2xl py-3 outline-none"
            />
            <span className="text-stone-500 text-sm">{t.profileScreenTimeUnit}</span>
          </div>
        </EditModal>
      )}

      {edit === 'sleep' && (
        <EditModal
          title={t.profileEditSleepTitle}
          onClose={() => setEdit(null)}
          onSave={() => {
            updateProfileField({ sleepQuality: sleepEdit });
            setEdit(null);
          }}
        >
          <ScaleSelector emojis={SLEEP_EMOJIS} value={sleepEdit} onChange={setSleepEdit} />
        </EditModal>
      )}

      {edit === 'energy' && (
        <EditModal
          title={t.profileEditEnergyTitle}
          onClose={() => setEdit(null)}
          onSave={() => {
            updateProfileField({ energyLevel: energyEdit });
            setEdit(null);
          }}
        >
          <ScaleSelector emojis={ENERGY_EMOJIS} value={energyEdit} onChange={setEnergyEdit} />
        </EditModal>
      )}

      {edit === 'habits' && (
        <EditModal
          title={t.profileEditHabitsTitle}
          onClose={() => setEdit(null)}
          onSave={() => {
            updateProfileField({ habits: habitsEdit });
            setEdit(null);
          }}
        >
          <div className="space-y-2">
            {HABIT_LABELS_KEY.map(({ key, tKey }) => (
              <button
                key={key}
                onClick={() => setHabitsEdit((h) => ({ ...h, [key]: !h[key] }))}
                className={`w-full flex items-center justify-between px-4 py-3 rounded-2xl transition-all ${
                  habitsEdit[key]
                    ? 'bg-[#5B8A5E]/10 ring-2 ring-[#5B8A5E]'
                    : 'bg-stone-50 ring-1 ring-stone-100'
                }`}
              >
                <span className="text-stone-700 text-sm font-medium">{t[tKey]}</span>
                <span
                  className={`w-5 h-5 rounded-full border-2 flex items-center justify-center ${
                    habitsEdit[key] ? 'bg-[#5B8A5E] border-[#5B8A5E]' : 'border-stone-300 bg-white'
                  }`}
                >
                  {habitsEdit[key] && (
                    <svg width="10" height="8" viewBox="0 0 12 10" fill="none">
                      <path d="M1 5l4 4 6-8" stroke="white" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                  )}
                </span>
              </button>
            ))}
          </div>
        </EditModal>
      )}

      {sleepDetailOpen && <SleepDetailPage onClose={() => setSleepDetailOpen(false)} />}

      {checkInOpen && (
        <CheckInModal
          onClose={() => setCheckInOpen(false)}
          initialValues={
            todayCheckIn
              ? {
                  sleep: todayCheckIn.sleep,
                  energy: todayCheckIn.energy,
                  mood: todayCheckIn.mood,
                  note: todayCheckIn.note,
                }
              : undefined
          }
        />
      )}
    </>
  );
}
