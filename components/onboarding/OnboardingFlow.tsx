'use client';

import { useState } from 'react';
import { UserProfile } from '@/types';
import { useAppStore } from '@/store/useAppStore';
import { useT } from '@/hooks/useT';
import { SUPPORTED_LOCALES } from '@/lib/i18n';
import type { Locale } from '@/lib/i18n';
import { toLocalDateString } from '@/lib/scoring';

const TOTAL_STEPS = 4; // habits, wellbeing, plan, ready

interface FormData {
  screenTimeHours: number;
  sleepQuality: number;
  energyLevel: number;
  brainFog: boolean;
  habits: {
    socialMedia: boolean;
    caffeine: boolean;
    junkFood: boolean;
    alcohol: boolean;
    porn: boolean;
    gaming: boolean;
  };
  planDuration: 7 | 14 | 30;
  hardMode: boolean;
}

const defaultForm: FormData = {
  screenTimeHours: 3,
  sleepQuality: 3,
  energyLevel: 3,
  brainFog: false,
  habits: {
    socialMedia: true,
    caffeine: false,
    junkFood: false,
    alcohol: false,
    porn: false,
    gaming: false,
  },
  planDuration: 14,
  hardMode: false,
};

function StepDots({ step }: { step: number }) {
  return (
    <div className="flex gap-2 justify-center mb-8">
      {Array.from({ length: TOTAL_STEPS }).map((_, i) => (
        <div
          key={i}
          className={`h-1.5 rounded-full transition-all duration-300 ${
            i < step
              ? 'w-6 bg-[#5B8A5E]'
              : i === step
              ? 'w-4 bg-[#5B8A5E]/50'
              : 'w-1.5 bg-stone-200'
          }`}
        />
      ))}
    </div>
  );
}

function StarRating({ value, onChange }: { value: number; onChange: (v: number) => void }) {
  return (
    <div className="flex gap-3 mt-3">
      {[1, 2, 3, 4, 5].map((n) => (
        <button
          key={n}
          onClick={() => onChange(n)}
          className={`w-10 h-10 rounded-xl text-lg transition-all ${
            n <= value ? 'bg-[#5B8A5E] text-white shadow-sm' : 'bg-stone-100 text-stone-400'
          }`}
        >
          {n}
        </button>
      ))}
    </div>
  );
}

function HabitToggle({ label, emoji, value, onChange }: {
  label: string; emoji: string; value: boolean; onChange: (v: boolean) => void;
}) {
  return (
    <button
      onClick={() => onChange(!value)}
      className={`flex items-center gap-3 px-4 py-3 rounded-2xl border-2 transition-all ${
        value ? 'border-[#5B8A5E] bg-[#5B8A5E]/8 text-[#3D6640]' : 'border-stone-200 bg-white text-stone-500'
      }`}
    >
      <span className="text-xl">{emoji}</span>
      <span className="font-medium text-sm">{label}</span>
      <div className={`ml-auto w-5 h-5 rounded-full border-2 flex items-center justify-center ${value ? 'border-[#5B8A5E] bg-[#5B8A5E]' : 'border-stone-300'}`}>
        {value && (
          <svg width="10" height="8" viewBox="0 0 10 8" fill="none">
            <path d="M1 4l3 3 5-6" stroke="white" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        )}
      </div>
    </button>
  );
}

function StepLanguage({ onNext }: { onNext: () => void }) {
  const language = useAppStore((s) => s.language);
  const setLanguage = useAppStore((s) => s.setLanguage);

  const handleSelect = (code: Locale) => {
    setLanguage(code);
    onNext();
  };

  return (
    <div className="flex flex-col items-center animate-[fade-in_0.4s_ease-out]">
      <div className="w-20 h-20 rounded-3xl bg-[#5B8A5E] flex items-center justify-center mb-5 shadow-lg">
        <span className="text-4xl">🌍</span>
      </div>
      <h1 className="text-2xl font-bold text-stone-800 mb-1 text-center">Choose your language</h1>
      <p className="text-stone-400 text-sm mb-8 text-center">Kies je taal · Wähle deine Sprache</p>

      <div className="grid grid-cols-3 gap-3 w-full">
        {SUPPORTED_LOCALES.map((loc) => (
          <button
            key={loc.code}
            onClick={() => handleSelect(loc.code as Locale)}
            className={`flex flex-col items-center gap-1.5 py-4 rounded-2xl border-2 transition-all active:scale-95 ${
              language === loc.code
                ? 'border-[#5B8A5E] bg-[#5B8A5E]/10'
                : 'border-stone-200 bg-white'
            }`}
          >
            <span className="text-3xl">{loc.flag}</span>
            <span className={`text-xs font-semibold ${language === loc.code ? 'text-[#3D6640]' : 'text-stone-600'}`}>
              {loc.name}
            </span>
          </button>
        ))}
      </div>
    </div>
  );
}

