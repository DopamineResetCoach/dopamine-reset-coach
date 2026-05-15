'use client';

import { useEffect, useState } from 'react';
import { useAppStore } from '@/store/useAppStore';
import OnboardingFlow from '@/components/onboarding/OnboardingFlow';
import WelcomeIntro from '@/components/onboarding/WelcomeIntro';
import Dashboard from '@/components/dashboard/Dashboard';

export default function Home() {
  const [hydrated, setHydrated] = useState(false);

  useEffect(() => {
    try {
      useAppStore.persist.rehydrate();
    } catch (_) {
      // ignore
    }
    setHydrated(true);
    // Initialiseer RevenueCat op de achtergrond (alleen op iOS device/simulator)
    useAppStore.getState().initPurchases();
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  const hasSeenWelcome = useAppStore((s) => s.hasSeenWelcome);
  const hasCompletedOnboarding = useAppStore((s) => s.hasCompletedOnboarding);
  const markWelcomeSeen = useAppStore((s) => s.markWelcomeSeen);

  if (!hydrated) {
    return (
      <div className="min-h-screen bg-[#F5F0EB] flex items-center justify-center">
        <div className="flex flex-col items-center gap-3">
          <div className="w-14 h-14 rounded-2xl bg-[#5B8A5E] flex items-center justify-center shadow-lg">
            <span className="text-3xl">🧠</span>
          </div>
          <p className="text-stone-400 text-sm font-medium">Loading…</p>
        </div>
      </div>
    );
  }

  // First launch only — shown once before onboarding starts. Existing users
  // (hasCompletedOnboarding already true) skip this entirely.
  if (!hasSeenWelcome && !hasCompletedOnboarding) {
    return <WelcomeIntro onContinue={markWelcomeSeen} />;
  }

  return hasCompletedOnboarding ? <Dashboard /> : <OnboardingFlow />;
}
