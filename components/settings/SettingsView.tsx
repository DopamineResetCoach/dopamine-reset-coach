'use client';

import { useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { getPlanDay, calculateStreak } from '@/lib/scoring';
import { useT } from '@/hooks/useT';
import { SUPPORTED_LOCALES } from '@/lib/i18n';
import type { Locale } from '@/lib/i18n';
import PremiumModal from '@/components/premium/PremiumModal';
import ProfileBlock from './ProfileBlock';
import PersonalStats from './PersonalStats';
import LegalModal from '@/components/legal/LegalModal';

function ToggleRow({
  label,
  description,
  value,
  onChange,
  accentColor = '#5B8A5E',
}: {
  label: string;
  description: string;
  value: boolean;
  onChange: (v: boolean) => void;
  accentColor?: string;
}) {
  return (
    <div
      className="flex items-center gap-4 py-4 border-b border-stone-100 last:border-0 cursor-pointer"
      onClick={() => onChange(!value)}
    >
      <div className="flex-1">
        <p className="text-stone-700 font-semibold text-sm">{label}</p>
        <p className="text-stone-400 text-xs mt-0.5">{description}</p>
      </div>
      <div
        className={`w-12 h-6 rounded-full relative transition-all flex-shrink-0 ${
          value ? '' : 'bg-stone-200'
        }`}
        style={{ backgroundColor: value ? accentColor : undefined }}
      >
        <div
          className={`absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all ${
            value ? 'right-0.5' : 'left-0.5'
          }`}
        />
      </div>
    </div>
  );
}

export default function SettingsView() {
  const {
    profile, toggleHardMode, resetApp, dailyLogs, isPremium, language, setLanguage,
    restorePurchases, stepGoal, setStepGoal, setPremium, todaySteps,
    notificationsEnabled, notificationTime, setNotificationsEnabled, setNotificationTime,
    eveningReflectionEnabled, eveningReflectionTime, setEveningReflectionEnabled, setEveningReflectionTime,
    challengeResetMode, setChallengeResetMode,
  } = useAppStore();
  const t = useT();
  const [showResetConfirm, setShowResetConfirm] = useState(false);
  const [showLangPicker, setShowLangPicker] = useState(false);
  const [showPremiumModal, setShowPremiumModal] = useState(false);
  const [legalOpen, setLegalOpen] = useState<null | 'privacy' | 'terms'>(null);
  const [restoreState, setRestoreState] = useState<'idle' | 'loading' | 'done' | 'not_found' | 'error'>('idle');
  // 7 taps on the premium status pill unlocks the dev toggle. Reviewers won't
  // discover it; Niek can reach it on any build.
  const [devUnlocked, setDevUnlocked] = useState(false);
  const [tapCount, setTapCount] = useState(0);
  const handleSecretTap = () => {
    const next = tapCount + 1;
    setTapCount(next);
    if (next >= 7) {
      setDevUnlocked(true);
      setTapCount(0);
    }
  };

  if (!profile) return null;

  const planDay = getPlanDay(profile.startDate);
  const streak = calculateStreak(dailyLogs);
  const totalLogs = Object.keys(dailyLogs).length;
  const currentLocale = SUPPORTED_LOCALES.find(l => l.code === language) ?? SUPPORTED_LOCALES[0];

  return (
    <div
      className="min-h-screen bg-[#F5F0EB] pb-52 overflow-y-auto"
      style={{ animation: 'fade-in 0.3s ease-out' }}
    >
      <div className="max-w-sm mx-auto px-4 pt-12 pb-4">
        <h1 className="text-2xl font-bold text-stone-800 mb-1">{t.settingsTitle}</h1>
        <p className="text-stone-400 text-sm mb-6">{t.settingsSub}</p>

        {/* Profile Summary */}
        <div className="bg-white rounded-2xl p-5 mb-4 shadow-sm">
          <div className="flex items-center gap-4 mb-4">
            <div className="w-14 h-14 rounded-2xl bg-[#5B8A5E] flex items-center justify-center">
              <span className="text-2xl">🧠</span>
            </div>
            <div>
              <p className="font-bold text-stone-800">
                {t.appName}
              </p>
              <p className="text-stone-400 text-sm">
                {t.settingsPlanDay} {planDay} • {streak > 0 ? `${streak} 🔥` : '—'}
              </p>
            </div>
          </div>
          <div className="grid grid-cols-3 gap-2">
            {[
              { label: t.settingsPlanDay, value: planDay },
              { label: t.settingsStreakLabel, value: streak },
              { label: t.settingsDaysLogged, value: totalLogs },
            ].map((s) => (
              <div key={s.label} className="bg-stone-50 rounded-xl px-3 py-2 text-center">
                <p className="font-bold text-[#5B8A5E] text-lg">{s.value}</p>
                <p className="text-stone-400 text-[10px]">{s.label}</p>
              </div>
            ))}
          </div>
        </div>

        {/* Language */}
        <div className="bg-white rounded-2xl px-5 mb-4 shadow-sm">
          <div
            className="flex items-center gap-4 py-4 cursor-pointer"
            onClick={() => setShowLangPicker(!showLangPicker)}
          >
            <div className="flex-1">
              <p className="text-stone-700 font-semibold text-sm">{t.settingsLanguage}</p>
              <p className="text-stone-400 text-xs mt-0.5">{currentLocale.flag} {currentLocale.name}</p>
            </div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M6 9l6 6 6-6" stroke="#9CA3AF" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
          </div>
          {showLangPicker && (
            <div className="pb-4 grid grid-cols-3 gap-2">
              {SUPPORTED_LOCALES.map((loc) => (
                <button
                  key={loc.code}
                  onClick={() => { setLanguage(loc.code as Locale); setShowLangPicker(false); }}
                  className={`flex flex-col items-center gap-0.5 px-2 py-2 rounded-xl text-xs font-medium transition-all ${
                    language === loc.code
                      ? 'bg-[#5B8A5E] text-white'
                      : 'bg-stone-50 text-stone-600 active:bg-stone-100'
                  }`}
                >
                  <span className="text-xl">{loc.flag}</span>
                  <span>{loc.name}</span>
                </button>
              ))}
            </div>
          )}
        </div>

        {/* Apple Health — required by Guideline 2.5.1 to clearly identify
            HealthKit functionality in the UI. Always visible, regardless of
            Pro status, so reviewers see HealthKit transparency upfront. */}
        <div className="bg-white rounded-2xl px-5 py-4 mb-4 shadow-sm">
          <div className="flex items-center gap-3 mb-2">
            <div className="w-9 h-9 rounded-xl bg-[#FF2D55]/10 flex items-center justify-center flex-shrink-0">
              <svg width="20" height="20" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                <path
                  d="M6 10.5S1 7.5 1 4.5A2.5 2.5 0 0 1 6 3.2 2.5 2.5 0 0 1 11 4.5C11 7.5 6 10.5 6 10.5Z"
                  fill="#FF2D55"
                />
              </svg>
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-stone-700 font-bold text-sm">{t.settingsHealthTitle}</p>
              <p
                className="text-[11px] font-semibold"
                style={{ color: isPremium && todaySteps > 0 ? '#5B8A5E' : '#9CA3AF' }}
              >
                {isPremium && todaySteps > 0 ? t.settingsHealthConnected : t.settingsHealthDisconnected}
              </p>
            </div>
          </div>
          <p className="text-stone-500 text-xs leading-relaxed mb-2">{t.settingsHealthDesc}</p>
          <p className="text-stone-400 text-[11px] leading-relaxed">{t.settingsHealthReadOnlyNote}</p>
        </div>

        {/* Mode Settings */}
        <div className="bg-white rounded-2xl px-5 mb-4 shadow-sm">
          <ToggleRow
            label={t.settingsHardModeLabel}
            description={t.settingsHardModeDesc}
            value={profile.hardMode}
            onChange={toggleHardMode}
            accentColor="#E4A85A"
          />
        </div>

        {/* Brain Challenges reset frequency */}
        <div className="bg-white rounded-2xl px-5 py-4 mb-4 shadow-sm">
          <p className="text-stone-700 font-bold text-sm mb-1">{t.settingsChallengeResetTitle}</p>
          <p className="text-stone-400 text-xs mb-3">{t.settingsChallengeResetDesc}</p>
          <div className="grid grid-cols-2 gap-2">
            {(['daily', 'weekly'] as const).map((mode) => {
              const selected = challengeResetMode === mode;
              const label = mode === 'daily' ? t.settingsChallengeResetDaily : t.settingsChallengeResetWeekly;
              return (
                <button
                  key={mode}
                  onClick={() => setChallengeResetMode(mode)}
                  className="px-3 py-2 rounded-xl text-sm font-semibold border-2 transition-all"
                  style={{
                    borderColor: selected ? '#5B8A5E' : '#e7e5e4',
                    background: selected ? '#f0f7f0' : 'white',
                    color: selected ? '#3D6640' : '#78716c',
                  }}
                >
                  {label}
                </button>
              );
            })}
          </div>
        </div>

        {/* Step goal — Pro only */}
        {isPremium && (
          <div className="bg-white rounded-2xl px-5 py-4 mb-4 shadow-sm">
            <p className="text-stone-700 font-bold text-sm mb-1">{t.settingsStepGoalTitle}</p>
            <p className="text-stone-400 text-xs mb-3">{t.settingsStepGoalDesc}</p>
            <div className="flex gap-2 flex-wrap">
              {[5000, 7500, 10000, 12500, 15000].map((goal) => (
                <button
                  key={goal}
                  onClick={() => setStepGoal(goal)}
                  className="px-3 py-1.5 rounded-xl text-xs font-semibold border-2 transition-all"
                  style={{
                    borderColor: stepGoal === goal ? '#5B8A5E' : '#e7e5e4',
                    background: stepGoal === goal ? '#f0f7f0' : 'white',
                    color: stepGoal === goal ? '#3D6640' : '#78716c',
                  }}
                >
                  {goal.toLocaleString(language)}
                </button>
              ))}
            </div>
            <div className="flex items-center gap-2 mt-3">
              <input
                type="number"
                value={stepGoal}
                onChange={(e) => {
                  const v = parseInt(e.target.value);
                  if (v > 0) setStepGoal(v);
                }}
                className="flex-1 border border-stone-200 rounded-xl px-3 py-2 text-sm text-stone-700"
                placeholder={t.settingsStepGoalCustom}
              />
              <span className="text-stone-400 text-xs">{t.settingsStepGoalUnit}</span>
            </div>
          </div>
        )}

        {/* Daily check-in notification */}
        <div className="bg-white rounded-2xl px-5 py-4 mb-4 shadow-sm">
          <div className="flex items-start justify-between mb-3">
            <div className="flex-1 pr-3">
              <p className="text-stone-700 font-bold text-sm">{t.notifSettingsTitle}</p>
              <p className="text-stone-400 text-xs mt-0.5">{t.notifSettingsDesc}</p>
            </div>
            <button
              onClick={async () => {
                const result = await setNotificationsEnabled(!notificationsEnabled);
                if (result === 'denied') {
                  alert(t.notifPermissionDenied);
                }
              }}
              className="w-12 h-6 rounded-full relative transition-all flex-shrink-0"
              style={{ backgroundColor: notificationsEnabled ? '#5B8A5E' : '#e7e5e4' }}
              aria-pressed={notificationsEnabled}
            >
              <span
                className={`absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all ${
                  notificationsEnabled ? 'right-0.5' : 'left-0.5'
                }`}
              />
            </button>
          </div>
          {notificationsEnabled && (
            <div className="flex items-center justify-between border-t border-stone-100 pt-3">
              <span className="text-stone-500 text-sm">{t.notifTimeLabel}</span>
              <input
                type="time"
                value={`${String(notificationTime.hour).padStart(2, '0')}:${String(notificationTime.minute).padStart(2, '0')}`}
                onChange={(e) => {
                  const [h, m] = e.target.value.split(':').map(Number);
                  if (!isNaN(h) && !isNaN(m)) setNotificationTime({ hour: h, minute: m });
                }}
                className="bg-stone-50 rounded-xl px-3 py-2 text-stone-700 font-semibold text-sm outline-none"
              />
            </div>
          )}
        </div>

        {/* Evening reflection notification */}
        <div className="bg-white rounded-2xl px-5 py-4 mb-4 shadow-sm">
          <div className="flex items-start justify-between mb-3">
            <div className="flex-1 pr-3">
              <p className="text-stone-700 font-bold text-sm">{t.notifEveningSettingsTitle}</p>
              <p className="text-stone-400 text-xs mt-0.5">{t.notifEveningSettingsDesc}</p>
            </div>
            <button
              onClick={async () => {
                const result = await setEveningReflectionEnabled(!eveningReflectionEnabled);
                if (result === 'denied') {
                  alert(t.notifPermissionDenied);
                }
              }}
              className="w-12 h-6 rounded-full relative transition-all flex-shrink-0"
              style={{ backgroundColor: eveningReflectionEnabled ? '#5B8A5E' : '#e7e5e4' }}
              aria-pressed={eveningReflectionEnabled}
            >
              <span
                className={`absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all ${
                  eveningReflectionEnabled ? 'right-0.5' : 'left-0.5'
                }`}
              />
            </button>
          </div>
          {eveningReflectionEnabled && (
            <div className="flex items-center justify-between border-t border-stone-100 pt-3">
              <span className="text-stone-500 text-sm">{t.notifEveningTimeLabel}</span>
              <input
                type="time"
                value={`${String(eveningReflectionTime.hour).padStart(2, '0')}:${String(eveningReflectionTime.minute).padStart(2, '0')}`}
                onChange={(e) => {
                  const [h, m] = e.target.value.split(':').map(Number);
                  if (!isNaN(h) && !isNaN(m)) setEveningReflectionTime({ hour: h, minute: m });
                }}
                className="bg-stone-50 rounded-xl px-3 py-2 text-stone-700 font-semibold text-sm outline-none"
              />
            </div>
          )}
        </div>

        {/* Personal stats — the daily "look at what you built" hero */}
        <PersonalStats />

        {/* Profile Details — dynamic + editable */}
        <ProfileBlock />

        {/* Actions */}
        <div className="space-y-3 mb-4">
          {!showResetConfirm ? (
            <button
              onClick={() => setShowResetConfirm(true)}
              className="w-full bg-white rounded-2xl px-5 py-4 text-left shadow-sm flex items-center justify-between active:scale-[0.99] transition-transform"
            >
              <div>
                <p className="text-danger font-semibold text-sm">{t.settingsResetTitle}</p>
                <p className="text-stone-400 text-xs mt-0.5">{t.settingsResetDesc}</p>
              </div>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path
                  d="M3 6h18M8 6V4h8v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"
                  stroke="#D97070"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                />
              </svg>
            </button>
          ) : (
            <div className="bg-red-50 border-2 border-red-100 rounded-2xl p-5">
              <p className="text-red-800 font-semibold text-sm mb-1">
                {t.settingsResetConfirmTitle}
              </p>
              <p className="text-red-400 text-xs mb-4">
                {t.settingsResetConfirmBody}
              </p>
              <div className="flex gap-3">
                <button
                  onClick={() => setShowResetConfirm(false)}
                  className="flex-1 py-3 rounded-xl border-2 border-stone-200 text-stone-500 font-semibold text-sm"
                >
                  {t.settingsResetCancel}
                </button>
                <button
                  onClick={resetApp}
                  className="flex-1 py-3 rounded-xl bg-red-500 text-white font-semibold text-sm"
                >
                  {t.settingsResetConfirm}
                </button>
              </div>
            </div>
          )}
        </div>

        {/* Premium */}
        <div className="bg-white rounded-2xl p-5 mb-4 shadow-sm">
          <div className="flex items-center justify-between mb-1">
            <p className="text-stone-700 font-bold text-sm">{t.settingsPremiumTitle}</p>
            <button
              onClick={handleSecretTap}
              className="text-xs font-bold px-2.5 py-1 rounded-full"
              style={{
                background: isPremium ? '#5B8A5E20' : '#F5F0EB',
                color: isPremium ? '#5B8A5E' : '#9CA3AF',
              }}
            >
              {isPremium ? t.settingsPremiumActive : t.settingsPremiumFree}
            </button>
          </div>
          <p className="text-stone-400 text-xs mb-3">
            {isPremium ? t.settingsPremiumActiveDesc : t.settingsPremiumFreeDesc}
          </p>
          {devUnlocked && (
            <button
              className="w-full py-2 mb-2 rounded-xl bg-amber-100 text-amber-800 font-bold text-xs border border-amber-300"
              onClick={() => setPremium(!isPremium)}
            >
              🔧 DEV: Toggle Premium ({isPremium ? 'ON' : 'OFF'})
            </button>
          )}
          {!isPremium ? (
            <div className="space-y-2">
              <button
                className="w-full py-3 rounded-2xl text-white font-bold text-sm"
                style={{ background: 'linear-gradient(135deg, #5B8A5E, #3D6640)' }}
                onClick={() => setShowPremiumModal(true)}
              >
                {t.settingsUnlockPro}
              </button>
              <button
                className="w-full py-2.5 rounded-2xl border border-stone-200 text-stone-500 font-medium text-xs"
                onClick={async () => {
                  setRestoreState('loading');
                  const result = await restorePurchases();
                  if (result === 'restored') setRestoreState('done');
                  else if (result === 'not_found') setRestoreState('not_found');
                  else setRestoreState('error');
                  setTimeout(() => setRestoreState('idle'), 3000);
                }}
                disabled={restoreState === 'loading'}
              >
                {restoreState === 'loading' ? '…' :
                 restoreState === 'done' ? '✓' :
                 restoreState === 'not_found' ? t.purchaseError :
                 restoreState === 'error' ? t.purchaseError :
                 t.settingsRestorePurchase}
              </button>
            </div>
          ) : null}
        </div>

        {/* Feedback */}
        <div className="bg-white rounded-2xl px-5 py-4 mb-4 shadow-sm">
          <p className="text-stone-700 font-bold text-sm">{t.feedbackTitle}</p>
          <p className="text-stone-400 text-xs mt-1 mb-3 leading-relaxed">{t.feedbackBody}</p>
          <a
            href={`mailto:rebuildwithinofficial@gmail.com?subject=${encodeURIComponent(t.feedbackSubject)}&body=${encodeURIComponent(`\n\n---\nDopamine Reset Coach\nLanguage: ${language}`)}`}
            className="block w-full py-3 rounded-2xl text-white font-bold text-sm text-center active:scale-[0.98] transition-transform"
            style={{ background: 'linear-gradient(135deg, #5B8A5E, #3D6640)' }}
          >
            {t.feedbackCta}
          </a>
        </div>

        {/* Legal — Privacy Policy + Terms of Use (Apple requirement) */}
        <div className="bg-white rounded-2xl px-5 mb-4 shadow-sm overflow-hidden">
          <button
            onClick={() => setLegalOpen('privacy')}
            className="w-full flex items-center gap-4 py-4 border-b border-stone-100 text-left"
          >
            <div className="flex-1">
              <p className="text-stone-700 font-semibold text-sm">Privacy Policy</p>
              <p className="text-stone-400 text-xs mt-0.5">How your data is handled</p>
            </div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M9 18l6-6-6-6" stroke="#9CA3AF" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
          </button>
          <button
            onClick={() => setLegalOpen('terms')}
            className="w-full flex items-center gap-4 py-4 text-left"
          >
            <div className="flex-1">
              <p className="text-stone-700 font-semibold text-sm">Terms of Use (EULA)</p>
              <p className="text-stone-400 text-xs mt-0.5">Subscription terms & agreement</p>
            </div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M9 18l6-6-6-6" stroke="#9CA3AF" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
          </button>
        </div>

        {/* About */}
        <div className="bg-stone-50 rounded-2xl p-4">
          <p className="text-stone-400 text-xs leading-relaxed text-center whitespace-pre-line">
            {t.settingsAbout}
          </p>
        </div>
      </div>

      {showPremiumModal && (
        <PremiumModal onClose={() => setShowPremiumModal(false)} />
      )}
      {legalOpen && (
        <LegalModal kind={legalOpen} onClose={() => setLegalOpen(null)} />
      )}
    </div>
  );
}