function StepWelcome({ onNext }: { onNext: () => void }) {
  const t = useT();
  return (
    <div className="flex flex-col items-center text-center animate-[fade-in_0.4s_ease-out]">
      <div className="w-20 h-20 rounded-3xl bg-[#5B8A5E] flex items-center justify-center mb-6 shadow-lg">
        <span className="text-4xl">🧠</span>
      </div>
      <h1 className="text-3xl font-bold text-stone-800 mb-3 tracking-tight">{t.welcomeTitle}</h1>
      <p className="text-stone-500 text-base leading-relaxed max-w-xs mb-2">{t.welcomeTagline}</p>
      <p className="text-stone-400 text-sm mb-10">{t.welcomeNoAccount}</p>
      <div className="w-full space-y-3 mb-8">
        {([
          ['📵', t.welcomeF1],
          ['⚡', t.welcomeF2],
          ['🎯', t.welcomeF3],
        ] as [string, string][]).map(([icon, text]) => (
          <div key={text} className="flex items-center gap-3 bg-stone-50 rounded-2xl px-4 py-3">
            <span className="text-xl">{icon}</span>
            <span className="text-stone-600 font-medium text-sm">{text}</span>
          </div>
        ))}
      </div>
      <button
        onClick={onNext}
        className="w-full py-4 rounded-2xl bg-[#5B8A5E] text-white font-semibold text-base shadow-sm active:scale-[0.98] transition-transform"
      >
        {t.welcomeStart}
      </button>
    </div>
  );
}

function StepHabits({ form, setForm, onNext, onBack }: {
  form: FormData; setForm: (f: FormData) => void; onNext: () => void; onBack: () => void;
}) {
  const t = useT();
  return (
    <div className="animate-[fade-in_0.4s_ease-out]">
      <h2 className="text-2xl font-bold text-stone-800 mb-1">{t.habitsTitle}</h2>
      <p className="text-stone-400 text-sm mb-6">{t.habitsSubtitle}</p>

      <div className="mb-6">
        <label className="text-stone-700 font-semibold text-sm block mb-1">{t.habitsScreenTime}</label>
        <div className="flex items-center gap-3 mt-3">
          <input
            type="range" min={0} max={10} step={0.5}
            value={form.screenTimeHours}
            onChange={(e) => setForm({ ...form, screenTimeHours: Number(e.target.value) })}
            className="flex-1"
          />
          <span className="text-[#5B8A5E] font-bold text-base w-16 text-right">
            {form.screenTimeHours}h/day
          </span>
        </div>
      </div>

      <div className="mb-2">
        <label className="text-stone-700 font-semibold text-sm block mb-3">{t.habitsWhich}</label>
        <div className="grid grid-cols-2 gap-2">
          {([
            ['socialMedia', t.habitSocialMedia, '📱'],
            ['caffeine', t.habitCaffeine, '☕'],
            ['junkFood', t.habitJunkFood, '🍔'],
            ['alcohol', t.habitAlcohol, '🍺'],
            ['porn', t.habitAdultContent, '🔞'],
            ['gaming', t.habitGaming, '🎮'],
          ] as [keyof FormData['habits'], string, string][]).map(([key, label, emoji]) => (
            <HabitToggle
              key={key} label={label} emoji={emoji}
              value={form.habits[key]}
              onChange={(v) => setForm({ ...form, habits: { ...form.habits, [key]: v } })}
            />
          ))}
        </div>
      </div>

      <div className="flex gap-3 mt-8">
        <button onClick={onBack} className="flex-1 py-4 rounded-2xl border-2 border-stone-200 text-stone-500 font-semibold text-base">{t.back}</button>
        <button onClick={onNext} className="flex-[2] py-4 rounded-2xl bg-[#5B8A5E] text-white font-semibold text-base shadow-sm active:scale-[0.98] transition-transform">{t.continueBtn}</button>
      </div>
    </div>
  );
}

function StepWellbeing({ form, setForm, onNext, onBack }: {
  form: FormData; setForm: (f: FormData) => void; onNext: () => void; onBack: () => void;
}) {
  const t = useT();
  return (
    <div className="animate-[fade-in_0.4s_ease-out]">
      <h2 className="text-2xl font-bold text-stone-800 mb-1">{t.wellbeingTitle}</h2>
      <p className="text-stone-400 text-sm mb-6">{t.wellbeingSubtitle}</p>

      <div className="mb-6">
        <label className="text-stone-700 font-semibold text-sm block">{t.wellbeingSleepQ}</label>
        <p className="text-stone-400 text-xs mt-0.5 mb-1">{t.wellbeingSleepScale}</p>
        <StarRating value={form.sleepQuality} onChange={(v) => setForm({ ...form, sleepQuality: v })} />
      </div>

      <div className="mb-6">
        <label className="text-stone-700 font-semibold text-sm block">{t.wellbeingEnergyQ}</label>
        <p className="text-stone-400 text-xs mt-0.5 mb-1">{t.wellbeingEnergyScale}</p>
        <StarRating value={form.energyLevel} onChange={(v) => setForm({ ...form, energyLevel: v })} />
      </div>

      <div className="mb-2">
        <label className="text-stone-700 font-semibold text-sm block mb-3">{t.wellbeingBrainFogQ}</label>
        <div className="flex gap-3">
          {([true, false] as const).map((val) => (
            <button
              key={String(val)}
              onClick={() => setForm({ ...form, brainFog: val })}
              className={`flex-1 py-3 rounded-2xl border-2 font-medium transition-all ${
                form.brainFog === val
                  ? 'border-[#5B8A5E] bg-[#5B8A5E]/8 text-[#3D6640]'
                  : 'border-stone-200 text-stone-500'
              }`}
            >
              {val ? t.wellbeingYes : t.wellbeingNo}
            </button>
          ))}
        </div>
      </div>

      <div className="flex gap-3 mt-8">
        <button onClick={onBack} className="flex-1 py-4 rounded-2xl border-2 border-stone-200 text-stone-500 font-semibold text-base">{t.back}</button>
        <button onClick={onNext} className="flex-[2] py-4 rounded-2xl bg-[#5B8A5E] text-white font-semibold text-base shadow-sm active:scale-[0.98] transition-transform">{t.continueBtn}</button>
      </div>
    </div>
  );
}

function StepPlan({ form, setForm, onNext, onBack }: {
  form: FormData; setForm: (f: FormData) => void; onNext: () => void; onBack: () => void;
}) {
  const t = useT();
  const plans: { days: 7 | 14 | 30; label: string; desc: string; tag?: string }[] = [
    { days: 7, label: t.plan7Label, desc: t.plan7Desc },
    { days: 14, label: t.plan14Label, desc: t.plan14Desc, tag: t.plan14Tag },
    { days: 30, label: t.plan30Label, desc: t.plan30Desc },
  ];

  return (
    <div className="animate-[fade-in_0.4s_ease-out]">
      <h2 className="text-2xl font-bold text-stone-800 mb-1">{t.planTitle}</h2>
      <p className="text-stone-400 text-sm mb-6">{t.planSubtitle}</p>

      <div className="space-y-3 mb-6">
        {plans.map((plan) => (
          <button
            key={plan.days}
            onClick={() => setForm({ ...form, planDuration: plan.days })}
            className={`w-full text-left px-4 py-4 rounded-2xl border-2 transition-all ${
              form.planDuration === plan.days ? 'border-[#5B8A5E] bg-[#5B8A5E]/8' : 'border-stone-200 bg-white'
            }`}
          >
            <div className="flex items-center justify-between mb-1">
              <span className={`font-bold ${form.planDuration === plan.days ? 'text-[#3D6640]' : 'text-stone-700'}`}>
                {plan.label}
              </span>
              {plan.tag && (
                <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#5B8A5E] text-white">{plan.tag}</span>
              )}
            </div>
            <p className="text-stone-400 text-xs leading-relaxed">{plan.desc}</p>
          </button>
        ))}
      </div>

      <div
        className={`flex items-start gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all ${
          form.hardMode ? 'border-amber-400 bg-amber-50' : 'border-stone-200 bg-white'
        }`}
        onClick={() => setForm({ ...form, hardMode: !form.hardMode })}
      >
        <div className={`w-12 h-6 rounded-full transition-all relative flex-shrink-0 mt-0.5 ${form.hardMode ? 'bg-amber-400' : 'bg-stone-200'}`}>
          <div className={`absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all ${form.hardMode ? 'right-0.5' : 'left-0.5'}`} />
        </div>
        <div>
          <p className="font-bold text-stone-700 text-sm">{t.hardModeLabel}</p>
          <p className="text-stone-400 text-xs mt-0.5 leading-relaxed">{t.hardModeDesc}</p>
        </div>
      </div>

      <div className="flex gap-3 mt-6">
        <button onClick={onBack} className="flex-1 py-4 rounded-2xl border-2 border-stone-200 text-stone-500 font-semibold text-base">{t.back}</button>
        <button onClick={onNext} className="flex-[2] py-4 rounded-2xl bg-[#5B8A5E] text-white font-semibold text-base shadow-sm active:scale-[0.98] transition-transform">{t.planBuildBtn}</button>
      </div>
    </div>
  );
}

function StepReady({ form, onStart }: { form: FormData; onStart: () => void }) {
  const t = useT();
  const habitCount = Object.values(form.habits).filter(Boolean).length;
  const score = Math.max(
    20,
    60 - form.screenTimeHours * 3 - (5 - form.sleepQuality) * 3 - (5 - form.energyLevel) * 2 - habitCount * 3
  );

  return (
    <div className="animate-[fade-in_0.4s_ease-out]">
      <div className="text-center mb-8">
        <div className="w-16 h-16 rounded-2xl bg-[#5B8A5E] flex items-center justify-center mx-auto mb-4">
          <span className="text-3xl">✅</span>
        </div>
        <h2 className="text-2xl font-bold text-stone-800 mb-2">{t.readyTitle}</h2>
        <p className="text-stone-400 text-sm">{t.readySubtitle}</p>
      </div>

      <div className="space-y-3 mb-6">
        <div className="bg-stone-50 rounded-2xl px-4 py-4 flex justify-between items-center">
          <span className="text-stone-600 text-sm font-medium">{t.readyPlanDuration}</span>
          <span className="font-bold text-[#5B8A5E]">{form.planDuration} {t.days}</span>
        </div>
        <div className="bg-stone-50 rounded-2xl px-4 py-4 flex justify-between items-center">
          <span className="text-stone-600 text-sm font-medium">{t.readyStartingScore}</span>
          <span className="font-bold text-amber-600">{Math.round(score)}/100</span>
        </div>
        <div className="bg-stone-50 rounded-2xl px-4 py-4 flex justify-between items-center">
          <span className="text-stone-600 text-sm font-medium">{t.readyMode}</span>
          <span className={`font-bold ${form.hardMode ? 'text-amber-600' : 'text-stone-500'}`}>
            {form.hardMode ? t.hardModeLabel : t.readyStandard}
          </span>
        </div>
        <div className="bg-stone-50 rounded-2xl px-4 py-4 flex justify-between items-center">
          <span className="text-stone-600 text-sm font-medium">{t.readyHabits}</span>
          <span className="font-bold text-stone-700">{habitCount} {t.readyIdentified}</span>
        </div>
      </div>

      <div className="bg-[#5B8A5E]/8 border border-[#5B8A5E]/20 rounded-2xl p-4 mb-6">
        <p className="text-[#3D6640] text-sm leading-relaxed">{t.readyExpectation}</p>
      </div>

      <button
        onClick={onStart}
        className="w-full py-4 rounded-2xl bg-[#5B8A5E] text-white font-semibold text-lg shadow-sm active:scale-[0.98] transition-transform"
      >
        {t.readyStartBtn}
      </button>
    </div>
  );
}

export default function OnboardingFlow() {
  const [step, setStep] = useState(0);
  const [form, setForm] = useState<FormData>(defaultForm);
  const completeOnboarding = useAppStore((s) => s.completeOnboarding);

  const next = () => setStep((s) => Math.min(s + 1, TOTAL_STEPS + 1));
  const back = () => setStep((s) => Math.max(s - 1, 0));

  const handleStart = () => {
    const today = toLocalDateString(new Date());
    const profile: UserProfile = { ...form, startDate: today };
    completeOnboarding(profile);
  };

  return (
    <div className="min-h-screen bg-[#F5F0EB] flex justify-center items-start">
      <div className="w-full max-w-sm min-h-screen bg-[#F5F0EB] flex flex-col px-6 pt-14 pb-8">
        {step > 1 && <StepDots step={step - 2} />}
        {step === 0 && <StepLanguage onNext={next} />}
        {step === 1 && <StepWelcome onNext={next} />}
        {step === 2 && <StepHabits form={form} setForm={setForm} onNext={next} onBack={back} />}
        {step === 3 && <StepWellbeing form={form} setForm={setForm} onNext={next} onBack={back} />}
        {step === 4 && <StepPlan form={form} setForm={setForm} onNext={next} onBack={back} />}
        {step === 5 && <StepReady form={form} onStart={handleStart} />}
      </div>
    </div>
  );
}
